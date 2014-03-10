<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_ForwarderController extends Zend_Controller_Action
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
     * message when conflicts were resolved
     */
    CONST MESSAGE_CONFLICTS_RESOLVED = "Differences resolved!";
    
    /**
     * message when forwarder wasn't found
     */
    CONST MESSAGE_FORWARDER_NOT_FOUND = 'Forwarder %1$s not found';
    
    /**
     * message when deleting a import account
     */
    CONST MESSAGE_IMPORT_DELETE_FAILED= 'Forwarder %1$s was added but could\'t be automaticaly deleted. Please delete %1$s manually';
    
    /**
     * message when import forwarder has a invalid context
     */
    CONST MESSAGE_IMPORT_INVALID = 'Invalid context for forwarder import';
    
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
        $ajaxContext->addActionContext('detail', 'json')
                    ->addActionContext('delete', 'json')
                    ->addActionContext('addtarget', 'json')
                    ->addActionContext('changestate', 'json')
                    ->addActionContext('index', array('html'))
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
            $this->_forward("domain");
            return;
        }

        $this->view->conflicts = MazelabVpopqmail_Model_DiFactory::getForwarderManager()
                ->getConflictedForwardersByOwner($this->_identity['_id']);
        
        $this->view->client = $this->_identity;
        $this->view->pager = $this->getPager($this->_identity['_id'], null, 10);
    }
    
    public function domainAction()
    {
        $domain = Core_Model_DiFactory::getDomainManager()->getDomainAsArray($this->_domainId);
        if(!$this->getRequest()->isXmlHttpRequest() && $this->isOwner($domain["owner"])) {
            $this->view->domain = $domain;
        }else{
            setcookie("domainId");
            $this->redirect($this->view->url(array(), "mazelab-vpopqmail_forwarder"));
        }
        
        $this->view->conflicts = MazelabVpopqmail_Model_DiFactory::getForwarderManager()
                ->getConflictedForwardersOfDomain($this->_domainId);
        
        $this->view->domainId = $this->_domainId;
        $this->view->pager = $this->getPager($this->_identity['_id'], $this->_domainId, 10);
    }
    
    public function addAction()
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        $form = new MazelabVpopqmail_Form_AddForwarder();
        $form->setDomainSelectByOwner($this->_identity['_id']);

        if($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $forwarderId = $forwarderManager->createForwarder($form->getValues());
                if ($forwarderId && ($forwarder = $forwarderManager->getForwarder($forwarderId))) {
                    $this->_redirect($this->view->url(array($forwarder->getEmail()),
                            'mazelab-vpopqmail_forwarderdetail'), array('exit' => false));
                }
            }
        }

        if($this->_domainId) {
            $form->showOnlyDomainAndDisableSelectbox($this->_domainId);
        }

        $this->view->domainId = $this->getParam('domainId', false);
        $this->view->form = $form;
    }
    
    public function detailAction()
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        if(!($forwarder = $forwarderManager->getForwarderByEmail($this->getParam('email'))) ||
                !$this->isOwner($forwarder->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_FORWARDER_NOT_FOUND, $this->getParam('email'));
            return $this->_forward('index');
        }
        
        $form = new MazelabVpopqmail_Form_Forwarder();
        $form->setAccountSelect($forwarder)->setForwarderTargets($forwarder);
        
        if($this->_request->getPost()) {
            $values = $form->getValidValues($this->_request->getPost());

            if (!empty($values) && $forwarderManager->updateForwarder($forwarder->getId(), $values)) {
                $this->view->result = true;
                $this->view->forwardTo = $forwarderManager->getForwarderTargets($forwarder->getId(), true);
            }
                
            $this->view->formErrors = $form->getMessages();
        }
        
        $data = $forwarder->getData();

        $this->view->forwarder = $data;
        $this->view->form = $form->disableAccountSelectboxIfEmpty()->setDefaults($data);
    }
    
    public function addtargetAction()
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        if(!($forwarder = $forwarderManager->getForwarderByEmail($this->getParam('email'))) ||
                !$this->isOwner($forwarder->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_FORWARDER_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        if($this->_request->getPost()) {
            $form = new MazelabVpopqmail_Form_Forwarder();
            $form->setValidatorForwarderTarget($forwarder->getId());
            
            if ($form->isValidPartial($this->getRequest()->getPost())) {
                if ($forwarderManager->addForwarderTarget($forwarder->getId(), $form->getValue('addForward'))) {
                    $this->view->result = true;
                    $this->view->forwardTo = $forwarder->getForwarderTargets(true);
                }
            }

            $this->view->formErrors = $form->getMessages();
        }
    }
    
    public function deleteAction()
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        if(!($forwarder = $forwarderManager->getForwarderByEmail($this->getParam('email'))) ||
                !$this->isOwner($forwarder->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_FORWARDER_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        $this->view->status = $forwarderManager->flagDelete($forwarder->getId());
    }
    
    public function changestateAction()
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        if(!($forwarder = $forwarderManager->getForwarderByEmail($this->getParam('email'))) ||
                !$this->isOwner($forwarder->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_FORWARDER_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        if($forwarderManager->changeForwarderState($forwarder->getId())) {
            if($forwarder->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
                $this->view->conflictedUrl = $this->view->url(array($forwarder->getEmail())
                        , 'mazelab-vpopqmail_diffForwarderDetail');
            }

            $this->view->forwarder = $forwarder->getData();
        }
    }
    
    public function diffAction()
    {
        $this->view->conflictsInDomains = MazelabVpopqmail_Model_DiFactory::getForwarderManager()
                ->getConflictedForwardersPerDomainByOwner($this->_identity['_id']);
    }
    
    public function diffdomainAction()
    {
        $this->view->conflicts = MazelabVpopqmail_Model_DiFactory::getForwarderManager()
                ->getConflictedForwardersOfDomain($this->_domainId);
        
        $this->view->domain = Core_Model_DiFactory::getDomainManager()
                ->getDomainAsArray($this->_domainId);
    }
    
    public function diffdetailAction()
    {
        $forwarderManager  = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        if(!($forwarder = $forwarderManager->getForwarderByEmail($this->getParam('email'))) ||
                !$this->isOwner($forwarder->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_FORWARDER_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        $form = new MazelabVpopqmail_Form_DiffForwarder();
        $form->initDiff($forwarder->getId());

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                $selectedForwarder = $this->_request->getPost();

                if ($forwarderManager->resolveForwarderConflicts($forwarder->getId(), $selectedForwarder)){
                    Core_Model_DiFactory::getMessageManager()
                            ->addSuccess(self::MESSAGE_CONFLICTS_RESOLVED);
                    $form->reset()->initDiff($forwarder->getId());
                }
            }
        }
        
        $this->view->form = $form;
        $this->view->forwarder = $forwarder->getData();
        $this->view->domain = Core_Model_DiFactory::getDomainManager()
                ->getDomainAsArray($forwarder->getData('domainId'));
    }

    /**
     * get account pager data
     * 
     * @param array|string $domainId
     * @param int $limit
     * @return array
     */
    public function getPager($ownerId, $domainId = null, $limit = null)
    {
        if(!$ownerId) {
            return array();
        }
        
        $pager = MazelabVpopqmail_Model_DiFactory::newForwarderPager($ownerId, $domainId);
        $pager->setLimit($limit);
        
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

