<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_ClientManager
{
    
    /**
     * returns all clients which are assigned with a domain on a certain node
     * 
     * @param string $nodeId
     * @return array contains Core_Model_ValueObject_Client
     */
    public function getClientsByNode($nodeId)
    {
        $clients = array();
        
        foreach(MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByNode($nodeId) as $domain) {
            if(($owner = $domain->getOwner()) && !array_key_exists($owner->getId(), $clients)) {
                $clients[$owner->getId()] = $owner;
            }
        }
        
        return $clients;
    }
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * gets count of used accounts of a certain client
     * 
     * @param string $clientId
     * @return int
     */
    public function getUsedAccounts($clientId)
    {
        if(!($client = Core_Model_DiFactory::getClientManager()->getClient($clientId))) {
            return 0;
        }
        
        return count(MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountsByOwner($clientId));
    }
    
    /**
     * returns the used accounts of a certain client in percent
     * 
     * @param string $clientId
     * @return int
     */
    public function getUsedAccountsInPercent($clientId)
    {
        $clientConfig = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getClientConfig($clientId);
        $usedAccounts = $this->getUsedAccounts($clientId);
        
        if(!array_key_exists('countAccounts', $clientConfig) || empty($clientConfig['countAccounts'])) {
            return 0;
        }

        if($usedAccounts >= $clientConfig['countAccounts']) {
            return 100;
        }

        return ($usedAccounts / $clientConfig['countAccounts']) * 100;
    }
    
    /**
     * gets used quota of a certain client
     * 
     * @param string $clientId
     * @return int
     */
    public function getUsedQuotas($clientId)
    {
        $usedQuota = 0;
        
        foreach(MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountsByOwner($clientId) as $account) {
            $quota = $account->getQuota();
            if(!is_numeric($quota) || empty($quota)) {
                continue;
            }
            
            $usedQuota = $usedQuota + $quota;
        }
        
        return $usedQuota;
    }
    
    /**
     * gets used quota of a certain client in percent
     * 
     * @param string $clientId
     * @return int
     */
    public function getUsedQuotasInPercent($clientId)
    {
        $clientConfig = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getClientConfig($clientId);
        $usedQuota = $this->getUsedQuotas($clientId);
        
        if(!array_key_exists('quota', $clientConfig) || empty($clientConfig['quota'])) {
            return 0;
        }
 
        if($usedQuota >= $clientConfig['quota']) {
            return 100;
        }

        return ($usedQuota / $clientConfig['quota']) * 100;
    }
    
}
