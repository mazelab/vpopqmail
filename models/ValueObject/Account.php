<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * 
 */
class MazelabVpopqmail_Model_ValueObject_Account 
    extends MazelabVpopqmail_Model_ValueObject
{

    /**
     * message when apply resulted in an error
     */
    CONST APPLY_ERROR = 'Apply error in node %1$s';
    
    /**
     * message when account is deactivated
     */
    CONST DEACTIVATED = 'The account is deactivated';
    
    /**
     * message when domain was not found
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t find domain';
    
    /**
     * message when saving resulted in an error
     */
    CONST ERROR_SAVING = 'Something went wrong while saving account %1$s';
    
    /**
     * exception message when account object has a corrupt state
     */
    CONST EXCEPTION_OBJECT_CORRUPT = 'Account object not correctly initialized!';
    
    /**
     * log action for conflicted email accounts
     */
    CONST LOG_ACTION_ACCOUNT_CONFLICT = 'conflicted email account';
    
    /**
     * log action for a resolved email account conflict
     */
    CONST LOG_ACTION_ACCOUNT_CONFLICT_RESOLVED = 'resolved conflicted email account';
    
    /**
     * message when email account is conflicted
     */
    CONST MESSAGE_ACCOUNT_CONFLICT = 'Email account %1$s is conflicted';
    
    /**
     * message when email account conflict was resolved
     */
    CONST MESSAGE_ACCOUNT_CONFLICT_RESOLVED = 'Resolved conflicted email account %1$s';
    
    /**
     * message when there is no node assigned
     */
    CONST MESSAGE_NODE_UNASSIGNED = 'There is no node assigned for domain %1$s. Therefore the changes can\'t be applied';
    
    /**
     * search category
     */
    CONST SEARCH_CATEGORY = 'vpopqmail-account';
    
    /**
     * @var boolean
     */
    protected $_rebuildSearchIndex;
    
    /**
     * sets conflict entry in log
     * 
     * @return void
     */
    protected function _addConflictToLog()
    {
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_CONFLICT)
                ->setMessage(self::MESSAGE_ACCOUNT_CONFLICT)
                ->setMessageVars($this->getEmail())
                ->setAction(self::LOG_ACTION_ACCOUNT_CONFLICT)
                ->setData($this->getConflicts())->setDomainRef($this->getDomain()->getId())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setRoute(array($this->getEmail()), 'MazelabVpopqmail_diffAccountDetail')
                ->setClientRef($this->getOwner()->getId())
                ->saveByContext($this->getEmail());
    }
    
    /**
     * encrypts given password into a vpopmail password
     * 
     * @param string $password
     * @return string|null
     */
    protected function _encryptPassword($password)
    {
        if(!is_string($password)) {
            return null;
        }
        
        srand ((double)microtime()*1000000);
        $salt = '$1$';
        
        for ($i = 0; $i < 5; $i++) {
            $retval = 'a';
            
            $rand = rand() % 64;
            if ($rand < 26) {
                $retval = $rand + 'a';
            } elseif ($rand > 25) {
                $retval = $rand - 26 + 'A';
            } elseif ($rand > 51) {
                $retval = $rand - 52 + '0';
            } elseif ($rand == 62) {
                $retval = ';';
            } elseif ($rand == 63) {
                $retval = '.';
            }
            
            $salt .= $retval;
        }
        
        $salt .= '0';
        
        return crypt($password, $salt);
    }
    
    /**
     * returns data backend provider
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Account
     */
    public function _getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getAccount();
    }
    
    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * override it with your own loading methods
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getAccount($this->getId());
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * @var array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedContext)
    {
        $id = $this->_getProvider()->saveAccount($unmappedContext, $this->getId());
        
        if(!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getEmail());
            return false;
        }
        
        return $id;
    }
    
    /**
     * activates this account
     * 
     * @return boolean
     */
    public function activate()
    {
        if(!$this->setData(array('status' => true))->save()) {
            return false;
        }
        
        if($this->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
            $this->_addConflictToLog();
        }
        
        $this->apply();
        
        return true;
    }
    
    /**
     * apply current configuration
     * 
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply($save = true)
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyAccount()->apply($this, $save);
    }
    
    /**
     * deactivates this account
     * 
     * @return boolean
     */
    public function deactivate()
    {
        if(!$this->setData(array('status' => false))->save()) {
            return false;
        }
        
        $this->resolveConflictLog();
        
        $this->apply();
        
        return true;
    }
    
    /**
     * deletes the reference of a certain forwarder
     * 
     * @param string $forwarderId
     * @return boolean
     */
    public function deleteForwarderRef($forwarderId)
    {
        if (!$this->getId() || is_null($forwarderId)) {
            return false;
        }
        
        $path = 'forwarder/' . $forwarderId;
        if(!$this->getData($path)) {
            return true;
        }
        
        return $this->unsetProperty($path)->save();
    }
    
    /**
     * deletes this account on the node and if it succeeds in the data backend
     * 
     * @return boolean
     */
    public function flagDelete()
    {
        if(!$this->getId()) {
            return false;
        }
        
        if(!($node = $this->getNode())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addNotification(self::MESSAGE_NODE_UNASSIGNED, $this->getData('domainName'));
            return false;
        }
        
        if(!$this->setProperty('delete', true)->save()) {
            return false;
        }
        
        return $this->apply();
    }
    
    /**
     * evaluates the given reported Data in reference to this object
     * 
     * @param array|null $data
     * @return boolean
     */
    public function evalReport($data)
    {
        if($this->getData('delete')) {
            if(key_exists('status', $data) && !$data['status']) {
                return MazelabVpopqmail_Model_DiFactory::getAccountManager()->deleteAccount($this->getId());
            }
            
            return $this->apply(false);
        }
        
        $this->setRemoteData($data)->save();
        
        if($this->getStatus() && $this->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
            $this->_addConflictToLog();
        } elseif ($this->getConflicts()) {
            $this->apply(false);
        } else {
            $this->resolveConflictLog();
        }
        
        return true;
    }
    
    /**
     * returns the Bean with the loaded data from data backend
     * 
     * @param boolean $new force new bean struct
     * @return MazelabVpopqmail_Model_Bean_Account
     */
    public function getBean($new = false)
    {
        if($new || !$this->_valueBean || !$this->_valueBean instanceof MazelabVpopqmail_Model_Bean_Account) {
            $this->_valueBean = new MazelabVpopqmail_Model_Bean_Account();
        }
        
        $this->load();
        
        return $this->_valueBean;
    }
    
    /**
     * returns domain object of this account
     * 
     * @return MazelabVpopqmail_Model_ValueObject_Domain|null
     */
    public function getDomain()
    {
        if(!($domainId = $this->getData('domainId'))) {
            return null;
        }
        
        return Core_Model_DiFactory::getDomainManager()->getDomain($domainId);
    }
    
    /**
     * returns complete email string
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('label');
    }
    
    /**
     * get node for this account
     * 
     * @return Core_Model_ValueObject_Node|null
     */
    public function getNode()
    {
        if(!($domainId = $this->getData('domainId'))) {
            return null;
        }
        
        return MazelabVpopqmail_Model_DiFactory::getNodeManager()->getNodeOfDomain($domainId);
    }
    
    /**
     * returns the owner of this account
     * 
     * @return Core_Model_ValueObject_Client|null
     */
    public function getOwner()
    {
        if(!($domainId = $this->getData('domainId'))) {
            return null;
        }
        
        return Core_Model_DiFactory::getClientManager()->getClientByDomain($domainId);
    }
    
    /**
     * returns status flag if set
     * 
     * @return boolean
     */
    public function getStatus()
    {
        return $this->getData('status');
    }
    
    /**
     * returns quota from data set
     * 
     * @return int
     */
    public function getQuota()
    {
        return $this->getData('quota');
    }
    
    /**
     * removes commands from current node
     */
    public function removeCommands()
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyAccount()->remove($this);
    }
    
    /**
     * if corresponding log entry exists it will be changed into resolved state
     * 
     * @return boolean
     */
    public function resolveConflictLog()
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $loggedConflict = $logManager->getContextLog($this->getEmail(),
                Core_Model_Logger::TYPE_CONFLICT, self::LOG_ACTION_ACCOUNT_CONFLICT);

        if(!$loggedConflict) {
            return true;
        }

        $this->_getLogger()->setMessage(self::MESSAGE_ACCOUNT_CONFLICT_RESOLVED)
                ->setMessageVars($this->getEmail())
                ->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setAction(self::LOG_ACTION_ACCOUNT_CONFLICT_RESOLVED);
        
        $this->_getLogger()->saveByContext($this->getEmail(),
                Core_Model_Logger::TYPE_CONFLICT, self::LOG_ACTION_ACCOUNT_CONFLICT);
        
        return true;
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * calls _save
     * 
     * @return boolean
     */
    public function save()
    {
        if(!parent::save()) {
            return false;
        }
        
        if($this->_rebuildSearchIndex) {
            $this->_rebuildSearchIndex = false;
            MazelabVpopqmail_Model_DiFactory::getIndexManager()->setAccount($this->getId());
        }
        
        return true;
    }
    
    /**
     * sets/adds new data set
     * 
     * @param array $data
     * @return MazelabVpopqmail_Model_ValueObject_Account
     */
    public function setData(array $data)
    {
        if(key_exists('label', $data)) {
            $this->_rebuildSearchIndex = true;
        }
        
        return parent::setData($data);
    }
    
    /**
     * sets the reference of a certain forwarder
     * 
     * @param string $forwarderId
     * @param string $email can be set to overwrite or just to avoid forwarder loading
     * @return boolean
     */
    public function setForwarderRef($forwarderId, $email = null)
    {
        if (!$this->getId() || is_null($forwarderId)) {
            return false;
        }
        
        if(!$email && !($forwarder = MazelabVpopqmail_Model_DiFactory::getForwarderManager()->getForwarder($forwarderId))) {
            return false;
        } elseif(!$email) {
            $email = $forwarder->getEmail();
        }
        
        return $this->setProperty("forwarder/$forwarderId" , $email)->save();
    }
    
    /**
     * sets password for this account
     * 
     * @param string $password
     * @param boolean $encrypt
     * @return MazelabVpopqmail_Model_ValueObject_Account
     */
    public function setPassword($password, $encrypt = true)
    {
        if(is_string($password)) { 
            if($encrypt) {
                $password = $this->_encryptPassword($password);
            }

            $this->setProperty('password', $password);
        }
        
        return $this;
    }
    
    /**
     * sets/adds new data set as remote data
     * 
     * additional boolean cast
     * 
     * @param array $data
     * @return MazelabVpopqmail_Model_ValueObject_Account
     */
    public function setRemoteData($data)
    {
        // cast boolean values
        if(key_exists('status', $data) && !((bool) $data['status'])) {
            $data['status'] = false;
        } elseif(key_exists('status', $data)) {
            $data['status'] = true;
        }
        
        if(key_exists('quota', $data) && empty($data['quota'])) {
            $data['quota'] = null;
        }
        
        $this->getBean()->setRemoteData($data);
        
        return $this;
    }
    
}
