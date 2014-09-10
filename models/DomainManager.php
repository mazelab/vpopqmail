<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_DomainManager
{

    /**
     * error message if the default catchall couldn't be set
     */
    CONST ERROR_COULNDT_SET_DEFAULT_CATCHALL = 'Couldn\'t set default Catchall for domain %1$s. Please Check the vpopqmail configuration';
    
    /**
     * error message if the default node is not available
     */
    CONST ERROR_NODE_NOT_AVAILABLE = 'Couldn\'t load vpopqmail node';
    
    /**
     * error message  if a domain is invalid for any action
     */
    CONST ERROR_INVALID_DOMAIN = 'Given domain is invalid';
    
    /**
     * error message if domain couldnt be deleted
     */
    CONST ERROR_DOMAIN_DELETE = 'Couldn\'t delete domain %1$s on current node';
    
    /**
     * error message when saving vpopqmail configuration didn't worked
     */
    CONST ERROR_WHILE_SAVING = 'Something went wrong while saving vpopqmail configuration';
    
    /**
     * message when domain was added to the vpopqmail module
     */
    CONST MESSAGE_DOMAIN_ADD = 'Domain %1$s was added to service vpopqmail on node %2$s';
    
    /**
     * message when domain was deleted from a vpopqmail module node
     */
    CONST MESSAGE_DOMAIN_DELTED = 'Domain %1$s was deleted in module vpopqmail on node %2$s';
    
    /**
     * success message when adding a domain to the vpopqmail module
     */
    CONST SUCCESS_ADDED_DOMAIN = 'The domain %1$s was successfully added';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * register a domain in the vpopqmail module
     * 
     * if node is not given it tries to use the default node
     * 
     * @param string $domainId
     * @param string $nodeId
     * @return boolean
     */
    public function addDomain($domainId, $nodeId)
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $node = $nodeManager->getNode($nodeId);
        
        if(!$node->getData()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_NODE_NOT_AVAILABLE);
            return false;
        }
        
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_INVALID_DOMAIN);
            return false;
        }
        
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        
        $data = array('domains' => array($domainId  => $domain->getName()));
        if(!$configManager->addNodeConfig($node->getId(), $data) ||
                !$configManager->addClientConfig($domain->getOwner()->getId(), $data) ||
                !$configManager->addDomainConfig($domainId, array('nodes' => $nodeId))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_WHILE_SAVING);
            return false;
        }
        
        $catchAllManager = MazelabVpopqmail_Model_DiFactory::getCatchAllManager();
        if(!$catchAllManager->getCatchAllByDomain($domainId) && !$catchAllManager->createDefaultCatchAll($domainId)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_COULNDT_SET_DEFAULT_CATCHALL, $domain->getName());
            return false;
        }

        $module = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getModule();
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_DOMAIN_ADD)
                ->setMessageVars($domain->getName(), $node->getName())
                ->setModuleRef($module->getName())->setClientRef($domain->getOwner()->getId())
                ->setDomainRef($domain->getId())->setData($data)
                ->setNodeRef($node->getId())->save();
        
        Core_Model_DiFactory::getMessageManager()
                ->addSuccess(self::SUCCESS_ADDED_DOMAIN, $domain->getName());
        
        return true;
    }
    
    /**
     * assigns a certain domain to a certain node
     * 
     * @param string $domainId
     * @param string $nodeId if empty domain will be unassigned
     * @return boolean
     */
    public function assignDomain($domainId, $nodeId = null)
    {
        if(!$nodeId) {
            return $this->unassignDomain($domainId);
        }
        
        if(($node = MazelabVpopqmail_Model_DiFactory::getNodeManager()
                ->getNodeOfDomain($domainId)) && $node->getId() == $nodeId) {
            return true;
        }

        if($node && !$this->unassignDomain($domainId)) {
            return false;
        }
        
        if(!$this->addDomain($domainId, $nodeId)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * returns all domains and catch all of a certain client
     * 
     * @param string $clientId
     * @return array
     */
    public function getDomainsAndCatchAllAndConfigByOwnerAsArray($clientId)
    {
        $catchAllManager = MazelabVpopqmail_Model_DiFactory::getCatchAllManager();
        $domains = array();

        foreach($this->getEmailDomainsByOwner($clientId) as $domainId => $domain) {
            $domains[$domainId] = $domain->getData();
            
            if(($catchall = $catchAllManager->getCatchAllByDomain($domainId))) {
                $domains[$domainId]["catchAll"] = $catchall->getData();
            }
            
            $config = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getDomainConfig($domainId);
            $domains[$domainId]['emailConfig'] = $config;
        }

        return $domains;
    }
    
    /**
     * returns all domains in q-vpop module
     * 
     * @return array contains MazelabVpopqmail_Model_ValueObject_Domain
     */
    public function getEmailDomains()
    {
        $domains = array();
        
        foreach(Core_Model_DiFactory::getClientManager()->getClients() as $clientId => $client) {
            if($client->hasService(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)) {
                $domains = array_merge($domains, $this->getEmailDomainsByOwner($clientId));
            }
        }
        
        return $domains;
    }
    
    /**
     * returns all q-vpop domains which are assigned to a certain node
     * 
     * @param string $nodeId
     * @return array contains MazelabVpopqmail_Model_ValueObject_Domain
     */
    public function getEmailDomainsByNode($nodeId)
    {
        $config = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getNodeConfig($nodeId);
        
        if(!array_key_exists('domains', $config) || !is_array($config['domains'])) {
            return array();
        }
        
        $domains = array();
        foreach(array_keys($config['domains']) as $domainId) {
            if(($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
                $domains[$domainId] = $domain;
            }
        }
        
        return $domains;
    }
    
    /**
     * returns all q-vpop domains of a certain client
     * 
     * @param string $clientId
     * @return array contains MazelabVpopqmail_Model_ValueObject_Domain
     */
    public function getEmailDomainsByOwner($clientId)
    {
        $emailDomains = array();
        
        foreach(Core_Model_DiFactory::getDomainManager()->getDomainsByOwner($clientId) as $domainId => $domain) {
            if(!$domain->hasService(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)) {
                continue;
            }
            
            $emailDomains[$domainId] = $domain;
        }
        
        return $emailDomains;
    }
    
    /**
     * returns not vpopqmail initialized domains of a client
     * 
     * @param string $clientId
     * @return array contains MazelabVpopqmail_Model_ValueObject_Domain
     */
    public function getNonEmailDomainsByOwner($clientId)
    {
        $domains = array();
        
        foreach(Core_Model_DiFactory::getDomainManager()->getDomainsByOwner($clientId) as $domainId => $domain) {
            if($domain->hasService(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)) {
                continue;
            }
            
            $domains[$domainId] = $domain;
        }
        
        return $domains;
    }
        
    /**
     * removes the domain with all dependencies
     * 
     * * all vpopqmail elements + module configuration
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function removeDomain($domainId)
    {
        if (!$this->unassignDomain($domainId)) {
            return false;
        }

        return $this->removeEmailElementsByDomain($domainId);
    }
    
    /**
     * removes commands from node of every domain relevant object
     * 
     * @param string $domainId
     * @return boolean
     */
    public function removeDomainCommands($domainId)
    {
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return false;
        }
        
        foreach(MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountsByDomain($domainId) as $account) {
            $account->removeCommands();
        }
        foreach(MazelabVpopqmail_Model_DiFactory::getForwarderManager()->getForwardersByDomain($domainId) as $forwarder) {
            $forwarder->removeCommands();
        }
        foreach(MazelabVpopqmail_Model_DiFactory::getMailRobotManager()->getMailRobotsByDomain($domainId) as $robot) {
            $robot->removeCommands();
        }
        foreach(MazelabVpopqmail_Model_DiFactory::getMailingListManager()->getMailingListsByDomain($domainId) as $list) {
            $list->removeCommands();
        }
        
        if(($catchall = MazelabVpopqmail_Model_DiFactory::getCatchAllManager()->getCatchAllByDomain($domainId))) {
            $catchall->removeCommands();
        }
    }
    
    /**
     * removes all elements from vpopqmail i.e. accounts, forwarders and specials
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function removeEmailElementsByDomain($domainId)
    {
        if (!MazelabVpopqmail_Model_DiFactory::getAccountManager()->deleteAccountsByDomain($domainId) ||
                !MazelabVpopqmail_Model_DiFactory::getForwarderManager()->deleteForwardersByDomain($domainId) ||
                !MazelabVpopqmail_Model_DiFactory::getSpecialsManager()->deleteSpecialsByDomain($domainId)) {
            return false;
        }
        
        $catchAllManager = MazelabVpopqmail_Model_DiFactory::getCatchAllManager();
        $catchAll = $catchAllManager->getCatchAllByDomain($domainId);

        if ($catchAll && !$catchAllManager->getProvider()->deleteCatchAll($catchAll)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * removes domain assignments to nodes
     * 
     * this will delete vpopqmail configuration and current data on nodes.
     * Use with caution
     * 
     * @param string $domainId
     * @return boolean
     */
    public function unassignDomain($domainId)
    {
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_NODE_NOT_AVAILABLE);
            return false;
        }
        
        if(!($node = MazelabVpopqmail_Model_DiFactory::getNodeManager()->getNodeOfDomain($domainId))) {
            return true;
        }
        
        $module = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getModule();
        
        $this->removeDomainCommands($domainId);
        
        // remove assignment entry in module config
        foreach($module->getClientConfig() as $clientId => $client) {
            if ($module->getData("config/clients/$clientId/domains/$domainId")) {
                $module->unsetProperty("config/clients/$clientId/domains/$domainId")->save();
            }
        }

        if ($module->getData("config/nodes/{$node->getId()}/domains/$domainId")) {
            $module->unsetProperty("config/nodes/{$node->getId()}/domains/$domainId")->save();
        }

        if ($module->getData("config/domains/$domainId")) {
            $module->unsetProperty("config/domains/$domainId")->save();
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_DOMAIN_DELTED)
                ->setMessageVars($domain->getName(), $node->getName())
                ->setModuleRef($module->getName())->setClientRef($domain->getOwner()->getId())
                ->setDomainRef($domain->getId())->setNodeRef($node->getId())->save();
        
        return true;
    }
    
}
