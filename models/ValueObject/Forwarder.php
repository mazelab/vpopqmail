<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * 
 */
class MazelabVpopqmail_Model_ValueObject_Forwarder 
    extends MazelabVpopqmail_Model_ValueObject
{
    
    /**
     * message when apply resulted in an error
     */
    CONST APPLY_ERROR = 'Apply error in node %1$s';
    
    /**
     * message when forwarder is deactivated
     */
    CONST DEACTIVATED = 'The forwarder is deactivated';
    
    /**
     * message when domain was not found
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t load domain';
    
    /**
     * message when saving resulted in an error
     */
    CONST ERROR_SAVING = 'Something went wrong while saving forwarder %1$s';
    
    /**
     * log action for conflicted forwarder
     */
    CONST LOG_ACTION_FORWARDER_CONFLICT = 'conflicted forwarder';
    
    /**
     * log action for a resolved forwarder conflict
     */
    CONST LOG_ACTION_FORWARDER_CONFLICT_RESOLVED = 'resolved conflicted forwarder';
    
    /**
     * message when forwarder is conflicted
     */
    CONST MESSAGE_FORWARDER_CONFLICT = 'Forwarder %1$s is conflicted';
    
    /**
     * message when forwarder conflict was resolved
     */
    CONST MESSAGE_FORWARDER_CONFLICT_RESOLVED = 'Resolved conflicted forwarder %1$s';
    
    /**
     * message when there is no node assigned
     */
    CONST MESSAGE_NODE_UNASSIGNED = 'There is no node assigned for domain %1$s. Therefore the changes can\'t be applied';
    
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
                ->setMessage(self::MESSAGE_FORWARDER_CONFLICT)
                ->setMessageVars($this->getEmail())->setData($this->getConflicts())
                ->setAction(self::LOG_ACTION_FORWARDER_CONFLICT)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setRoute(array($this->getEmail()), 'MazelabVpopqmail_diffForwarderDetail')
                ->setDomainRef($this->getDomain()->getId())
                ->setClientRef($this->getOwner()->getId())
                ->saveByContext($this->getEmail());
    }
    
    /**
     * returns data backend provider
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
     */
    public function _getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getForwarder();
    }
    
    /**
     * loads context from data backend
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getForwarder($this->getId());
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * @var array $unmappedData from bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedData)
    {
        $id = $this->_getProvider()->saveForwarder($unmappedData, $this->getId());

        if (!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getEmail());
            return false;
        }

        return $id;
    }
    
    /**
     * adds given email into forwarder target
     * 
     * @param string $email
     * @return string|boolean false or key for email entry
     */
    public function addForwarderTarget($email)
    {
        if (is_null($email)) {
            return false;
        }
        
        // fill the depending account with the forwarder information
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        if(($account = $accountManager->getAccountByEmail($email))) {
            $account->setForwarderRef($this->getId(), $this->getEmail());
        }
        
        $key = md5(strtolower($email));
        $data = array(
            'forwardTo' => array(
                $key => $email
            )
        );
        
        $this->setData($data);
        if (!$this->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getEmail());
            return false;
        }

        $this->apply();
        
        return $key;
    }
    
    /**
     * activates this forwarder
     * 
     * @return boolean
     */
    public function activate()
    {
        foreach($this->getForwarderTargets() as $targetId => $target) {
            $this->setRemoteData(array("forwardTo/{$targetId}" => null));
            $this->setData(array("forwardTo/{$targetId}" => $target));
        }
        
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
        return MazelabVpopqmail_Model_DiFactory::getApplyForwarder()->apply($this, $save);
    }
    
    /**
     * deactivates this forwarder
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
     * deletes a forwarder target
     * 
     * @param string $targetId
     * @return boolean
     */
    public function deleteForwarderTarget($targetId)
    {
        if (!$this->getId() || is_null($targetId)) {
            return false;
        }

        $targets = $this->getForwarderTargets(false, false);
        if(!key_exists($targetId, $targets)) {
            return true;
        }
        
        if(!$this->unsetProperty("forwardTo/$targetId")->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getEmail());
            return false;
        }
        
        $this->apply();
        
        return true;
    }
    
    /**
     * evaluates the given reported Data in reference to this object
     * 
     * @param array $data
     * @return boolean
     */
    public function evalReport($data)
    {
         if($this->getData('delete')) {
            if(key_exists('status', $data) && !$data['status']) {
                return MazelabVpopqmail_Model_DiFactory::getForwarderManager()->deleteForwarder($this->getId());
            }

            return $this->apply(false);
        }
        
        if($this->getStatus() && (key_exists('status', $data) && $data['status'])) {
            foreach ($this->getForwarderTargets(false, false) as $targetId => $target) {
                if(!$target && !isset($data['forwardTo'][$targetId])) {
                    $this->unsetProperty("forwardTo/$targetId");
                } elseif($target && isset($data['forwardTo'][$targetId])){
                    // fix case sensitive email
                    $data['forwardTo'][$targetId] = $target;
                } elseif(!isset($data['forwardTo'][$targetId])){
                    $data['forwardTo'][$targetId] = null;
                }
            }
        }
        
        if(!$this->setRemoteData($data)->save()) {
            return false;
        }
        
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
     * deletes this forwarder on node and if it succeeds in the data backend
     * 
     * @return boolean
     */
    public function flagDelete()
    {
        if(!$this->getId()) {
            return false;
        }
        
        if(!$this->setProperty('delete', true)->save()) {
            return false;
        }
        
        return $this->apply();
    }
    
    /**
     * returns the bean with the loaded data from data backend
     * 
     * @param boolean $new force new bean struct
     * @return MazelabVpopqmail_Model_Bean_Forwarder
     */
    public function getBean($new = false)
    {
        if ($new || !$this->_valueBean || !$this->_valueBean instanceof MazelabVpopqmail_Model_Bean_Forwarder) {
                $this->_valueBean = new MazelabVpopqmail_Model_Bean_Forwarder();
        }

        $this->load();

        return $this->_valueBean;
    }
    
    /**
     * returns domain object of this instance
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
     * returns registered forwarder targets
     * 
     * @param boolean $sort sort forwarder target
     * @param boolean $keepEmpty removes entry from return if local value is null
     * @return array
     */
    public function getForwarderTargets($sort = false, $keepEmpty = true)
    {
        $targets = $this->getData('forwardTo');
        $returns = array();
        $created = array();

         if (!is_array($targets)) {
            return array();
         }
         
         foreach($targets as $targetId => $target) {
            if($keepEmpty && !$target){
                unset($targets[$targetId]);
                continue;
            }
            $created[$targetId] = $this->getData("forwardTo/$targetId/created");
         }
         
         if ($sort == false) {
            return $targets;
         }

        asort($created, SORT_ASC);
        foreach ($created as $targetId => $timestamp) {
           $returns[$targetId] = $targets[$targetId];
        }

        return $returns;
    }
    
    /**
     * get node for this forwarder
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
     * returns the owner of this forwarder
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
     * removes commands from current node
     */
    public function removeCommands()
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyForwarder()->remove($this);
    }
    
    /**
     * if corresponding log entry exists it will be changed into resolved state
     * 
     * @return boolean
     */
    public function resolveConflictLog()
    {
        $loggedConflict = Core_Model_DiFactory::getLogManager()->getContextLog($this->getEmail(),
                Core_Model_Logger::TYPE_CONFLICT, self::LOG_ACTION_FORWARDER_CONFLICT);

        if(!$loggedConflict) {
                return true;
        }

        $this->_getLogger()->setMessage(self::MESSAGE_FORWARDER_CONFLICT_RESOLVED)
                ->setMessageVars($this->getEmail())
                ->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setAction(self::LOG_ACTION_FORWARDER_CONFLICT_RESOLVED);
        
        $this->_getLogger()->saveByContext($this->getEmail(),
                Core_Model_Logger::TYPE_CONFLICT, self::LOG_ACTION_FORWARDER_CONFLICT);
        
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
            MazelabVpopqmail_Model_DiFactory::getIndexManager()->setForwarder($this->getId());
        }
        
        return true;
    }
    
    /**
     * sets forwarder reference to email accounts which ar targets of this forwarder
     * 
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder
     */
    public function setAccountRefs()
    {
        foreach($this->getForwarderTargets() as $target) {
            if(!($account = MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountByEmail($target))) {
                continue;
            }
            
            $account->setForwarderRef($this->getId(), $this->getEmail());
        }
        
        return $this;
    }
    
    /**
     * sets/adds new data set
     * 
     * @param array $data
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder
     */
    public function setData(array $data)
    {
        if(key_exists('label', $data)) {
            $this->_rebuildSearchIndex = true;
        }
        
        return parent::setData($data);
    }
    
    /**
     * sets/adds new data set as remote data
     * 
     * additional cast boolean
     * 
     * @param array $data
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder
     */
    public function setRemoteData($data)
    {
        if(key_exists('status', $data) && (empty($data['status']) || $data['status'] === 'false')) {
            $data['status'] = false;
        } elseif(key_exists('status', $data)) {
            $data['status'] = true;
        }
        
        $this->getBean()->setRemoteData($data);

        return $this;
    }
    
}

