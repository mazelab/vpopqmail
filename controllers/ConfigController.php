<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_ConfigController extends Zend_Controller_Action
{
    
    /**
     * message when client wasn't found
     */
    CONST MESSAGE_CLIENT_NOT_FOUND = 'Client %1$s not found';
    
    /**
     * message when domain wasn't found
     */
    CONST MESSAGE_DOMAIN_NOT_FOUND = 'Domain %1$s not found';
    
    /**
     * message when node wasn't found
     */
    CONST MESSAGE_NODE_NOT_FOUND = 'Node %1$s not found';    
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('configclient', array('json', 'html'))
                    ->addActionContext('confignode', array('json', 'html'))
                    ->addActionContext('config', array('json', 'html'))
                    ->addActionContext('configdomain', array('json', 'html'))
                    ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function configAction()
    {
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $form = new MazelabVpopqmail_Form_Config;
        
        if($this->_request->getPost() && $form->isValid($this->_request->getPost())) {
            $this->view->result = $configManager->addConfig($form->getValues());
        }
        
        $this->view->formErrors = $form->getMessages(null, true);
        $this->view->form = $form->setDefaults($configManager->getConfig());
    }
    
    public function confignodeAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!($node = $nodeManager->getNodeByName($this->getParam('nodeName')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_NODE_NOT_FOUND, $this->getParam('label'));
            $this->getResponse()->setHttpResponseCode(404);
            $this->_helper->viewRenderer->setNoRender(TRUE);
            return null;
        }
        
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $form = new MazelabVpopqmail_Form_ConfigNode();
        
        if($this->_request->getPost() && $form->isValid($this->_request->getPost())) {
            $this->view->result = $configManager->addNodeConfig($node->getId(), $form->getValues());
        }
        
        $this->view->node = $node->getData();
        $this->view->formErrors = $form->getMessages(null, true);
        $this->view->form = $form->setDefaults($configManager->getNodeConfig($node->getId()));
    }
    
    public function configclientAction()
    {
        if(!($client = Core_Model_DiFactory::getClientManager()->getClient($this->getParam('clientId')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_CLIENT_NOT_FOUND, $this->getParam('clientLabel'));
            $this->getResponse()->setHttpResponseCode(404);
            $this->_helper->viewRenderer->setNoRender(TRUE);
            return null;
        }

        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $form = new MazelabVpopqmail_Form_ConfigClient();
        
        $form->setDefaults($configManager->getClientConfig($client->getId()));
        if($this->_request->getPost()) {
            $values = $form->getValidValues($this->_request->getPost());

            if(!empty($values)) {
                $this->view->result = $configManager->addClientConfig($client->getId(), $values);
            }

            $this->view->formErrors = $form->getMessages();
        }
        
        $this->view->form = $form;
        $this->view->clientId = $client->getId();
        $this->view->client = $client->getData();
    }

    public function configdomainAction()
    {
        if(!($domain = Core_Model_DiFactory::getDomainManager()
                ->getDomainByName($this->getParam('domainName')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_DOMAIN_NOT_FOUND, $this->getParam('domainName'));
            $this->getResponse()->setHttpResponseCode(404);
            $this->_helper->viewRenderer->setNoRender(TRUE);
            return null;
        }
        
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $form = new MazelabVpopqmail_Form_ConfigDomain();
        
        $form->setNodeSelect($domain->getId());
        if ($this->_request->isPost()){
            $post = $this->getRequest()->getPost();
            
            if(key_exists('nodes', $post)) {
                if($form->isValidPartial(array('nodes' => $this->getParam('nodes')))) {
                    $this->view->result = MazelabVpopqmail_Model_DiFactory::getDomainManager()
                            ->assignDomain($domain->getId(), $form->getValue('nodes'));
                }
            } elseif (($values = $form->getValidValues($this->_request->getPost()))) {
                $this->view->result = $configManager->addDomainConfig($domain->getId(), $values);
            }
            
            $this->view->formErrors = $form->getMessages();
        }

        $this->view->domainId = $domain->getId();
        $this->view->domain = $domain->getData();
        $this->view->form = $form->setDefaults($configManager->getDomainConfig($domain->getId()));
    }
}
