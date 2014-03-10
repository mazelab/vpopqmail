<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_ValueObject_CatchAll 
    extends MazelabVpopqmail_Model_ValueObject
{
    
    /**
     * message when an error occured while applying
     */
    CONST APPLY_ERROR = 'Apply error in node %1$s';
    
    /**
     * message when domain was not found
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t load domain';
    
    /**
     * message when an error occured while saving
     */
    CONST ERROR_SAVING = 'Something went wrong while saving catch-all %1$s';

    /**
     * message when catchall was deleted
     */
    CONST MESSAGE_CATCHALL_DELETED = 'Catchall %1$s was deleted';
    
    /**
     * message when there is no node assigned
     */
    CONST MESSAGE_NODE_UNASSIGNED = 'There is no node assigned for domain %1$s. Therefore the changes can\'t be applied';
    
    /**
     * returns data backend provider
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll
     */
    public function _getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getCatchAll();
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
        return $this->_getProvider()->getCatchAll($this->getId());
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * @var array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedContext)
    {
        $id = $this->_getProvider()->saveCatchAll($unmappedContext, $this->getId());
        
        if(!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getId());
            return false;
        }
        
        return $id;
    }
    
    /**
     * apply current configuration
     * 
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply($save = true)
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyCatchAll()->apply($this, $save);
    }
    
    /**
     * deletes instance in the data backend
     * 
     * @return boolean
     */
    public function delete()
    {
        if(!$this->getId()) {
            return false;
        }
        
        if(!($domain = $this->getDomain())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        
        if(!$this->_getProvider()->deleteCatchAll($this)) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::getCatchAllManager()->unregisterCatchAll($this->getId());
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_CATCHALL_DELETED)
                ->setMessageVars($domain->getName())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($domain->getOwner()->getId())
                ->setDomainRef($domain->getId())->save();
        
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
        $this->setRemoteData($data);
        
        if ($this->getConflicts()) {
            $this->apply(false);
        }
        
        return $this->save();
    }
    
    /**
     * returns the bean with the loaded data from data backend
     * 
     * @param boolean $new force new bean struct
     * @return MazelabVpopqmail_Model_Bean_CatchAll
     */
    public function getBean($new = false)
    {
        if ($new || !$this->_valueBean || !$this->_valueBean instanceof MazelabVpopqmail_Model_Bean_CatchAll) {
            $this->_valueBean = new MazelabVpopqmail_Model_Bean_CatchAll();
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
     * removes commands from current node
     */
    public function removeCommands()
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyCatchAll()->remove($this);
    }
    
    /**
     * sets/adds new data set as local data
     * 
     * sets behavior field depending on selectedBehavior
     * 
     * @param array $data
     * @return Core_Model_ValueObject
     */
    public function setData(array $data)
    {
        if(!key_exists('behavior', $data) && key_exists('selectedBehavior', $data)) {
            $selectedBehavior = $data['selectedBehavior'];
            
            if($selectedBehavior == MazelabVpopqmail_Model_CatchAllManager::
                    CATCH_ALL_BEHAVIOR_DELETE) {
                $data['behavior'] = 'delete';
            } else if ($selectedBehavior == MazelabVpopqmail_Model_CatchAllManager::
                    CATCH_ALL_BEHAVIOR_SEND_TO_EMAIL &&
                    key_exists('sendToEmail', $data)) {
                $data['behavior'] = $data['sendToEmail'];
            } else if ($selectedBehavior == MazelabVpopqmail_Model_CatchAllManager::
                    CATCH_ALL_BEHAVIOR_MOVE_TO_ACCOUNT &&
                    key_exists('sendToAccount', $data)) {
                $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
                $account = $accountManager->getAccount($data['sendToAccount']);
                
                if($account->getEmail()) {
                    $data['behavior'] = $account->getEmail();
                }
            }
        }
        
        return parent::setData($data);
    }
    
}
