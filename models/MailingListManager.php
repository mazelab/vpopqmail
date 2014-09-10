<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_MailingListManager
{
    
    /**
     * message when domain was not available
     */
    CONST DOMAIN_NOT_FOUND = 'Couldn\'t find domain';
    
    /**
     * message when domain has no owner
     */
    CONST DOMAIN_WITHOUT_OWNER = 'Domain %1$s doesn\'t have a owner';
    
    /**
     * message when email allready exists
     */
    CONST EMAIL_EXISTS = 'email allready exists';
    
    /**
     * message when mailing list was created
     */
    CONST MESSAGE_LIST_CREATED = 'Mailing list %1$s was created';

    /**
     * message when deleting a mail robot
     */
    CONST MESSAGE_LIST_DELETED = 'Mailing list %1$s was deleted';
    
    /**
     * message when mailing list was updated
     */
    CONST MESSAGE_LIST_UPDATED = 'Mailing list %1$s was updated';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain mailing list instance if registered
     * 
     * @param string $mailingListId
     * @return MazelabVpopqmail_Model_ValueObject_MailingList|null null if not registered
     */
    protected function _getRegisteredMailingList($mailingListId)
    {
        if(!$this->isMailingListRegistered($mailingListId)) {
            return null;
        }
        
        return MazelabVpopqmail_Model_DiFactory::getMailingList($mailingListId);
    }
    
    /**
     * loads and registers a certain mailing list instance
     * 
     * @param string $mailingListId
     * @return boolean
     */
    protected function _loadMailingList($mailingListId)
    {
        $data = $this->getProvider()->getMailingList($mailingListId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerMailingList($mailingListId, $data);
    }
    
    /**
     * loads and registers a certain mailing list instance by email
     * 
     * @param string $mailingListId
     * @return boolean
     */
    protected function _loadMailingListByEmail($email)
    {
        $data = $this->getProvider()->getMailingListByEmail($email);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return false;
        }
        
        return $this->registerMailingList($data['_id'], $data);
    }
    
    /**
     * adds new mailing list for given domain
     * 
     * @param string $domainId
     * @param array $context
     * @return false|string id
     */
    public function addMailingList($domainId, array $context)
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return false;
        }
        
        if(!array_key_exists('user', $context)) {
            return false;
        }
        
        $email = $context['user'] . '@' . $domain->getName();
        if($accountManager->getAccountByEmail($email)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::EMAIL_EXISTS);
            return false;
        }
        
        if(!($owner = $domain->getOwner())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_WITHOUT_OWNER, $domain->getName());
            return false;            
        }
        
        $mailingList = MazelabVpopqmail_Model_DiFactory::newMailingList();
        $context['domainId'] = $domainId;
        if(isset($context["status"])) {
            $context["status"] = (boolean) $context["status"];
        }else {
            $context["status"] = true;
        }
        $context['domainName'] = $domain->getName();
        $context['ownerId'] = $owner->getId();

        if(array_key_exists('subscriber', $context)) {
            $subscriber = $context['subscriber'];
            unset($context['subscriber']);
            
            foreach($subscriber as $email) {
                if($email) {
                    $context['subscriber'][md5($email)] = $email;
                }
            }
        }
        
        if (!$mailingList->setData($context)->save()) {
            return false;
        }

        $this->registerMailingList($mailingList->getId(), $mailingList);
        
        if (($domainNode = MazelabVpopqmail_Model_DiFactory::getNodeManager()
                ->getNodeOfDomain($domain->getId()))){
            $this->_getLogger()->setNodeRef($domainNode->getId());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_LIST_CREATED)->setData($mailingList->getData())
                ->setMessageVars($mailingList->getEmail())->setClientRef($owner->getId())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setDomainRef($domain->getId())->save();
        
        $mailingList->apply();
        
        return $mailingList->getId();
    }
    
    /**
     * adds given subscriber to existing mailing list
     * 
     * @param string $mailingListId
     * @param string $subscriber
     * @return string id of new subscriber
     */
    public function addMailingListSubscriber($mailingListId, $subscriber)
    {
        if(!($mailingList = $this->getMailingList($mailingListId))) {
            return false;
        }
        
        if(!($newSubscriver = $mailingList->addSubscriber($subscriber))) {
            return false;
        }
        
        $mailingList->apply();
        
        return true;
    }
    
    /**
     * deletes a certain mailing list
     * 
     * @param string $listId
     * @return boolean
     */
    public function deleteMailingList($listId)
    {
        if(!($list = $this->getMailingList($listId)) || !$list->removeCommands()) {
            return false;
        }
        
        if(!$this->getProvider()->deleteMailingList($list)) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::getIndexManager()->unsetList($listId);
        
        if(($domain = $list->getDomain())) {
            $this->_getLogger()->setDomainRef($domain->getId());
        }

        if($domain && ($owner = $domain->getOwner())) {
            $this->_getLogger()->setClientRef($owner->getId());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_LIST_DELETED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setMessageVars($list->getEmail())->save();
        
        $this->unregisterMailingList($listId);
        
        return true;
    }
    
    /**
     * set delete flag to certain list
     * 
     * @param string $listId
     * @return boolean
     */
    public function flagDelete($listId)
    {
        if(!($list = $this->getMailingList($listId))) {
            return false;
        }
        
        if(!$list->getNode()) {
            return $this->deleteMailingList($listId);
        }
        
        return $list->flagDelete();
    }
    
    /**
     * returns a certain mailing list instance by id
     * 
     * @param string $mailingListId
     * @return MazelabVpopqmail_Model_ValueObject_MailingList|null
     */
    public function getMailingList($mailingListId)
    {
        if(!$this->isMailingListRegistered($mailingListId)) {
            $this->_loadMailingList($mailingListId);
        }
        
        return $this->_getRegisteredMailingList($mailingListId);
    }
    
    /**
     * returns a certain mailing list instance by email
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_ValueObject_MailingList
     */
    public function getMailingListByEmail($email)
    {
        if(($mailingList = MazelabVpopqmail_Model_DiFactory::getMailingListByEmail($email))) {
            return $mailingList;
        }
        
        $this->_loadMailingListByEmail($email);
        return MazelabVpopqmail_Model_DiFactory::getMailingListByEmail($email);
    }
    
    /**
     * get all mailing lists
     * 
     * @return array
     */
    public function getMailingLists()
    {
        $lists = array();
        foreach($this->getProvider()->getMailingLists() as $listId => $list) {
            $this->registerMailingList($listId, $list);
            $lists[$listId] = $this->getMailingList($listId);
        }
        
        return $lists;
    }
    
    /**
     * get all mailing lists as array
     * 
     * @return array
     */
    public function getMailingListsAsArray()
    {
        $lists = array();
        foreach($this->getProvider()->getMailingLists() as $listId => $list) {
            $this->registerMailingList($listId, $list);
            $lists[$listId] = $list;
        }
        
        return $lists;
    }
    
    /**
     * returns all mailing list instances of a certain domain
     * 
     * @param string $domainId
     * @return array contains MazelabVpopqmail_Model_ValueObject_MailingList|null
     */
    public function getMailingListsByDomain($domainId)
    {
        $lists = array();
        
        if(($context = $this->getProvider()->getMailingListsByDomain($domainId))) {
            foreach($context as $listId => $data) {
                $this->registerMailingList($listId, $data);
                
                if(($list = $this->_getRegisteredMailingList($listId))) {
                    $lists[$listId] = $list;
                }
            }
        }
        
        return $lists;
    }
    
    /**
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailingList
     */
    public function getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getMailingList();
    }
    
    /**
     * returns all registered subscriber in mailing list
     * 
     * @param  boolean $sort sort subscribers
     * @return array
     */
    public function getSubscribers($mailingListId, $sort = false)
    {
        if(!($mailingList = $this->getMailingList($mailingListId))) {
            return array();
        }

        if(!$mailingList->getData()) {
            return array();
        }
        
        return $mailingList->getSubscribers($sort);
    }
    
    /**
     * updates a certain mailing list with the given context
     * 
     * @param string $id
     * @param array $context
     * @return boolean
     */
    public function updateMailingList($id, array $context)
    {
        if(!($mailingList = $this->getMailingList($id))) {
            return false;
        }
        
        // delete empty subscriber
        if (array_key_exists('subscriber', $context)){
            foreach ($context["subscriber"] as $subscriberId => $subscriber){
                $mailingList->removeSubscriber($subscriberId);
                unset($context["subscriber"][$subscriberId]);
                
                if (!empty($subscriber)){
                    $context["subscriber"][md5($subscriber)] = $subscriber;
                }
            }
        }

        $mailingList->setData($context);

        if(!$mailingList->save()) {
            return false;
        }

        $domain = $mailingList->getDomain();
        $owner = $domain->getOwner();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_LIST_UPDATED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($owner->getId())->setMessageVars($mailingList->getEmail())
                ->setDomainRef($domain->getId())->setData($context)
                ->save();
        
        $mailingList->apply();

        return true;
    }
    
    /**
     * import a new mailing list from report
     * 
     * @param string $domainId
     * @param array $data
     * @return boolean
     */
    public function importListFromReport($domainId, array $data)
    {
        if(!array_key_exists('email', $data)) {
            return false;
        }
        
        $data['user'] = substr($data['email'], 0, strpos($data['email'], "@"));
        $data['domainId'] = $domainId;
        
        $form = new MazelabVpopqmail_Form_AddMailingList();
        if(!$form->setDomainSelectByDomain($domainId)->isValid($data) || 
                !($listId = $this->addMailingList($domainId, $data)))  {
            return false;
        }

        if(!($list = $this->getMailingList($listId))) {
            return false;
        }
        
        $list->setRemoteData($data)->save();
        MazelabVpopqmail_Model_DiFactory::getApplyList()->remove($list);
        
        return true;
    }
    
    /**
     * checks if a certain mailing list instance is allready registered
     * 
     * @param string $mailingListId
     * @return boolean
     */
    public function isMailingListRegistered($mailingListId)
    {
        if(MazelabVpopqmail_Model_DiFactory::isMailingListRegistered($mailingListId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a mailing list instance
     * 
     * overwrites existing instances
     * 
     * @param string $mailingListId
     * @param mixed $context array or MazelabVpopqmail_Model_ValueObject_MailingList
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerMailingList($mailingListId, $context, $setLoadedFlag = true)
    {
        $mailingList = null;
        
        if(is_array($context)) {
            $mailingList = MazelabVpopqmail_Model_DiFactory::newMailingList($mailingListId);
            
            if($setLoadedFlag) {
                $mailingList->setLoaded(true);
            }
            
            $mailingList->getBean()->setBean($context);
        } elseif($context instanceof MazelabVpopqmail_Model_ValueObject_MailingList) {
            $mailingList = $context;
        }
        
        if(!$mailingList) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::registerMailingList($mailingListId, $mailingList);
        
        return true;
    }
    
    /**
     * unregisters a certain mailing list instance
     * 
     * @param string $mailingListId
     * @return boolean
     */
    public function unregisterMailingList($mailingListId)
    {
        if(!$this->_getRegisteredMailingList($mailingListId)) {
            return true;
        }
        
        MazelabVpopqmail_Model_DiFactory::unregisterMailingList($mailingListId);
    }
    
}

