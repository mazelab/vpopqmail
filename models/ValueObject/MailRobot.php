<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_ValueObject_MailRobot 
    extends MazelabVpopqmail_Model_ValueObject
{
    
    /**
     * message when an error occured while applying
     */
    CONST APPLY_ERROR = 'Apply error in node %1$s';
    
    /**
     * message when a robot is deactivated
     */
    CONST DEACTIVATED = 'The mail robot is deactivated';
    
    /**
     * message when domain was not found
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t load domain';
    
    /**
     * message when an error occured while saving
     */
    CONST ERROR_SAVING = 'Something went wrong while saving mail robot %1$s';
    
    /**
     * message when there is no node assigned
     */
    CONST MESSAGE_NODE_UNASSIGNED = 'There is no node assigned for domain %1$s. Therefore the changes can\'t be applied';
    
    /**
     * message when robot was activated
     */
    CONST MESSAGE_ROBOT_ACTIVATED = 'Mail robot %1$s was activated';
    
    /**
     * message when robot was deactivated
     */
    CONST MESSAGE_ROBOT_DEACTIVATED = 'Mail robot %1$s was deactivated';
    
    /**
     * message when robot was deleted
     */
    CONST MESSAGE_ROBOT_DELETED = 'Mail robot %1$s was deleted';
    
    /**
     * @var boolean
     */
    protected $_rebuildSearchIndex;
    
    /**
     * returns data backend provider
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot
     */
    public function _getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getMailRobot();
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
        return $this->_getProvider()->getMailRobot($this->getId());
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * @var array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedContext)
    {
        $id = $this->_getProvider()->saveMailRobot($unmappedContext, $this->getId());
        
        if(!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getId());
            return false;
        }
        
        return $id;
    }
    
    /**
     * activates this instance
     * 
     * @return boolean
     */
    public function activate()
    {
        if(!$this->setData(array('status' => true))->save()) {
            return false;
        }
        
        $this->apply();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ROBOT_ACTIVATED)
                ->setMessageVars($this->getEmail())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($this->getData('ownerId'))
                ->setDomainRef($this->getData('domainId'))->save();
        
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
        return MazelabVpopqmail_Model_DiFactory::getApplyRobot()->apply($this, $save);
    }
    
    /**
     * deactivates this instance
     * 
     * @return boolean
     */
    public function deactivate()
    {
        if(!$this->setData(array('status' => false))->save()) {
            return false;
        }
        
        $this->apply();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ROBOT_DEACTIVATED)
                ->setMessageVars($this->getEmail())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($this->getData('ownerId'))
                ->setDomainRef($this->getData('domainId'))->save();
        
        return true;
    }
    
    /**
     * deletes instance on node and data backend
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
     * evaluates the given reported Data in reference to this object
     * 
     * @param array $data
     * @return boolean
     */
    public function evalReport($data)
    {
        if($this->getData('delete')) {
            if(array_key_exists('status', $data) && !$data['status']) {
                return $this->_getProvider()->deleteRobot($this);
            }
            
            return $this->apply(false);
        }
        
        $this->setRemoteData($data);
        
        if($this->getConflicts()) {
            $this->apply(false);
        }
        
        return $this->save();
    }
    
    /**
     * returns the bean with the loaded data from data backend
     * 
     * @param boolean $new force new bean struct
     * @return MazelabVpopqmail_Model_Bean_MailRobot
     */
    public function getBean($new = false)
    {
        if ($new || !$this->_valueBean || !$this->_valueBean instanceof MazelabVpopqmail_Model_Bean_MailRobot) {
            $this->_valueBean = new MazelabVpopqmail_Model_Bean_MailRobot();
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
     * removes commands from current node
     */
    public function removeCommands()
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyRobot()->remove($this);
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
            MazelabVpopqmail_Model_DiFactory::getIndexManager()->setRobot($this->getId());
        }
        
        return true;
    }
    
    /**
     * sets/adds new data set
     * 
     * @param array $data
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot
     */
    public function setData(array $data)
    {
        if(array_key_exists('label', $data)) {
            $this->_rebuildSearchIndex = true;
        }
        if(array_key_exists('content', $data)) {
            $data['content'] = str_replace("\r\n","\n", $data['content']);
            // normalize tabs
            $data['content'] = str_replace("\t","    ", $data['content']);
            $data['content'] = str_replace("    ","\t", $data['content']);
        }
        
        return parent::setData($data);
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
        if(array_key_exists('status', $data) && (empty($data['status']) || $data['status'] === 'false')) {
            $data['status'] = false;
        } elseif(array_key_exists('status', $data)) {
            $data['status'] = true;
        }
        
        $this->getBean()->setRemoteData($data);
        
        return $this;
    }
    
}

