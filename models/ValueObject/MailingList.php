<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazelabVpopqmail_Model_ValueObject_MailingList 
    extends MazelabVpopqmail_Model_ValueObject
{
    
    /**
     * message when error occured while applying
     */
    CONST APPLY_ERROR = 'Apply error in node %1$s';
    
    /**
     * message when mailing list is deactivated
     */
    CONST DEACTIVATED = 'The mailing list is deactivated';
    
    /**
     * message when domain was not found
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t load domain';
    
    /**
     * message when error occured while saving
     */
    CONST ERROR_SAVING = 'Something went wrong while saving mailing list %1$s';
    
    /**
     * key for subscriber property
     */
    CONST INDEX_SUBSCRIBER = 'subscriber';
    
    /**
     * message when mailing list was activated
     */
    CONST MESSAGE_LIST_ACTIVATED = 'Mailing list %1$s was activated';
    
    /**
     * message when robot was deactivated
     */
    CONST MESSAGE_LIST_DEACTIVATED = 'Mailing list %1$s was deactivated';
    
    /**
     * message when robot was deleted
     */
    CONST MESSAGE_LIST_DELETED = 'Mailing list %1$s was deleted';
    
    /**
     * message when there is no node assigned
     */
    CONST MESSAGE_NODE_UNASSIGNED = 'There is no node assigned for domain %1$s. Therefore the changes can\'t be applied';
    
    /**
     * message when subscriber is allready listed
     */
    CONST SUBSCRIBER_ALREADY_LISTED = 'Subscriber allready enlisted';
    
    /**
     * @var boolean
     */
    protected $_rebuildSearchIndex;
    
    /**
     * returns data backend provider
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailingList
     */
    public function _getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getMailingList();
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
        return $this->_getProvider()->getMailingList($this->getId());
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * @var array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedContext)
    {
        $id = $this->_getProvider()->saveMailingList($unmappedContext, $this->getId());
        
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
        if (!$this->setData(array('status' => true))->save()) {
            return false;
        }

        if(!($domain = $this->getDomain())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        
        $this->apply();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_LIST_ACTIVATED)
                ->setMessageVars($this->getEmail())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($domain->getOwner()->getId())
                ->setDomainRef($domain->getId())->save();
        
        return true;
    }
    
    /**
     * adds given subscriber to mailing list
     * 
     * @param string $email
     * @return string|false id of new subscriber 
     */
    public function addSubscriber($email)
    {
        if($this->hasSubscriber($email)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::SUBSCRIBER_ALREADY_LISTED);
            return false;
        }
        
        $subscriberId = md5($email);
        $data = array(
            self::INDEX_SUBSCRIBER => array(
                $subscriberId => $email
            )
        );

        $this->setData($data)->setData(array("subscriber/$subscriberId/created" => time()));
        if(!$this->save()) {
            return false;
        }
        
        return $subscriberId;
    }
    
    /**
     * apply current configuration
     * 
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply($save = true)
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyList()->apply($this, $save);
    }
    
    /**
     * deactivates this instance
     * 
     * @return boolean
     */
    public function deactivate()
    {
        if (!$this->setData(array('status' => false))->save()) {
            return false;
        }
        
        if(!($domain = $this->getDomain())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        
        $this->apply();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_LIST_DEACTIVATED)
                ->setMessageVars($this->getEmail())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($domain->getOwner()->getId())
                ->setDomainRef($domain->getId())->save();;
        
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
            if(key_exists('status', $data) && !$data['status']) {
                return MazelabVpopqmail_Model_DiFactory::getMailingListManager()->deleteMailingList($this->getId());
            }
            
            return $this->apply(false);
        }
        
        if(!$this->getStatus() && (!key_exists('subscriber', $data) || !$data['subscriber'])) {
            $data['status'] = false;
        }
        
        if($this->getStatus()) {
            // set not reported forwarder targets as conflict
            foreach ($this->getSubscribers(false, false) as $subscriberId => $email) {
                if(!$email && !isset($data['subscriber'][$subscriberId])) {
                    $this->unsetProperty("subscriber/$subscriberId");
                } elseif($email && isset($data['subscriber'][$subscriberId]) ){
                    // fix case sensitive email
                    $data['subscriber'][$subscriberId] = $email;
                } elseif(!isset($data['subscriber'][$subscriberId])) {
                    $data['subscriber'][$subscriberId] = null;
                }
            }
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
     * @return MazelabVpopqmail_Model_Bean_MailingList
     */
    public function getBean($new = false)
    {
        if ($new || !$this->_valueBean || !$this->_valueBean instanceof MazelabVpopqmail_Model_Bean_MailingList) {
            $this->_valueBean = new MazelabVpopqmail_Model_Bean_MailingList();
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
     * returns all registered subscriber in mailing list
     * 
     * @param  boolean $sort sort subscribers
     * @return array
     */
    public function getSubscribers($sort = false, $keepEmpty = true)
    {
        $subscribers = $this->getData(self::INDEX_SUBSCRIBER);
        $returns = array();
        $created = array();
        
        if(!is_array($subscribers)) {
            return array();
        }
        
        foreach($subscribers as $subscriberId => $subscriber) {
           if($keepEmpty && !$subscriber){
                unset($subscribers[$subscriberId]);
                continue;
            }
            $created[$subscriberId] = $this->getData("subscriber/$subscriberId/created");
        }
        
        if ($sort == false) {
            return $subscribers;
        }

        asort($created, SORT_ASC);
        foreach(array_keys($created) as $subscriberId) {
           $returns[$subscriberId] = $subscribers[$subscriberId];
        }

        return $returns;
    }
    
    /**
     * checks that given email is allready registered in mailing list
     * 
     * @param string $email
     * @return boolean
     */
    public function hasSubscriber($email)
    {
        $registeredSubscriber = $this->getSubscribers();
        $subscriberId = md5($email);
        
        if(key_exists($subscriberId, $registeredSubscriber)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * removes commands from current node
     */
    public function removeCommands()
    {
        return MazelabVpopqmail_Model_DiFactory::getApplyList()->remove($this);
    }
    
    /**
     * removes given subscriber from mailing list
     * 
     * @param string $subscriberId identification of email -> md5 key of email
     * @return boolean
     */
    public function removeSubscriber($subscriberId)
    {
        if (!$this->getId()) {
            return false;
        }
        
        if(!$this->setData(array("subscriber/$subscriberId" => null))->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getEmail());
            return false;
        }
        
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
            MazelabVpopqmail_Model_DiFactory::getIndexManager()->setList($this->getId());
        }
        
        return true;
    }
    
    /**
     * sets/adds new data set as local data
     * 
     * @param array $data
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot
     */
    public function setData(array $data)
    {
        if(key_exists('user', $data)) {
            if(!key_exists('domainId', $data)) {
                $domain = $this->getDomain();    
            } else {
                $domain = Core_Model_DiFactory::getDomainManager()->getDomain($data['domainId']);
            }
            
            if($domain) {
                $data['label'] = $data['user'] . '@' . $domain->getName();
            }
        }
        if(key_exists('label', $data)) {
            $this->_rebuildSearchIndex = true;
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
    public function setRemoteData(array $data)
    {
        // cast boolean values
        if(key_exists('status', $data) && (empty($data['status']) || $data['status'] === 'false')) {
            $data['status'] = false;
        } elseif(key_exists('status', $data)) {
            $data['status'] = true;
        }
        
        $this->getBean()->setRemoteData($data);
        
        return $this;
    }
    
}

