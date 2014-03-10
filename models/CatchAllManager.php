<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_CatchAllManager
{
    
    /**
     * catch-all behavior delete
     */
    CONST CATCH_ALL_BEHAVIOR_DELETE = 'delete';
    
    /**
     * catch-all behavior bounce
     */
    CONST CATCH_ALL_BEHAVIOR_BOUNCE = 'bounce';
    
    /**
     * catch-all behavior send to email
     */
    CONST CATCH_ALL_BEHAVIOR_SEND_TO_EMAIL = 'sendToEmail';
    
    /**
     * catch-all behavior send to email (existing maze account)
     */
    CONST CATCH_ALL_BEHAVIOR_MOVE_TO_ACCOUNT = 'moveToAccount';
    
    /**
     * message when catchall exists
     */
    CONST CATCH_ALL_EXISTS = 'Catch-all allready exists for choosen domain!';
    
    /**
     * message when domain was not available
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t find domain';
    
    /**
     * message when domain has no owner
     */
    CONST DOMAIN_WITHOUT_OWNER = 'Domain %1$s doesn\'t have a owner';
    
    /**
     * message when catchall was created
     */
    CONST MESSAGE_CATCHALL_CREATED = 'Catchall for domain %1$s was created';
    
    /**
     * message when catchall was updated
     */
    CONST MESSAGE_CATCHALL_UPDATED = 'Catchall for domain %1$s was updated';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain catch all instance if registered
     * 
     * @param string $catchAllId
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll|null null if not registered
     */
    protected function _getRegisteredCatchAll($catchAllId)
    {
        if(!$this->isCatchAllRegistered($catchAllId)) {
            return null;
        }
        
        return MazelabVpopqmail_Model_DiFactory::getCatchAll($catchAllId);
    }
    
    /**
     * loads and registers a certain catch all instance
     * 
     * @param string $catchAllId
     * @return boolean
     */
    protected function _loadCatchAll($catchAllId)
    {
        $data = $this->getProvider()->getCatchAll($catchAllId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerCatchAll($catchAllId, $data);
    }
    
    /**
     * loads and registers a certain catchall instance by domain
     * 
     * @param string $domainId
     * @return string|null catch all id
     */
    protected function _loadCatchAllByDomain($domainId)
    {
        $data = $this->getProvider()->getCatchAllByDomain($domainId);
        if(empty($data) || !key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerCatchAll($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * loads and registers a certain catchall instance by domain name
     * 
     * @param string $domainId
     * @return string|null catch all id
     */
    protected function _loadCatchAllByDomainName($domainName)
    {
        $data = $this->getProvider()->getCatchAllByDomainName($domainName);
        if(empty($data) || !key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerCatchAll($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * creates new catch all of given domain and context
     * 
     * @param string $domainId
     * @param array $context
     * @return boolean|string on success it will return the id
     */
    public function createCatchAll($domainId, array $context)
    {
        if ($this->getCatchAllByDomain($domainId)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::CATCH_ALL_EXISTS);
            return false;
        }
        
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        
        if(!($owner = $domain->getOwner())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_WITHOUT_OWNER, $domain->getName());
            return false;            
        }

        $catchAll = MazelabVpopqmail_Model_DiFactory::newCatchAll();
        $context['domainId'] = $domainId;
        $context['domainName'] = $domain->getName();
        $context['ownerId'] = $owner->getId();
        $context['ownerName'] = $owner->getLabel();

        if (!$catchAll->setData($context)->save()) {
            return false;
        }

        $this->registerCatchAll($catchAll->getId(), $catchAll);
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_CATCHALL_CREATED)->setData($catchAll->getData())
                ->setMessageVars($domain->getName())->setClientRef($owner->getId())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setDomainRef($domain->getId())->save();
        
        $catchAll->apply();
        
        return $catchAll->getId();
    }
    
    /**
     * sets the default catchall for the given domain
     * 
     * @param string $domainId
     * @return boolean
     */
    public function createDefaultCatchAll($domainId)
    {
        if(!($config = $this->getDefaultConfig($domainId))) {
            return false;
        }
        
        if(!$this->createCatchAll($domainId, $config)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * returns a certain catch all instance by id
     * 
     * @param string $catchAllId
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll|null
     */
    public function getCatchAll($catchAllId)
    {
        if(!$this->isCatchAllRegistered($catchAllId)) {
            $this->_loadCatchAll($catchAllId);
        }
        
        return $this->_getRegisteredCatchAll($catchAllId);
    }
    
    /**
     * returns a certain catch-all instance by domainId
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll|null
     */
    public function getCatchAllByDomain($domainId)
    {
        if(($catchAll = MazelabVpopqmail_Model_DiFactory::getCatchAllByDomain($domainId))) {
            return $catchAll;
        }
        
        if(!($catchAllId = $this->_loadCatchAllByDomain($domainId))) {
            return null;
        }
        
        return $this->_getRegisteredCatchAll($catchAllId);
    }
    
    /**
     * returns a certain catch-all instance by domain name
     * 
     * @param string $domainName
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll|null
     */
    public function getCatchAllByDomainName($domainName)
    {
        if(($catchAll = MazelabVpopqmail_Model_DiFactory::getCatchAllByDomainName($domainName))) {
            return $catchAll;
        }
        
        if(!($catchAllId = $this->_loadCatchAllByDomainName($domainName))) {
            return null;
        }
        
        return $this->_getRegisteredCatchAll($catchAllId);
    }
    
    /**
     * get all catch alls
     */
    public function getCatchAlls()
    {
        $catchAlls = array();
        foreach($this->getProvider()->getCatchAlls() as $catchAllId => $catchAll) {
            $this->registerCatchAll($catchAllId, $catchAll);
            $catchAlls[$catchAllId] = $this->getCatchAll($catchAllId);
        }
        
        return $catchAlls;
    }
    
    /**
     * get all catch alls as array
     */
    public function getCatchAllsAsArray()
    {
        $catchAlls = array();
        foreach($this->getProvider()->getCatchAlls() as $catchAllId => $catchAll) {
            $this->registerCatchAll($catchAllId, $catchAll);
            $catchAlls[$catchAllId] = $catchAll;
        }
        
        return $catchAlls;
    }
    
    /**
     * gets default config for catchall configuration from node or plugin config
     * 
     * @param string $domainId
     * @return array
     */
    public function getDefaultConfig($domainId)
    {
        $behavior = self::CATCH_ALL_BEHAVIOR_DELETE;
        $sendToEmail = null;
        
        $moduleConfig = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getConfig();
        if(($node = MazelabVpopqmail_Model_DiFactory::getNodeManager()->getNodeOfDomain($domainId))) {
            $nodeConfig = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getNodeConfig($node->getId());
        }
        
        if (isset($nodeConfig) && key_exists('selectedBehavior', $nodeConfig) &&
                (!empty($nodeConfig['selectedBehavior']) || $nodeConfig['selectedBehavior'] !== "")) {
            $behavior = $nodeConfig['selectedBehavior'];
            if(key_exists('sendToEmail', $nodeConfig)) {
                $sendToEmail = $nodeConfig['sendToEmail'];
            }
        } elseif ($moduleConfig && key_exists('selectedBehavior', $moduleConfig) &&
                (!empty($moduleConfig['selectedBehavior']) || $moduleConfig['selectedBehavior'] !== "")) {
            $behavior = $moduleConfig['selectedBehavior'];
            if(key_exists('sendToEmail', $moduleConfig)) {
                $sendToEmail = $moduleConfig['sendToEmail'];
            }
        }

        if(!$sendToEmail && $behavior !== self::CATCH_ALL_BEHAVIOR_SEND_TO_EMAIL) {
            $config['selectedBehavior'] = $behavior;
            $config['behavior'] = $behavior;
        } else {
            $config['selectedBehavior'] = $behavior;
            $config['sendToEmail'] = $sendToEmail;
            $config['behavior'] = $sendToEmail;
        }
        
        return $config;
    }
 
    /**
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll
     */
    public function getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getCatchAll();
    }
    
    /**
     * checks if a certain catch all instance is allready registered
     * 
     * @param string $catchAllId
     * @return boolean
     */
    public function isCatchAllRegistered($catchAllId)
    {
        if(MazelabVpopqmail_Model_DiFactory::isCatchAllRegistered($catchAllId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a catchAll instance
     * 
     * overwrites existing instances
     * 
     * @param string $catchAllId
     * @param mixed $context array or MazelabVpopqmail_Model_ValueObject_CatchAll
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerCatchAll($catchAllId, $context, $setLoadedFlag = true)
    {
        $catchAll = null;
        
        if(is_array($context)) {
            $catchAll = MazelabVpopqmail_Model_DiFactory::newCatchAll($catchAllId);
            
            if($setLoadedFlag) {
                $catchAll->setLoaded(true);
            }
            
            $catchAll->getBean()->setBean($context);
        } elseif($context instanceof MazelabVpopqmail_Model_ValueObject_CatchAll) {
            $catchAll = $context;
        }
        
        if(!$catchAll) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::registerCatchAll($catchAllId, $catchAll);
        
        return true;
    }
    
    /**
     * unregisters a certain catch all instance
     * 
     * @param string $catchAllId
     * @return boolean
     */
    public function unregisterCatchAll($catchAllId)
    {
        if(!$this->_getRegisteredCatchAll($catchAllId)) {
            return true;
        }
        
        MazelabVpopqmail_Model_DiFactory::unregisterCatchAll($catchAllId);
    }
    
    /**
     * updates a certain catch-all with the given context
     * 
     * @param string $id
     * @param array $context
     * @return boolean
     */
    public function updateCatchAll($id, array $context)
    {
        if(!($catchAll = $this->getCatchAll($id))) {
            return false;
        }
        
        if(!$catchAll->setData($context)->save()) {
            return false;
        }

        $domain = $catchAll->getDomain();
        $owner = $domain->getOwner();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_CATCHALL_UPDATED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($owner->getId())->setMessageVars($domain->getName())
                ->setDomainRef($domain->getId())->setData($context)
                ->save();
        
        $catchAll->apply();
        
        return true;
    }
    
}

