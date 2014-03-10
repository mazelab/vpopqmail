<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_ReportManager
{

    /**
     * message when catchall couldnt be created 
     */
    CONST ERROR_COULDNT_CREATE_CATCHALL = 'Could not create catch-all';
    
    /**
     * key name for email accounts
     */
    CONST KEY_ACCOUNTS = 'accounts';
    
    /**
     * key name for forwarders 
     */
    CONST KEY_FORWARDERS = 'forwarders';
    
    /**
     * key name for catchall
     */
    CONST KEY_CATCHALL = 'catchall';
    
    /**
     * key name for robots
     */
    CONST KEY_ROBOTS = 'robots';
    
    /**
     * key name for mailing lists
     */
    CONST KEY_LISTS = 'lists';

    /**
     * evals domain report for accounts
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @param string $nodeId
     * @param array $data
     */
    protected function _evalAccounts(Core_Model_ValueObject_Domain $domain, $nodeId, array $data)
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        
        $unknownAccounts = $data;
        foreach($accountManager->getAccountsByDomain($domain->getId()) as $account) {
            $email_md5 = md5(strtolower($account->getEmail()));

            if(key_exists($email_md5, $data)) {
                $account->evalReport($data[$email_md5]);
            } else {
                $account->evalReport(array('status' => false));
            }
            
            unset($unknownAccounts[$email_md5]);
        }
        
        if(!empty($unknownAccounts)) {
            foreach ($unknownAccounts as $email_md5 => $context) {
                $accountManager->importAccountFromReport($domain->getId(), $context);
            }
        }
    }
    
    /**
     * evals domain report for the catch-all
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @param string $nodeId
     * @param array $data
     */
    protected function _evalCatchAll(Core_Model_ValueObject_Domain $domain, array $data)
    {
        $catchAllManager = MazelabVpopqmail_Model_DiFactory::getCatchAllManager();
        if(!($catchAll = $catchAllManager->getCatchAllByDomain($domain->getId()))) {
            if(!MazelabVpopqmail_Model_DiFactory::getCatchAllManager()->createDefaultCatchAll($domain->getId())) {
                Core_Model_DiFactory::getMessageManager()
                        ->addError(self::ERROR_COULDNT_CREATE_CATCHALL);
                return false;
            }
            
            $catchAll = $catchAllManager->getCatchAllByDomain($domain->getId());
        }
        
        if (key_exists('behavior', $data)){
            $data["behavior"] = strtolower($data['behavior']);
        }
        
        if (empty($data)) {
            $catchAll->evalReport(array('status' => false));
        } else {
            $catchAll->evalReport($data);
        }
    }
    
    /**
     * evaluates status report for a domain
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @param string $nodeId
     * @param array $data
     */
    protected function _evalDomainReport(Core_Model_ValueObject_Domain $domain, $nodeId, array $data)
    {
        if(!isset($data[self::KEY_ACCOUNTS])) {
            $data[self::KEY_ACCOUNTS] = array();
        }
        
        if(!isset($data[self::KEY_FORWARDERS])) {
            $data[self::KEY_FORWARDERS] = array();
        }
        
        if(!isset($data[self::KEY_CATCHALL])) {
            $data[self::KEY_CATCHALL] = array();
        }
        
        if(!isset($data[self::KEY_ROBOTS])) {
            $data[self::KEY_ROBOTS] = array();
        }
        
        if(!isset($data[self::KEY_LISTS])) {
            $data[self::KEY_LISTS] = array();
        }
        
        $this->_evalAccounts($domain, $nodeId, $data[self::KEY_ACCOUNTS]);
        $this->_evalForwarders($domain, $nodeId, $data[self::KEY_FORWARDERS]);
        $this->_evalCatchAll($domain, $data[self::KEY_CATCHALL]);
        $this->_evalRobots($domain, $nodeId, $data[self::KEY_ROBOTS]);
        $this->_evalLists($domain, $nodeId, $data[self::KEY_LISTS]);
    }
    
    /**
     * evals domain report for forwarders
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @param string $nodeId
     * @param array $data
     */
    protected function _evalForwarders(Core_Model_ValueObject_Domain $domain, $nodeId, array $data)
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        
        $unknownForwarder = $data;
        foreach($forwarderManager->getForwardersByDomain($domain->getId()) as $forwarder) {
            $email_md5 = md5(strtolower($forwarder->getEmail()));
            
            if(key_exists($email_md5, $data)) {
                $forwarder->evalReport($data[$email_md5]);
            } else {
                $forwarder->evalReport(array('status' => false));
            }
            
            unset($unknownForwarder[$email_md5]);
        }
        
        if(!empty($unknownForwarder)) {
            foreach ($unknownForwarder as $email_md5 => $context) {
                $forwarderManager->importForwarderFromReport($domain->getId(), $context);
            }
        }
    }
    
    /**
     * evals domain report for lists
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @param string $nodeId
     * @param array $data
     */
    protected function _evalLists(Core_Model_ValueObject_Domain $domain, $nodeId, array $data)
    {
        $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
        
        $unknownLists = $data;
        foreach($mailingListManager->getMailingListsByDomain($domain->getId()) as $list) {
            $email_md5 = md5(strtolower($list->getEmail()));

            if(key_exists($email_md5, $data)) {
                if (strtolower($list->getEmail()) == $data[$email_md5]["email"]){
                    $data[$email_md5]["email"] = $list->getEmail();
                }
                $list->evalReport($data[$email_md5]);
            } else {
                $list->evalReport(array('status' => false));
            }
            
            unset($unknownLists[$email_md5]);
        }

        if(!empty($unknownLists)) {
            foreach ($unknownLists as $email_md5 => $context) {
                $mailingListManager->importListFromReport($domain->getId(), $context);
            }
        }
    }    

    /**
     * evals domain report for robots
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @param string $nodeId
     * @param array $data
     */
    protected function _evalRobots(Core_Model_ValueObject_Domain $domain, $nodeId, array $data)
    {
        $mailRobotManager = MazelabVpopqmail_Model_DiFactory::getMailRobotManager();
        
        $unknownRobots = $data;
        foreach($mailRobotManager->getMailRobotsByDomain($domain->getId()) as $robot) {
            $email_md5 = md5(strtolower($robot->getEmail()));
            
            if(key_exists($email_md5, $data)) {
                $robot->evalReport($data[$email_md5]);
            } else {
                $robot->evalReport(array('status' => false));
            }
            
            unset($unknownRobots[$email_md5]);
        }
        
        if(!empty($unknownRobots)) {
            foreach ($unknownRobots as $email_md5 => $context) {
                $mailRobotManager->importRobotFromReport($domain->getId(), $context);
            }
        }
    }
    
    /**
     * process report of a certain node
     * 
     * @param string $nodeId
     * @param string $report
     * @return boolean
     */
    public function reportNode($nodeId, $report)
    {
        if(!($node = Core_Model_DiFactory::getNodeManager()->getNode($nodeId)) ||
                !$node->hasService(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)) {
            return false;
        }
        
        $data = json_decode($report, true);
        if(!$data || !key_exists('domains', $data) || !is_array($data['domains'])) {
            $data['domains'] = array();
        }

        $unknownDomains = $data['domains'];
        foreach(MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByNode($nodeId) as $domain) {
            $domain_md5 = md5(strtolower($domain->getName()));

            if(key_exists($domain_md5, $data['domains'])) {
                $this->_evalDomainReport($domain, $node->getId(), $data['domains'][$domain_md5]);
            } else {
                $this->_evalDomainReport($domain, $node->getId(), array());
            }
            
            unset($unknownDomains[$domain_md5]);
        }
        
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!empty($unknownDomains)) {
            foreach ($unknownDomains as $domain_md5 => $context) {
                if (!key_exists('name', $context)) {
                    continue;
                }

                if(($domain = $domainManager->getDomainByName($context['name']))) {
                    continue;
                }
                
                $domainManager->logUnregisteredDomain($context['name'], $context,
                        $node->getId(), MazelabVpopqmail_Model_ConfigManager::MODULE_NAME);
            }
        }
        
        return true;
    }
    
}
