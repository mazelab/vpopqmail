<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Api_Core extends Core_Model_Module_Api_Abstract
{
    CONST MESSAGE_INVALID_CHAR = '%1$s: domains with special characters are not supported';
    
    /**
     * removes the module collection
     * 
     * @return boolean
     */
    public function deinstall()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getCollection()->drop();
    }
    
    /**
     * returns all domains which are set in a particular module
     * 
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomains()
    {
        return MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomains();
    }
    
    /**
     * returns all nodes which are set in a particular module
     * 
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodes()
    {
        return MazelabVpopqmail_Model_DiFactory::getNodeManager()->getEmailNodes();
    }
    
    /**
     * returns all nodes of a certain domain
     * 
     * @param string $domainId
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByDomain($domainId)
    {
        $result = array();
        
        if(($node = MazelabVpopqmail_Model_DiFactory::getNodeManager()->getNodeOfDomain($domainId))) {
            $result[$node->getId()] = $node;
        }
        
        return $result;
    }
    
    /**
     * returns all nodes of a certain client which are set in a particular module
     * 
     * @param string $clientId
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByClient($clientId)
    {
        return MazelabVpopqmail_Model_DiFactory::getNodeManager()->getNodesByClient($clientId);
    }
    
    /**
     * returns all domains of a certain client on a certain node
     * 
     * @param string $nodeId
     * @param string $clientId
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomainsByNode($nodeId, $clientId = null)
    {
        $domainManager = MazelabVpopqmail_Model_DiFactory::getDomainManager();
        $domainsByNode = $domainManager->getEmailDomainsByNode($nodeId);
        
        if(!$clientId) {
            return $domainsByNode;
        }
        
        $domains = array();
        $clientDomains = $domainManager->getEmailDomainsByOwner($clientId);
        foreach($domainsByNode as $domainId => $domain) {
            if(key_exists($domainId, $clientDomains)) {
                $domains[$domainId] = $domain;
            }
        }
        
        return $domains;
    }
    
    /**
     * returns all clients of a certain node which are set in a particular module
     * 
     * @param string $nodeId
     * @return array contains Core_Model_ValueObject_Client
     */
    public function getClientsByNode($nodeId)
    {
        return MazelabVpopqmail_Model_DiFactory::getClientManager()->getClientsByNode($nodeId);
    }

    /**
     * trigger when the module will be removed
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function removeDomainService($domainId)
    {
        return MazelabVpopqmail_Model_DiFactory::getDomainManager()->removeDomain($domainId);
    }

    /**
     * process report of a certain node
     * 
     * if false will be returned then the report will be abort
     * 
     * @param string $nodeId
     * @param string $report
     * @return boolean
     */
    public function reportNode($nodeId, $report)
    {
        $reportManager = MazelabVpopqmail_Model_DiFactory::getReportManager();
        
        return $reportManager->reportNode($nodeId, $report);
    }
    
    /**
     * validates the given domain name
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function validateDomainForService($domainId)
    {
        if (($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId)) &&
                preg_replace("/[^öüäĉŝµ]/i", "", $domain->getName()) != "") {
            Core_Model_DiFactory::getMessageManager()->addNotification(self::MESSAGE_INVALID_CHAR, MazelabVpopqmail_Model_ConfigManager::MODULE_NAME);
            return false;
        }

        return true;
    }
}

