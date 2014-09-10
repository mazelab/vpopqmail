<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_MailRobotManager
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
     * message when mail robot was created
     */
    CONST MESSAGE_ROBOT_CREATED = 'Mail robot %1$s was created';

    /**
     * message when deleting a mail robot
     */
    CONST MESSAGE_ROBOT_DELETED = 'Mail robot %1$s was deleted';
    
    /**
     * message when robot was updated
     */
    CONST MESSAGE_ROBOT_UPDATED = 'Mail robot %1$s was updated';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain mail robot instance if registered
     * 
     * @param string $mailRobotId
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot|null null if not registered
     */
    protected function _getRegisteredMailRobot($mailRobotId)
    {
        if(!$this->isMailRobotRegistered($mailRobotId)) {
            return null;
        }
        
        return MazelabVpopqmail_Model_DiFactory::getMailRobot($mailRobotId);
    }
    
    /**
     * loads and registers a certain mail robot instance
     * 
     * @param string $mailRobotId
     * @return boolean
     */
    protected function _loadMailRobot($mailRobotId)
    {
        $data = $this->getProvider()->getMailRobot($mailRobotId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerMailRobot($mailRobotId, $data);
    }
    
    /**
     * loads and registers a certain mail robot instance by email
     * 
     * @param string $mailRobotId
     * @return boolean
     */
    protected function _loadMailRobotByEmail($mailRobotId)
    {
        $data = $this->getProvider()->getMailRobotByEmail($mailRobotId);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return false;
        }
        
        return $this->registerMailRobot($data['_id'], $data);
    }
    
    /**
     * adds new mail robot for given domain
     * 
     * @param string $domainId
     * @param array $context
     * @return string|null id
     */
    public function addMailRobot($domainId, array $context)
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        $specialsManager = MazelabVpopqmail_Model_DiFactory::getSpecialsManager();
        
        if(!array_key_exists('user', $context)) {
            return null;
        }
        
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_NOT_FOUND);
            return null;
        }
        
        $email = $context['user'] . '@' . $domain->getName();
        if ($forwarderManager->getForwarderByEmail($email) || $specialsManager->getSpecialByEmail($email)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::EMAIL_EXISTS);
            return null;
        }

        if(!($owner = $domain->getOwner())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_WITHOUT_OWNER, $domain->getName());
            return null;            
        }
        
        $context['domainId'] = $domainId;
        $context['domainName'] = $domain->getName();
        $context['ownerId'] = $owner->getId();
        $context['ownerName'] = $owner->getLabel();
        $context['label'] = $email;
        $context['content'] = str_replace("\r\n","\n", $context['content']);
        if(isset($context["status"])) {
            $context["status"] = (boolean) $context["status"];
        }else {
            $context["status"] = true;
        }
        if(!array_key_exists('copyTo', $context) || !$context['copyTo']) {
            $context['copyTo'] = null;
        }
        
        $mailRobot = MazelabVpopqmail_Model_DiFactory::newMailRobot();
        if (!$mailRobot->setData($context)->save()) {
            return null;
        }

        $this->registerMailRobot($mailRobot->getId(), $mailRobot);
        
        if (($domainNode = MazelabVpopqmail_Model_DiFactory::getNodeManager()
                ->getNodeOfDomain($domain->getId()))){
            $this->_getLogger()->setNodeRef($domainNode->getId());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ROBOT_CREATED)->setData($mailRobot->getData())
                ->setMessageVars($mailRobot->getEmail())->setClientRef($owner->getId())
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setDomainRef($domain->getId())->save();
        
        $mailRobot->apply();
        
        return $mailRobot->getId();
    }

    /**
     * deletes a certain mail robot
     * 
     * @param string $robotId
     * @return boolean
     */
    public function deleteMailRobot($robotId)
    {
        if(!($robot = $this->getMailRobot($robotId)) || !$robot->removeCommands()) {
            return false;
        }
        
        if(!$this->getProvider()->deleteRobot($robot)) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::getIndexManager()->unsetRobot($robotId);
        
        if(($domain = $robot->getDomain())) {
            $this->_getLogger()->setDomainRef($domain->getId());
        }
        if($domain && ($owner = $domain->getOwner())) {
            $this->_getLogger()->setClientRef($owner->getId());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ROBOT_DELETED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setMessageVars($robot->getEmail())->save();
        
        $this->unregisterMailRobot($robotId);
        
        return true;
    }
    
    /**
     * set delete flag to certain robot
     * 
     * @param string $robotId
     * @return boolean
     */
    public function flagDelete($robotId)
    {
        if(!($robot = $this->getMailRobot($robotId))) {
            return false;
        }
        
        // there are no deacrivated robots, so if deactivated then delete it
        if(!$robot->getRemoteData('status') || !$robot->getNode()) {
            return $this->deleteMailRobot($robotId);
        }
        
        return $robot->flagDelete();
    }
    
    /**
     * returns a certain mail robot instance by id
     * 
     * @param string $id
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot|null
     */
    public function getMailRobot($mailRobotId)
    {
        if(!$this->isMailRobotRegistered($mailRobotId)) {
            $this->_loadMailRobot($mailRobotId);
        }
        
        return $this->_getRegisteredMailRobot($mailRobotId);
    }
    
    /**
     * returns a certain mail robot instance by email
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot
     */
    public function getMailRobotByEmail($email)
    {
        if(($mailRobot = MazelabVpopqmail_Model_DiFactory::getMailRobotByEmail($email))) {
            return $mailRobot;
        }
        
        $this->_loadMailRobotByEmail($email);
        return MazelabVpopqmail_Model_DiFactory::getMailRobotByEmail($email);
    }
    
    /**
     * get all mail robots
     * 
     * @return array
     */
    public function getMailRobots()
    {
        $robots = array();
        foreach($this->getProvider()->getMailRobots() as $robotId => $robot) {
            $this->registerMailRobot($robotId, $robot);
            $robots[$robotId] = $this->getMailRobot($robotId);
        }
        
        return $robots;
    }
    
    /**
     * get all mail robots as array
     * 
     * @return array
     */
    public function getMailRobotsAsArray()
    {
        $robots = array();
        foreach($this->getProvider()->getMailRobots() as $robotId => $robot) {
            $this->registerMailRobot($robotId, $robot);
            $robots[$robotId] = $robot;
        }
        
        return $robots;
    }
    
    /**
     * returns all mail robot instances of a certain domain
     * 
     * @param string $domainId
     * @return array contains MazelabVpopqmail_Model_ValueObject_MailRobot|null
     */
    public function getMailRobotsByDomain($domainId)
    {
        $robots = array();
        
        if(($context = $this->getProvider()->getMailRobotsByDomain($domainId))) {
            foreach($context as $robotId => $data) {
                $this->registerMailRobot($robotId, $data);
                
                if(($robot = $this->_getRegisteredMailRobot($robotId))) {
                    $robots[$robotId] = $robot;
                }
            }
        }
        
        return $robots;
    }
    
    /**
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot
     */
    public function getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getMailRobot();
    }
    
    /**
     * updates a certain mail robot with the given context
     * 
     * @param string $id
     * @param array $context
     * @return boolean
     */
    public function updateMailRobot($id, array $context)
    {
        if(!($mailRobot = $this->getMailRobot($id))) {
            return false;
        }
        
        if(!$mailRobot->setData($context)->save()) {
            return false;
        }
        
        $domain = $mailRobot->getDomain();
        $owner = $domain->getOwner();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_ROBOT_UPDATED)
                ->setModuleRef(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)
                ->setClientRef($owner->getId())->setMessageVars($mailRobot->getEmail())
                ->setDomainRef($domain->getId())->setData($context)
                ->save();
        
        $mailRobot->apply();
        
        return true;
    }
    
    /**
     * import a new mail robot from report
     * 
     * @param string $domainId
     * @param array $data
     * @return boolean
     */
    public function importRobotFromReport($domainId, array $data)
    {
        if(!array_key_exists('email', $data)) {
            return false;
        }
        
        $data['user'] = substr($data['email'], 0, strpos($data['email'], "@"));
        $data['domainId'] = $domainId;
        
        $form = new MazelabVpopqmail_Form_AddMailRobot();
        if(!$form->setDomainSelectByDomain($domainId)->isValid($data) || 
                !($robotId = $this->addMailRobot($domainId, $data)))  {
            return false;
        }

        if(!($robot = $this->getMailRobot($robotId))) {
            return false;
        }
        
        $robot->setRemoteData($data)->save();
        MazelabVpopqmail_Model_DiFactory::getApplyRobot()->remove($robot);
        
        return true;
    }
    
    /**
     * checks if a certain mail robot instance is allready registered
     * 
     * @param string $mailRobotId
     * @return boolean
     */
    public function isMailRobotRegistered($mailRobotId)
    {
        if(MazelabVpopqmail_Model_DiFactory::isMailRobotRegistered($mailRobotId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a mail robot instance
     * 
     * overwrites existing instances
     * 
     * @param string $mailRobotId
     * @param mixed $context array or MazelabVpopqmail_Model_ValueObject_MailRobot
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerMailRobot($mailRobotId, $context, $setLoadedFlag = true)
    {
        $mailRobot = null;
        
        if(is_array($context)) {
            $mailRobot = MazelabVpopqmail_Model_DiFactory::newMailRobot($mailRobotId);
            
            if($setLoadedFlag) {
                $mailRobot->setLoaded(true);
            }
            
            $mailRobot->getBean()->setBean($context);
        } elseif($context instanceof MazelabVpopqmail_Model_ValueObject_MailRobot) {
            $mailRobot = $context;
        }
        
        if(!$mailRobot) {
            return false;
        }
        
        MazelabVpopqmail_Model_DiFactory::registerMailRobot($mailRobotId, $mailRobot);
        
        return true;
    }
    
    /**
     * unregisters a certain mail robot instance
     * 
     * @param string $mailRobotId
     * @return boolean
     */
    public function unregisterMailRobot($mailRobotId)
    {
        if(!$this->_getRegisteredMailRobot($mailRobotId)) {
            return true;
        }
        
        MazelabVpopqmail_Model_DiFactory::unregisterMailRobot($mailRobotId);
    }
    
}

