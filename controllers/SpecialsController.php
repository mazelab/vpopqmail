<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_SpecialsController extends Zend_Controller_Action
{
    
    /**
     * @var mixed | null
     */
    protected $_identity;

    /**
     * @var string
     */
    protected $_domainId;
    
    /**
     * message when cathall wasn't found
     */
    CONST MESSAGE_CATCHALL_NOT_FOUND = 'Catch all %1$s not found';
    
    /**
     * message when domain wasn't found
     */
    CONST MESSAGE_DOMAIN_NOT_FOUND = 'Domain %1$s not found';
    
    /**
     * message when importing special failed
     */
    CONST MESSAGE_IMPORT_DELETE_FAILED = 'Importing special %1$s failed';
    
    /**
     * message when import mailing list has a invalid context
     */
    CONST MESSAGE_LIST_IMPORT_INVALID = 'Invalid context for mailing list import';
    
    /**
     * message when mailing list wasn't found
     */
    CONST MESSAGE_LIST_NOT_FOUND = 'Mailing list %1$s not found';
    
    /**
     * message when import robot has a invalid context
     */
    CONST MESSAGE_ROBOT_IMPORT_INVALID = 'Invalid context for mailrobot import';
    
    /**
     * message when mail robot wasn't found
     */
    CONST MESSAGE_ROBOT_NOT_FOUND = 'Mailrobot %1$s not found';
    
    /**
     * message when special wasn't found
     */
    CONST MESSAGE_SPECIAL_NOT_FOUND = 'Special %1$s not found';
    
    /**
     * checks whether target with current client id matches
     * 
     * @param  string $targetId
     * @return boolean
     */
    public function isOwner($targetId)
    {
        return($targetId == $this->_identity["_id"]);
    }
    
    public function init()
    {
        $this->_identity = Zend_Auth::getInstance()->getIdentity();
        
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('delete', 'json')
                    ->addActionContext('changestate', 'json')
                    ->addActionContext('addmailinglistsubscriber', 'json')
                    ->addActionContext('mailinglist', 'json')
                    ->addActionContext('mailrobot', 'json')
                    ->addActionContext('index', 'html')
                    ->addActionContext('domain', 'html')
                    ->initContext();

        if ($this->getRequest()->getQuery("domainFilter")) {
            $this->_domainId = $this->getParam("domainFilter");
            setcookie("domainId", $this->getParam("domainFilter"));
        } else {
            $this->_domainId = $this->_request->getCookie("domainId");
        }
        
        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }
 
    public function indexAction()
    {
        if ($this->_domainId){
            $this->forward("domain");
            return;
        }
        
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->view->addBasePath(realpath($this->getFrontController()
                ->getControllerDirectory('core') . '/../views'));
            
            $this->view->client = $this->_identity;
        }
        
        $this->view->pager = $this->getPager($this->_identity['_id'], null, 10);
    }
    
    public function domainAction()
    {
        $domain = Core_Model_DiFactory::getDomainManager()->getDomainAsArray($this->_domainId);
        if(!$this->getRequest()->isXmlHttpRequest() && $this->isOwner($domain["owner"])) {
            $this->view->addBasePath(realpath($this->getFrontController()
                ->getControllerDirectory('core') . '/../views'));
            
            $this->view->domain = $domain;
        }else{
            setcookie("domainId");
            $this->redirect($this->view->url(array(), "mazelab-vpopqmail_specials"));
        }
        
        $this->view->pager = $this->getPager($this->_identity['_id'], $this->_domainId, 10);
    }
    
    public function addAction()
    {
        $clientId = $this->_identity['_id'];
        $type = $this->getParam('type');

        $form = new MazelabVpopqmail_Form_AddSpecial();
        $form->addDomainSelect($clientId);

        if($this->getRequest()->getPost() &&
                $form->isValid($this->getRequest()->getPost())) {
            $redirector = $this->_helper->getHelper('redirector');
            $domain = Core_Model_DiFactory::getDomainManager()->getDomain($form->getValue('domainId'));
            
            if ($type == MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST) {
                $redirector->goToRoute(array($domain->getName()), 'mazelab-vpopqmail_addMailingList');
                return;
            } elseif ($type == MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT) {
                $redirector->goToRoute(array($domain->getName()), 'mazelab-vpopqmail_addMailRobot');
                return;
            }
            
        }
        if($this->_request->getCookie("domainId", false)) {
            $form->showOnlyDomainAndDisableSelectbox($this->_request->getCookie("domainId"));
        }
        $this->view->form = $form->setDefaults($this->getRequest()->getParams());
    }
    
    public function addmailinglistAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!($domain = $domainManager->getDomainByName($this->getParam('domainName'))) ||
                !$this->isOwner($domain->getData("owner"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_DOMAIN_NOT_FOUND, $this->getParam('domainName'));
            return $this->_forward('add');
        }
        
        $form = new MazelabVpopqmail_Form_AddMailingList();
        $form->setDomainSelectValue($domain->getId());
        
        if ($this->getRequest()->isPost()) {
            $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
            
            if($this->getParam('subscriber')) {
                $form->addSubscriberFromPost($this->getParam('subscriber'));
            }
            
            if($form->isValid($this->getRequest()->getPost())) {
                if(($specialId = $mailingListManager->addMailingList($domain->getId(), $form->getValues()))) {
                    $this->_redirect($this->view->url(array(), 'mazelab-vpopqmail_specials'), array('exit' => false));
                }
            }
        }
        
        $this->view->form = $form;
        $this->view->domain = $domain->getData();;
    }
    
    public function addmailinglistsubscriberAction()
    {
        $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
        if(!($list = $mailingListManager->getMailingListByEmail($this->getParam('email'))) ||
                !$this->isOwner($list->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_LIST_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        $result = false;

        if($this->_request->getPost()) {
            $form = new MazelabVpopqmail_Form_MailingList();
            $validate = array(
                'addSubscriber' => $this->getParam('addSubscriber')
            );
            
            if ($form->isValidPartial($validate)) {
                $result = $mailingListManager->addMailingListSubscriber($list->getId(),
                        $form->getValue('addSubscriber'));
                
                $this->view->subscribers = $mailingListManager->getSubscribers($list->getId(), true);
            }

            $this->view->formErrors = $form->getMessages(null, true);
        }
        
        $this->view->result = $result;
    }
    
    public function addmailrobotAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!($domain = $domainManager->getDomainByName($this->getParam('domainName'))) ||
                !$this->isOwner($domain->getData("owner"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_DOMAIN_NOT_FOUND, $this->getParam('domainName'));
            return $this->_forward('add');
        }
        
        $form = new MazelabVpopqmail_Form_AddMailRobot();
        $form->setDomainSelectValue($domain->getId());
        
        if ($this->getRequest()->isPost() && 
                $form->isValid($this->getRequest()->getPost())) {
            $mailRobotManager = MazelabVpopqmail_Model_DiFactory::getMailRobotManager();
            
            $specialId = $mailRobotManager->addMailRobot($domain->getId(), $form->getValues());
            
            if($specialId) {
                $this->_redirect($this->view->url(array(), 'mazelab-vpopqmail_specials'), array('exit' => false));
            }
        }
        
        $this->view->form = $form;
        $this->view->domain = $domain->getData();
    }
    
    public function catchallAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!($domain = $domainManager->getDomainByName($this->getParam('domainName'))) ||
                !$this->isOwner($domain->getData("owner"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_DOMAIN_NOT_FOUND, $this->getParam('domainName'));
            return $this->_forward('index');
        }
        
        $catchAllManager = MazelabVpopqmail_Model_DiFactory::getCatchAllManager();
        if(!($catchAll = $catchAllManager->getCatchAllByDomainName($this->getParam('domainName')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_CATCHALL_NOT_FOUND, $this->getParam('domainName'));
            return $this->_forward('index');
        }
        
        $form = new MazelabVpopqmail_Form_CatchAll();
        $form->addClientAccounts($domain->getData('owner'))
             ->setDefaults($catchAll->getData());

        if($this->getRequest()->isPost() &&
                $form->isValid($this->getRequest()->getPost())) {
            $catchAllManager->updateCatchAll($catchAll->getId(), $form->getValues());
        }

        $this->view->form = $form;
        $this->view->domain = $domain->getData();
        $this->view->selectedBehavior = $form->getValue('selectedBehavior');
    }
    
    public function mailrobotAction()
    {
        $mailRobotManager = MazelabVpopqmail_Model_DiFactory::getMailRobotManager();
        if(!($mailRobot = $mailRobotManager->getMailRobotByEmail($this->getParam('email'))) || 
                !$this->isOwner($mailRobot->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_ROBOT_NOT_FOUND, $this->getParam('email'));
            return $this->_forward('index');
        }
        
        $form = new MazelabVpopqmail_Form_MailRobot();

        if($this->getRequest()->isPost()){
            $values = $form->getValidValues($this->getRequest()->getPost());
            if (!empty($values)) {
                $this->view->result = $mailRobotManager->updateMailRobot($mailRobot->getId(), $values);
            }

            $this->view->formErrors = $form->getMessages();
        }
        
        $data = $mailRobot->getData();

        $this->view->mailRobot = $data;
        $this->view->form = $form->setDefaults($data);
    }
    
    public function mailinglistAction()
    {
        $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
        if(!($mailingList = $mailingListManager->getMailingListByEmail($this->getParam('email'))) || 
                !$this->isOwner($mailingList->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_LIST_NOT_FOUND, $this->getParam('email'));
            return $this->_forward('index');
        }
        
        $form = new MazelabVpopqmail_Form_MailingList();
        $form->setDomainSelect($mailingList->getData('domainId'))
             ->setMailingListTargets($mailingList->getId());

        if($this->_request->getPost()) {
            $values = $form->getValidValues($this->_request->getPost());
            
            if (!empty($values)) {
                $this->view->result = $mailingListManager->updateMailingList($mailingList->getId(), $values);
                $this->view->subscribers = $mailingListManager->getSubscribers($mailingList->getId(), true);
            }

            $this->view->formErrors = $form->getMessages();
        }

        $data = $mailingList->getData();
        
        $this->view->mailingList = $data;
        $this->view->form = $form->setDefaults($data);
    }
    
    public function deleteAction()
    {
        $specialManager = MazelabVpopqmail_Model_DiFactory::getSpecialsManager();
        if(!($special = $specialManager->getSpecialByEmail($this->getParam('email')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_SPECIAL_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        $this->view->status = $specialManager->deleteSpecial($special->getId());
    }
    
    public function changestateAction()
    {
        $specialManager = MazelabVpopqmail_Model_DiFactory::getSpecialsManager();
        if(!($special = $specialManager->getSpecialByEmail($this->getParam('email')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_SPECIAL_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        $specialManager->changeSpecialState($special->getId());
        $this->view->special = $special->getData();
    }
    
    /**
     * get specials pager data
     * 
     * @param int $limit
     * @return array
     */
    public function getPager($ownerId, $domainId = null, $limit = null)
    {
        if(!$ownerId) {
            return array();
        }
        
        $pager = MazelabVpopqmail_Model_DiFactory::newSpecialsPager($ownerId, $domainId);
        $pager->setLimit($limit);
        
        if($this->getParam('term')) {
            $pager->setSearchTerm($this->getParam('term'));
        }
        
        if($this->getParam('term')) {
            $pager->setSearchTerm($this->getParam('term'));
        }
        
        $action = $this->getParam('pagerAction');
        if($action == 'last') {
            $pager->last();
        } else {
            $pager->setPage($this->getParam('page', 1))->page();
        }
        
        return $pager->asArray();
    }
    
}

