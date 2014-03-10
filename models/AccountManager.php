<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_AccountManager
{
    
    /**
     * message when the account allready exists
     */
    CONST ACCOUNT_EXISTS = 'Account %1$s allready exists';
    
    /**
     * message when domain was not found
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t find domain';
    
    /**
     * message when domain doesnt have a owner
     */
    CONST DOMAIN_WITHOUT_OWNER = 'Domain %1$s doesn\'t have a owner';
    
    /**
     * message when email account was deactivated
     */
    CONST MESSAGE_ACCOUNT_ACTIVATED = 'Email account %1$s was activated';    
    
    /**
     * message when a email account was created
     */
    CONST MESSAGE_ACCOUNT_CREATED = 'Email account %1$s was created';
    
    /**
     * message when email account is conflicted
     */
    CONST MESSAGE_ACCOUNT_CONFLICTED = 'email account %1$s is conflicted! Please resolve all conflicts before proceeding.';
    
    /**
     * message when email account was deactivated
     */
    CONST MESSAGE_ACCOUNT_DEACTIVATED = 'Email account %1$s was deactivated';
    
    /**
     * message when deleting a email account
     */
    CONST MESSAGE_ACCOUNT_DELETED = 'Email account %1$s was deleted';
    
    /**
     * message when a email account was updated
     */
    CONST MESSAGE_ACCOUNT_UPDATED = 'Email account %1$s was updated';
    
    /**
     * message when account update failed
     */
    CONST UPDATE_FAILED = 'Account could not be updated';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain account instance if registered
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Model_ValueObject_Account|null null if not registered
     */
    protected function _getRegisteredAccount($accountId)
    {
        if(!$this->isAccountRegistered($accountId)) {
            return null;
        }
        
        return MazelabVpopqmail_Model_DiFactory::getAccount($accountId);
    }
    
    /**
     * loads and registers a certain account instance
     * 
     * @param string $accountId
     * @return boolean
     */
    protected function _loadAccount($accountId)
    {
        $data = $this->getProvider()->getAccount($accountId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerAccount($accountId, $data);
    }
    
    /**
     * loads and registers a certain account instance by email
     * 
     * @param string $accountId
     * @return string|null account id
     */
    protected function _loadAccountByEmail($email)
    {
        $data = $this->getProvider()->getAccountByEmail($email);
        if(empty($data) || !key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerAccount($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * changes the status of a certain account
     * 
     * @param string $accountId
     * @return boolean
     */
    public function changeAccountState($accountId)
    {
        if(!($account = $this->getAccount($accountId))) {
            return false;
        }

        $currentStatus = $account->getStatus();
        
        if(!$currentStatus || $currentStatus === false) {
            if(!$account->activate()) {
                return false;
            }

            $this->_getLogger()->setMessage(self::MESSAGE_ACCOUNT_ACTIVATED);
        } else {
            // manual conflicts
            if($account->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
                Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_ACCOUNT_CONFLICTED, $account->getEmail());
                return false;    
            }

            if(!$account->deactivate()) {
                return false;
            }

            $this->_getLogger()->setMessage(self::MESSAGE_ACCOUNT_DEACTIVATED);
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setDomainRef($account->getDomain()->getId())
                ->setMessageVars($account->getEmail())
                ->setClientRef($account->getOwner()->getId())->save();

        return true;
    }
    
    /**
     * creates account in backend and on the nodes
     * 
     * @param array $data
     * @param boolean $encryptPassword encrypt given password
     * @return boolean||string Id of new email account
     */
    public function createAccount(array $data, $encryptPassword = true)
    {
        if(!key_exists('user', $data) 
                || !key_exists('password', $data)
                || !key_exists('domainId', $data)) {
            return false;
        }
        
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($data['domainId']))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        
        if(!($owner = $domain->getOwner())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_WITHOUT_OWNER, $domain->getName());
            return false;            
        }
        
        $email = $data['user'] . '@' . $domain->getName();
        $specialManager = MazelabVpopqmail_Model_DiFactory::getSpecialsManager();
        if ($this->getAccountByEmail($email) || $specialManager->getSpecialByEmail($email)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ACCOUNT_EXISTS, $email);
            return false;
        }
        
        if(key_exists('quota', $data) && !$data['quota']) {
            $data['quota'] = null;
        }
        if(key_exists('status', $data)) {
            $data["status"] = (boolean) $data["status"];
        }else {
            $data["status"] = true;
        }
        $data['domainName'] = $domain->getName();
        $data['ownerId'] = $owner->getId();
        $data['ownerName'] = $owner->getLabel();
        $data['label'] = $email;
        
        $account = MazelabVpopqmail_Model_DiFactory::newAccount();
        if(!$account->setData($data)->setPassword($data['password'], $encryptPassword)->save()) {
            return false;
        }
        
        $this->registerAccount($account->getId(), $account);
        
        if (($node = $account->getNode())){
            $this->_getLogger()->setNodeRef($node->getId());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ACCOUNT_CREATED)->setData($account->getData())
                ->setMessageVars($email)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($owner->getId())
                ->setDomainRef($domain->getId())->save();
        
        $account->apply();
        
        return $account->getId();
    }
    
    /**
     * deletes a certain account
     * 
     * @param string $accountId
     * @return boolean
     */
    public function deleteAccount($accountId)
    {
        if(!($account = $this->getAccount($accountId)) || !$account->removeCommands()) {
            return false;
        }
        
        if(!$this->getProvider()->deleteAccount($account)) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::getIndexManager()->unsetAccount($accountId);
        
        $email = $account->getEmail();
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ACCOUNT_DELETED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($account->getOwner()->getId())->setMessageVars($email)
                ->setDomainRef($account->getDomain()->getId())->save();
        
        foreach (MazelabVpopqmail_Model_DiFactory::getForwarderManager()->getForwardersOfTarget($email) as $forwarder) {
            $forwarder->deleteForwarderTarget(md5(strtolower($email)));
        }

        return true;
    }
    
    /**
     * deletes all accounts of the given domain
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function deleteAccountsByDomain($domainId)
    {
        if (!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return false;
        }

        foreach ($this->getAccountsByDomain($domain->getId()) as $account) {
            $this->deleteAccount($account->getId());
        }

        return sizeof($this->getAccountsByDomain($domain->getId())) == 0;
    }
    
    /**
     * set delete flag to certain account
     * 
     * @param string $accountId
     * @return boolean
     */
    public function flagDelete($accountId)
    {
        if(!($account = $this->getAccount($accountId))) {
            return false;
        }
        
        if(!$account->getNode()) {
            return $this->deleteAccount($accountId);
        }
        
        return $account->flagDelete();
    }
    
    /**
     * returns a certain account object 
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Model_ValueObject_Account
     */
    public function getAccount($accountId)
    {
        if(!$this->isAccountRegistered($accountId)) {
            $this->_loadAccount($accountId);
        }
        
        return $this->_getRegisteredAccount($accountId);
    }
    
    /**
     * returns the data set of a certain acctount as array
     * 
     * @param string $accountId
     * @return array
     */
    public function getAccountAsArray($accountId)
    {
        if(!$this->isAccountRegistered($accountId)) {
            $this->_loadAccount($accountId);
        }
        
        if(!($account = $this->_getRegisteredAccount($accountId))) {
            return array();
        }
        
        return $account->getData();
    }
    
    /**
     * gets a certain account by email
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_Account|null
     */
    public function getAccountByEmail($email)
    {
        if(($account = MazelabVpopqmail_Model_DiFactory::getAccountByEmail($email))) {
            return $account;
        }
        
        if(!($accountId = $this->_loadAccountByEmail($email))) {
            return null;
        }
        
        return $this->_getRegisteredAccount($accountId);
    }
    
    /**
     * gets a certain account by email
     * 
     * @param string $email
     * @return array
     */
    public function getAccountByEmailAsArray($email)
    {
        if(($account = MazelabVpopqmail_Model_DiFactory::getAccountByEmail($email))) {
            return $account->getData();
        }
        
        if(!($accountId = $this->_loadAccountByEmail($email))) {
            return array();
        }
        
        if(!($account = $this->_getRegisteredAccount($accountId))) {
            return array();
        }
                
        return $account->getData();
    }
    
    /**
     * get all accounts
     * 
     * @return array
     */
    public function getAccounts()
    {
        $accounts = array();
        foreach($this->getProvider()->getAccounts() as $accountId => $account) {
            $this->registerAccount($accountId, $account);
            $accounts[$accountId] = $this->getAccount($accountId);
        }
        
        return $accounts;
    }
    
    /**
     * get all accounts as array
     * 
     * @return array
     */
    public function getAccountsAsArray()
    {
        $accounts = array();
        foreach($this->getProvider()->getAccounts() as $accountId => $account) {
            $this->registerAccount($accountId, $account);
            $accounts[$accountId] = $account;
        }
        
        return $accounts;
    }
    
    /**
     * returns all accounts of a certain domain
     * 
     * @param string $domainId
     * @return array contains MazelabVpopqmail_Model_Valueobject_Account
     */
    public function getAccountsByDomain($domainId)
    {
        $accounts = array();
        
        foreach($this->getProvider()->getAccountsByDomain($domainId)
                as $accountId => $account) {
            $this->registerAccount($accountId, $account);
            $accounts[$accountId] = $this->getAccount($accountId);
        }
        
        return $accounts;
    }
    
    /**
     * returns a data set of all accoutns of a certain client
     * 
     * @param string $clientId
     * @return array
     */
    public function getAccountsByOwner($clientId)
    {
        $accounts = array();

        foreach(MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByOwner($clientId) as $domainId => $domain) {
            foreach($this->getAccountsByDomain($domainId) as $accountId => $account) {
                $accounts[$accountId] = $account;
            }
        }
        
        return $accounts;
    }
    
    /**
     * returns all conflicted email accounts of the given client
     * 
     * @param string $clientId
     * @return array
     */
    public function getConflictedAccountsByOwner($clientId)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $entries = array();
        
        foreach ($logManager->getClientContextLogs($clientId
                , Core_Model_Logger::TYPE_CONFLICT,
                MazelabVpopqmail_Model_ValueObject_Account::LOG_ACTION_ACCOUNT_CONFLICT)
                as $entry) {
            if(!key_exists('contextId', $entry)) {
                continue;
            }
            
            $entries[$entry['contextId']] = $entry;
        }
        
        return $entries;
    }
    
    /**
     * get conflicted email accounts of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getConflictedAccountsByDomain($domainId)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $entries = array();
        
        foreach ($logManager->getDomainContextLogs($domainId
                , Core_Model_Logger::TYPE_CONFLICT,
                MazelabVpopqmail_Model_ValueObject_Account::LOG_ACTION_ACCOUNT_CONFLICT)
                as $entry) {
            if(!key_exists('contextId', $entry)) {
                continue;
            }
            
            $entries[$entry['contextId']] = $entry;
        }
        
        return $entries;
    }
    
    /**
     * get all domains witch has conflicted email accounts
     * 
     * @param string $clientId
     * @return array
     */
    public function getConflictedAccountsPerDomainByOwner($clientId)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $entries = array();
        
        foreach ($logManager->getClientContextLogs($clientId
                , Core_Model_Logger::TYPE_CONFLICT,
                MazelabVpopqmail_Model_ValueObject_Account::LOG_ACTION_ACCOUNT_CONFLICT)
                as $entry) {
            if(!key_exists('contextId', $entry) || !isset($entry['domain']['label'])) {
                continue;
            }
            
            $entries[$entry['domain']['label']][$entry['contextId']] = $entry;
        }

        return $entries;
    }
    
    /**
     * returns data provider for account context
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Account
     */
    public function getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getAccount();
    }
    
    /**
     * gets remote values of a certain email account as array
     * 
     * @param string $accountId
     * @return array
     */
    public function getRemoteValuesOfAccount($accountId)
    {
        if(!($account = $this->getAccount($accountId))) {
            return array();
        }
        
        if(!($remoteValues = $account->getRemoteData()) || !is_array($remoteValues)) {
            return array();
        }
        
        return $remoteValues;
    }

    /**
     * import a new email account from report
     * 
     * @param string $domainId
     * @param array $data
     * @return boolean
     */
    public function importAccountFromReport($domainId, array $data)
    {
        if(!key_exists('email', $data)) {
            return false;
        }
        
        $data['user'] = substr($data['email'], 0, strpos($data['email'], "@"));
        $data['domainId'] = $domainId;
        
        $form = new MazelabVpopqmail_Form_AddAccount();
        if(!$form->setDomainSelectByDomain($domainId)->isValid($data) || 
                !($accountId = $this->createAccount($data, false)))  {
            return false;
        }

        if(!($account = $this->getAccount($accountId))) {
            return false;
        }
        
        $account->setRemoteData($data)->save();
        MazelabVpopqmail_Model_DiFactory::getApplyAccount()->remove($account);
        
        return true;
    }
    
    /**
     * checks if a certain account instance is allready registered
     * 
     * @param string $accountId
     * @return boolean
     */
    public function isAccountRegistered($accountId)
    {
        if(MazelabVpopqmail_Model_DiFactory::isAccountRegistered($accountId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a account instance
     * 
     * overwrites existing instances
     * 
     * @param string $accountId
     * @param mixed $context array or MazelabVpopqmail_Model_ValueObject_Account
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerAccount($accountId, $context, $setLoadedFlag = true)
    {
        $account = null;
        
        if(is_array($context)) {
            $account = MazelabVpopqmail_Model_DiFactory::newAccount($accountId);
            
            if($setLoadedFlag) {
                $account->setLoaded(true);
            }
            
            $account->getBean()->setBean($context);
        } elseif($context instanceof MazelabVpopqmail_Model_ValueObject_Account) {
            $account = $context;
        }
        
        if(!$account) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::registerAccount($accountId, $account);
        
        return true;
    }
    
    /**
     * resolves conflicts in an email account with the given data
     * 
     * @param string $accountId
     * @param array $data
     * @return array
     */
    public function resolveAccountConflicts($accountId, array $data)
    {
        if(!($account = $this->getAccount($accountId))) {
            return false;
        }
        
        if(key_exists('quota', $data) && (!$data['quota'] || $data['quota'] == "")) {
            $data['quota'] = null;
        }
        
        $account->setData($data);
        $account->save();
        
        if(!$account->apply()) {
            return false;
        }
        
        if(!$account->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
            $account->resolveConflictLog();
        }
        
        return true;
    }
    
    /**
     * unregisters a certain account instance
     * 
     * @param string $accountId
     * @return boolean
     */
    public function unregisterAccount($accountId)
    {
        if(!$this->_getRegisteredAccount($accountId)) {
            return true;
        }
        
        MazelabVpopqmail_Model_DiFactory::unregisterAccount($accountId);
    }
    
    /**
     * updates a certain account with the given data
     * 
     * @param string $accountId
     * @param array $data
     * @return boolean
     */
    public function updateAccount($accountId, $data)
    {
        if(!($account = $this->getAccount($accountId))) {
            return false;
        }
        
        if(!($domain = $account->getDomain())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        
        if(key_exists('password', $data)) {
            $account->setPassword($data['password']);
            unset($data['password']);
        }
        if(key_exists('quota', $data) && (!$data['quota'] || $data['quota'] == "")) {
            $data['quota'] = null;
        }
        
        if(!$account->setData($data)->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::UPDATE_FAILED);
            return false;
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ACCOUNT_UPDATED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($account->getOwner()->getId())
                ->setMessageVars($account->getEmail())
                ->setDomainRef($domain->getId())->setData($data)
                ->save();
        
        if($account->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_ACCOUNT_CONFLICTED, $account->getEmail());
            return true;
        }
        
        $account->apply();
        
        return true;
    }

}
