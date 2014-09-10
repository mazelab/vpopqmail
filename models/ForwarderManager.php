<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_ForwarderManager
{

    /**
     * message when forwarder allready exists
     */
    CONST ACCOUNT_EXISTS = 'Account %1$s allready exists';
    
    /**
     * message when forwarder is conflicted
     */
    CONST CONFLICTED_FORWARDER = 'forwarder %1$s is conflicted! Please resolve all conflicts before proceeding.';
    
    /**
     * message when domain wasnt available
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t find domain';
    
    /**
     * message when domain has no owner
     */
    CONST DOMAIN_WITHOUT_OWNER = 'Domain %1$s doesn\'t have a owner';

    /**
     * message when forwarder was deactivated
     */
    CONST MESSAGE_FORWARDER_ACTIVATED = 'Forwarder %1$s was activated';    
    
    /**
     * message when a forwarder was created
     */
    CONST MESSAGE_FORWARDER_CREATED = 'Forwarder %1$s created';
    
    /**
     * message when forwarder was deactivated
     */
    CONST MESSAGE_FORWARDER_DEACTIVATED = 'Forwarder %1$s was deactivated';
    
    /**
     * message when deleting a forwarder
     */
    CONST MESSAGE_FORWARDER_DELETED = 'Forwarder %1$s was deleted';
    
    /**
     * message when a forwarder was updated
     */
    CONST MESSAGE_FORWARDER_UPDATED = 'Forwarder %1$s was updated';
    
    /**
     * message when update failed
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
     * returns a certain forwarder instance if registered
     * 
     * @param string $forwarderId
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder|null null if not registered
     */
    protected function _getRegisteredForwarder($forwarderId)
    {
        if(!$this->isForwarderRegistered($forwarderId)) {
            return null;
        }
        
        return MazelabVpopqmail_Model_DiFactory::getForwarder($forwarderId);
    }
    
    /**
     * loads and registers a certain forwarder instance
     * 
     * @param string $forwarderId
     * @return boolean
     */
    protected function _loadForwarder($forwarderId)
    {
        $data = $this->getProvider()->getForwarder($forwarderId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerForwarder($forwarderId, $data);
    }

    /**
     * loads and registers a certain forwarder instance by email
     * 
     * @param string $forwarderId
     * @return string|null forwarder id
     */
    protected function _loadForwarderByEmail($email)
    {
        $data = $this->getProvider()->getForwarderByEmail($email);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerForwarder($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * adds given email as target of the given forwarder
     * 
     * @param string $accountId
     * @param string $email
     * @return string|boolean
     */
    public function addForwarderTarget($accountId, $email)
    {
        $forwarder = $this->getForwarder($accountId);

        if(!$forwarder) {
            return false;
        }
        
        if(!($domain = $forwarder->getDomain())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }

        if(!($key = $forwarder->addForwarderTarget($email))) {
            return false;
        }

        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_FORWARDER_UPDATED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($forwarder->getOwner()->getId())
                ->setMessageVars($forwarder->getEmail())
                ->setDomainRef($domain->getId())->setData(array('forwarder' => $email))
                ->save();
        
        return $key;
    }
    
    /**
     * changes the status of a certain forwarder
     * 
     * @param string $accountId
     * @return boolean
     */
    public function changeForwarderState($accountId)
    {
        $forwarder = $this->getForwarder($accountId);
        $currentStatus = $forwarder->getStatus();
        
        if(!$currentStatus || $currentStatus === false) {
            if(!$forwarder->activate()) {
                    return false;
            }
            
            $this->_getLogger()->setMessage(self::MESSAGE_FORWARDER_ACTIVATED);
        } else {
            // manual conflicts
            if($forwarder->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
                Core_Model_DiFactory::getMessageManager()
                            ->addError(self::CONFLICTED_FORWARDER, $forwarder->getEmail());
                return false;
            }
                
            if(!$forwarder->deactivate()) {
                return false;
            }
            
            $this->_getLogger()->setMessage(self::MESSAGE_FORWARDER_DEACTIVATED);
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setDomainRef($forwarder->getDomain()->getId())
                ->setMessageVars($forwarder->getEmail())
                ->setClientRef($forwarder->getOwner()->getId())->save();
        
        return true;
    }
    
    /**
     * creates forwarder in backend and nodes
     * 
     * @param array $data
     * @return boolean||string Id of new forwarder 
     */
    public function createForwarder($data)
    {
        if(!array_key_exists('user', $data)
                || !array_key_exists('domainId', $data)) {
            return false;
        }

        $specialManager = MazelabVpopqmail_Model_DiFactory::getSpecialsManager();
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($data['domainId']))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        $email = $data['user'] . '@' . $domain->getName();
        
        if(!($owner = $domain->getOwner())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_WITHOUT_OWNER, $domain->getName());
            return false;            
        }
        
        if ($this->getForwarderByEmail($email) || $specialManager->getSpecialByEmail($email)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ACCOUNT_EXISTS, $email);
            return false;
        }

        if(isset($data["status"])) {
            $data["status"] = (boolean) $data["status"];
        }else {
            $data["status"] = true;
        }
        $data['domainName'] = $domain->getName();
        $data['ownerId'] = $owner->getId();
        $data['ownerName'] = $owner->getLabel();
        $data['label'] = $email;
        
        $forwarder = MazelabVpopqmail_Model_DiFactory::newForwarder();
        if(!$forwarder->setLoaded(true)->setData($data)->save()) {
            return false;
        }
        
        $this->registerForwarder($forwarder->getId(), $forwarder);
        
        if (($domainNode = MazelabVpopqmail_Model_DiFactory::getNodeManager()
                ->getNodeOfDomain($domain->getId()))){
            $this->_getLogger()->setNodeRef($domainNode->getId());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_FORWARDER_CREATED)->setData($forwarder->getData())
                ->setMessageVars($forwarder->getEmail())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($forwarder->getOwner()->getId())
                ->setDomainRef($forwarder->getDomain()->getId())->save();
        
        $forwarder->apply();
        
        return $forwarder->getId();
    }
    
    /**
     * deletes a certain forwarder
     * 
     * @param string $forwarderId
     * @return boolean
     */
    public function deleteForwarder($forwarderId)
    {
        if(!($forwarder = $this->getForwarder($forwarderId)) || !$forwarder->removeCommands()) {
            return false;
        }
        
        foreach($forwarder->getForwarderTargets() as $targetId => $email) {
            if(($account = MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountByEmail($email))) {
                $account->deleteForwarderRef($forwarderId);
            }
        }
        
        if(!$this->getProvider()->deleteForwarder($forwarder)) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::getIndexManager()->unsetForwarder($forwarderId);
        $this->unregisterForwarder($forwarderId);
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_FORWARDER_DELETED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($forwarder->getOwner()->getId())
                ->setMessageVars($forwarder->getEmail())
                ->setDomainRef($forwarder->getDomain()->getId())->save();
        
        return true;
    }

    /**
     * deletes all forwarders of the given domain
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function deleteForwardersByDomain($domainId)
    {
        if (!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return false;
        }

        foreach ($this->getForwardersByDomain($domain->getId()) as $forwarder) {
            $this->deleteForwarder($forwarder->getId());
        }

        return sizeof($this->getForwardersByDomain($domain->getId())) == 0;
    }
    
    /**
     * sets delete flag on forwarder or deletes it directly
     * 
     * @param string $forwarderId
     * @return boolean
     */
    public function flagDelete($forwarderId)
    {
        if(!($forwarder = $this->getForwarder($forwarderId))) {
            return false;
        }
        
        // there are no deacrivated forwarder, so if deactivated then delete it
        if(!$forwarder->getRemoteData('status') || !$forwarder->getNode()) {
            return $this->deleteForwarder($forwarderId);
        }
        
        return $forwarder->flagDelete();
    }
    
    /**
     * returns all conflicted forwarders of the given client
     * 
     * @param string $clientId
     * @return array
     */
    public function getConflictedForwardersByOwner($clientId)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $entries = array();
        
        foreach ($logManager->getClientContextLogs($clientId
                , Core_Model_Logger::TYPE_CONFLICT,
                MazelabVpopqmail_Model_ValueObject_Forwarder::LOG_ACTION_FORWARDER_CONFLICT)
                as $entry) {
            if(!array_key_exists('contextId', $entry)) {
                continue;
            }
            
            $entries[$entry['contextId']] = $entry;
        }
        
        return $entries;
    }
    
    /**
     * get conflicted forwarders of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getConflictedForwardersOfDomain($domainId)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $entries = array();
        
        foreach ($logManager->getDomainContextLogs($domainId
                , Core_Model_Logger::TYPE_CONFLICT,
                MazelabVpopqmail_Model_ValueObject_Forwarder::LOG_ACTION_FORWARDER_CONFLICT)
                as $entry) {
            if(!array_key_exists('contextId', $entry)) {
                continue;
            }
            
            $entries[$entry['contextId']] = $entry;
        }
        
        return $entries;
    }
    
    /**
     * get all domains witch has conflicted forwarders
     * 
     * @param string $clientId
     * @return array
     */
    public function getConflictedForwardersPerDomainByOwner($clientId)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $entries = array();
        
        foreach ($logManager->getClientContextLogs($clientId
                , Core_Model_Logger::TYPE_CONFLICT,
                MazelabVpopqmail_Model_ValueObject_Forwarder::LOG_ACTION_FORWARDER_CONFLICT)
                as $entry) {
            if(!array_key_exists('contextId', $entry) || !isset($entry['domain']['label'])) {
                continue;
            }
            
            $entries[$entry['domain']['label']][$entry['contextId']] = $entry;
        }

        return $entries;
    }
    
    /**
     * returns a certain forwarder object 
     * 
     * @param string $forwarderId
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder|null
     */
    public function getForwarder($forwarderId)
    {
        if(!$this->isForwarderRegistered($forwarderId)) {
            $this->_loadForwarder($forwarderId);
        }
        
        return $this->_getRegisteredForwarder($forwarderId);
    }
    
    /**
     * returns the data set of a certain forwarder as array
     * 
     * @param string $forwarderId
     * @return array
     */
    public function getForwarderAsArray($forwarderId)
    {
        if(!$this->isForwarderRegistered($forwarderId)) {
            $this->_loadForwarder($forwarderId);
        }
        
        if(!($forwarder = $this->_getRegisteredForwarder($forwarderId))) {
            return array();
        }
        
        // sort forwarder targets
        $data = $forwarder->getData();
        $data["forwardTo"] = $forwarder->getForwarderTargets();
        
        return $data;
    }
    
    /**
     * gets a certain forwarder by email
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_Forwarder|null
     */
    public function getForwarderByEmail($email)
    {
        if(($forwarder = MazelabVpopqmail_Model_DiFactory::getForwarderByEmail($email))) {
            return $forwarder;
        }
        
        if(!($forwarderId = $this->_loadForwarderByEmail($email))) {
            return null;
        }
        
        return $this->_getRegisteredForwarder($forwarderId);
    }
    
    /**
     * gets a certain forwarder by email
     * 
     * @param string $email
     * @return array
     */
    public function getForwarderByEmailAsArray($email)
    {
        if(($forwarder = MazelabVpopqmail_Model_DiFactory::getForwarderByEmail($email))) {
            return $forwarder->getData();
        }
        
        if(!($forwarderId = $this->_loadForwarderByEmail($email))) {
            return array();
        }
        
        if(!($forwarder = $this->_getRegisteredForwarder($forwarderId))) {
            return array();
        }
                
        return $forwarder->getData();
    }
    
    /**
     * returns all forwarder which are connected/attached (email) to the given email account
     * 
     * @param string $email
     * @return array contains MazelabVpopqmail_Model_Forwarder
     */
    public function getForwardersOfTarget($email)
    {
        $return = array();
        foreach ($this->getProvider()->getForwarderByTarget($email) as $forwarderId => $forwarder) {
            $this->registerForwarder($forwarderId, $forwarder);
            $return[$forwarderId] = $this->_getRegisteredForwarder($forwarderId);
        }
        
        return $return;
    }
    
    /**
     * returns all forwarder which are connected/attached (email) to the given email account
     * 
     * @param string $email
     * @return array
     */
    public function getForwardersOfTargetAsArray($email)
    {
        $return = array();
        
        foreach ($this->getForwardersOfTarget($email) as $forwarderId => $forwarder) {
            $return[$forwarderId] = $forwarder->getData();
            $return[$forwarderId]['email'] = $forwarder->getEmail();
        }

        return $return;
    }

    /**
     * returns registered forwarder targets
     * 
     * @param  string $forwarderId
     * @param  boolean $sort sort forwarder target
     * @return array
     */
    public function getForwarderTargets($forwarderId, $sort = false)
    {
        if(!($forwarder = $this->getForwarder($forwarderId))) {
            return array();
        }
        
        return $forwarder->getForwarderTargets($sort);
    }
    
    /**
     * get all forwarders
     * 
     * @return array
     */
    public function getForwarders()
    {
        $forwarders = array();
        foreach($this->getProvider()->getForwarders() as $forwarderId => $forwarder) {
            $this->registerForwarder($forwarderId, $forwarder);
            $forwarders[$forwarderId] = $this->getForwarder($forwarderId);
        }
        
        return $forwarders;
    }
    
    /**
     * get all forwarders as array
     * 
     * @return array
     */
    public function getForwardersAsArray()
    {
        $forwarders = array();
        foreach($this->getProvider()->getForwarders() as $forwarderId => $forwarder) {
            $this->registerForwarder($forwarderId, $forwarder);
            $forwarders[$forwarderId] = $forwarder;
        }
        
        return $forwarders;
    }
    
    /**
     * returns all forwarder of a certain domain
     * 
     * @param string $domainId
     * @return array contains MazelabVpopqmail_Model_Forwarder
     */
    public function getForwardersByDomain($domainId)
    {
        $forwarders = array();
        
        foreach($this->getProvider()->getForwardersByDomain($domainId)
                as $forwarderId => $forwarder) {
            $this->registerForwarder($forwarderId, $forwarder);
            $forwarders[$forwarderId] = $this->getForwarder($forwarderId);
        }
        
        return $forwarders;
    }
    
    /**
     * returns a data set of all forwarder of a certain client
     * 
     * @param string $clientId
     * @return array
     */
    public function getForwardersByOwner($clientId)
    {
        $forwarders = array();

        foreach(MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByOwner($clientId) as $domainId => $domain) {
            foreach($this->getForwardersByDomain($domainId) as $forwarderId => $forwarder) {
                $forwarders[$forwarderId] = $forwarder;
            }
        }
        
        return $forwarders;
    }
    
    /**
     * returns data provider for forwarder context
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
     */
    public function getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getForwarder();
    }
    
    /**
     * import a new forwarder from report
     * 
     * @param string $domainId
     * @param array $data
     * @return boolean
     */
    public function importForwarderFromReport($domainId, array $data)
    {
        if(!array_key_exists('email', $data)) {
            return false;
        }
        
        $data['user'] = substr($data['email'], 0, strpos($data['email'], "@"));
        $data['domainId'] = $domainId;
        
        $form = new MazelabVpopqmail_Form_AddForwarder();
        if(!$form->setDomainSelectByDomain($domainId)->isValid($data) || 
                !($forwarderId = $this->createForwarder($data)))  {
            return false;
        }

        if(!($forwarder = $this->getForwarder($forwarderId))) {
            return false;
        }
        
        $forwarder->setRemoteData($data)->save();
        MazelabVpopqmail_Model_DiFactory::getApplyForwarder()->remove($forwarder);
        $forwarder->setAccountRefs();
        
        return true;
    }
    
    /**
     * checks if a certain forwarder instance is allready registered
     * 
     * @param string $forwarderId
     * @return boolean
     */
    public function isForwarderRegistered($forwarderId)
    {
        if(MazelabVpopqmail_Model_DiFactory::isForwarderRegistered($forwarderId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a forwarder instance
     * 
     * overwrites existing instances
     * 
     * @param string $forwarderId
     * @param mixed $context array or MazelabVpopqmail_Model_ValueObject_Forwarder
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerForwarder($forwarderId, $context, $setLoadedFlag = true)
    {
        $forwarder = null;
        
        if(is_array($context)) {
            $forwarder = MazelabVpopqmail_Model_DiFactory::newForwarder($forwarderId);
            
            if($setLoadedFlag) {
                $forwarder->setLoaded(true);
            }
            
            $forwarder->getBean()->setBean($context);
        } elseif($context instanceof MazelabVpopqmail_Model_ValueObject_Forwarder) {
            $forwarder = $context;
        }
        
        if(!$forwarder) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::registerForwarder($forwarderId, $forwarder);
        
        return true;
    }
    
    /**
     * resolves conflicts in an forwarder with the given data
     * 
     * @param string $forwarderId
     * @param array $data
     * @return boolean
     */
    public function resolveForwarderConflicts($forwarderId, array $data)
    {
        if(!($forwarder = $this->getForwarder($forwarderId))) {
            return false;
        }
        
        if(array_key_exists('forwardTo', $data) && is_array($data['forwardTo'])) {
            foreach($data['forwardTo'] as $id => $email) {
                if(!$email) {
                    $data['forwardTo'][$id] = null;
                }
            }
        }
        
        if(!$forwarder->setData($data)->save()) {
            return false;
        }
        
        $forwarder->apply();
        
        if(!$forwarder->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
            $forwarder->resolveConflictLog();
        }
        
        return true;
    }
    
    /**
     * unregisters a certain forwarder instance
     * 
     * @param string $forwarderId
     * @return boolean
     */
    public function unregisterForwarder($forwarderId)
    {
        if(!$this->_getRegisteredForwarder($forwarderId)) {
            return true;
        }
        
        MazelabVpopqmail_Model_DiFactory::unregisterForwarder($forwarderId);
    }
    
    /**
     * updates a certain forwarder with the given data
     * 
     * @param string $forwarderId
     * @param string $data
     * @return boolean
     */
    public function updateForwarder($forwarderId, $data)
    {
        $conflicted = false;
        
        if(!($forwarder = $this->getForwarder($forwarderId))) {
            return false;
        }
        
        if(!($domain = $forwarder->getDomain())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }

        // delete empty forwarder
        if (isset($data["forwardTo"])){
            foreach ($data["forwardTo"] as $targetId => $target){
                $accountEmail = $forwarder->getData("forwardTo/$targetId");
                $forwarder->deleteForwarderTarget($targetId);
                unset($data["forwardTo"][$targetId]);
                
                if(($account = MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountByEmail($accountEmail))) {
                    $account->deleteForwarderRef($forwarderId);
                }
                
                if (!empty($target)){
                    $data["forwardTo"][md5($target)] = $target;
                }
            }
        }

        // has manual conflicts?
        if($forwarder->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
            $conflicted = true;
        }
        
        $forwarder->setData($data);

        if(!$forwarder->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::UPDATE_FAILED);
            return false;
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_FORWARDER_UPDATED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($forwarder->getOwner()->getId())
                ->setMessageVars($forwarder->getEmail())
                ->setDomainRef($domain->getId())->setData($data)
                ->save();
        
        
        if(($domainNode = MazelabVpopqmail_Model_DiFactory::getNodeManager()->getNodeOfDomain($domain->getId())) && $conflicted) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::CONFLICTED_FORWARDER, $forwarder->getEmail());
            return true;
        }
        
        $forwarder->apply();
        
        return true;
    }
    
}

