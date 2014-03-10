<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_AccountsController extends Zend_Controller_Action
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
     * message when account wasn't found
     */
    CONST MESSAGE_ACCOUNT_NOT_FOUND = 'Email account %1$s not found';

    /**
     * message when conflicts were resolved
     */
    CONST MESSAGE_CONFLICTS_RESOLVED = "Differences resolved!";
    
    /**
     * message when deleting a import account
     */
    CONST MESSAGE_IMPORT_DELETE_FAILED = 'Account %1$s was added but could\'t be automaticaly deleted. Please delete %1$s manually';

    /**
     * message when import account has a invalid context
     */
    CONST MESSAGE_IMPORT_INVALID = 'Invalid context for account import';
    
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
                    ->addActionContext('changestate', 'json')
                    ->addActionContext('index', array('html'))
                    ->addActionContext('domain', array('html'))
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
        
        $this->view->conflicts = MazelabVpopqmail_Model_DiFactory::getAccountManager()
                ->getConflictedAccountsByOwner($this->_identity['_id']);
        
        $this->view->client = $this->_identity;
        $this->view->pager = $this->getPager($this->_identity['_id'], null, 10);
    }
    
    public function domainAction()
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        $this->view->conflicts = $accountManager->getConflictedAccountsByDomain($this->_domainId);
        $domain = Core_Model_DiFactory::getDomainManager()->getDomainAsArray($this->_domainId);
        
        if(!$this->getRequest()->isXmlHttpRequest() && $this->isOwner($domain["owner"])) {
            $this->view->domain = $domain;
        }else {
            setcookie("domainId");
            $this->redirect($this->view->url(array(), "mazelab-vpopqmail_accounts"));
        }
        
        $this->view->domainId = $this->_domainId;
        $this->view->pager = $this->getPager($this->_identity['_id'], $this->_domainId, 10);
    }
    
    public function addAction()
    {
        $clientId = $this->_identity['_id'];
        
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        $form = new MazelabVpopqmail_Form_AddAccount();
        
        $form->setDomainSelectByOwner($clientId)
             ->setQuotaLimitValidator($clientId)
             ->setAccountLimitValidator($clientId);
        
        if($this->getRequest()->getPost() && $form->isValid($this->getRequest()->getPost())) {
            if(($accountId = $accountManager->createAccount($form->getValues()))) {
                $account = $accountManager->getAccount($accountId);
                
                $redirector = $this->_helper->getHelper('Redirector');
                $redirector->gotoRoute(array($account->getEmail()), "mazelab-vpopqmail_accountdetail");
                return;
            }
        }
        
        if($this->_domainId) {
            $form->showOnlyDomainAndDisableSelectbox($this->_domainId);
        }
        
        $this->view->form = $form;
        $this->view->domainId = $this->_domainId;
    }
    
    public function detailAction()
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        if(!($account = $accountManager->getAccountByEmail($this->getParam('email'))) ||
                !$this->isOwner($account->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_ACCOUNT_NOT_FOUND, $this->getParam('email'));
            return $this->_forward('index');
        }
        
        $form = new MazelabVpopqmail_Form_Account();
        
        if($this->_request->getPost()) {
            $form->setQuotaLimitValidator($account->getId());
            $values = $form->getValidValues($this->_request->getPost());
            
            if (!empty($values)) {
                $this->view->result = $accountManager->
                        updateAccount($account->getId(), $values);
            }

            $this->view->formErrors = $form->getMessages();
        }
        
        $form->setUsedForwarders($account->getId())
             ->setAvailableForwarders($account->getId())
             ->disableAccountSelectboxIfEmpty();

        $data = $account->getData();
        
        $this->view->account = $data;
        $this->view->form = $form->setDefaults($data);
        
        $this->view->addBasePath(realpath(
              $this->getFrontController()->getControllerDirectory('core') . '/../views'));
    }
    
    public function deleteAction()
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        if(!($account = $accountManager->getAccountByEmail($this->getParam('email'))) ||
                !$this->isOwner($account->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_ACCOUNT_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        $this->view->status = $accountManager->flagDelete($account->getId());
    }
    
    public function changestateAction()
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        if(!($account = $accountManager->getAccountByEmail($this->getParam('email'))) ||
                !$this->isOwner($account->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_ACCOUNT_NOT_FOUND, $this->getParam('email'));
            return null;
        }
        
        if($accountManager->changeAccountState($account->getId())) {
            if($account->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
                $this->view->conflictedUrl = $this->view->url(array($account->getEmail())
                        , 'mazelab-vpopqmail_diffAccountDetail');
            }
            
            $this->view->account = $account->getData();
        }
    }
    
    public function diffAction()
    {
        $this->view->conflictsInDomains = MazelabVpopqmail_Model_DiFactory::getAccountManager()
                ->getConflictedAccountsPerDomainByOwner($this->_identity['_id']);
    }
    
    public function diffdomainAction()
    {
        $this->view->conflicts = MazelabVpopqmail_Model_DiFactory::getAccountManager()
                ->getConflictedAccountsByDomain($this->_domainId);
        
        $this->view->domain = Core_Model_DiFactory::getDomainManager()->getDomainAsArray($this->_domainId);
    }
    
    public function diffdetailAction()
    {
        $accountManager  = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        if(!($account = $accountManager->getAccountByEmail($this->getParam('email'))) ||
                !$this->isOwner($account->getData("ownerId"))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_ACCOUNT_NOT_FOUND, $this->getParam('email'));
            return $this->_forward('index');
        }
        
        $form = new MazelabVpopqmail_Form_DiffAccountLocal();
        $formRemote = new MazelabVpopqmail_Form_DiffAccountRemote();
        
        $form->setQuotaLimitValidator($account->getId());
        $formRemote->setQuotaLimitValidator($account->getId());
        
        if($this->getRequest()->isPost()) {
            if($this->getParam('remoteForm', false)) {
                $validatingForm = $formRemote;
            } else {
                $validatingForm = $form;
            }
            
            if($validatingForm->isValid($this->getRequest()->getPost())) {
                $accountManager->resolveAccountConflicts($account->getId()
                        , $validatingForm->getValues());
                Core_Model_DiFactory::getMessageManager()
                        ->addSuccess(self::MESSAGE_CONFLICTS_RESOLVED);
            }
        }
        
        $data = $account->getData();
        
        $this->view->account = $data;
        $this->view->domain = Core_Model_DiFactory::getDomainManager()
                ->getDomainAsArray($account->getData('domainId'));
        $this->view->form = $form->setDefaults($data);
        $this->view->formRemote = $formRemote->setDefaults($accountManager
                ->getRemoteValuesOfAccount($account->getId()));
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
        
        $pager = MazelabVpopqmail_Model_DiFactory::newAccountPager($ownerId, $domainId);
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

