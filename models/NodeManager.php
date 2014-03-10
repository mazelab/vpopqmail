<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_NodeManager
{

    /**
     * returns all nodes which are assigned to q-vpop module
     * 
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getEmailNodes()
    {
        return Core_Model_DiFactory::getNodeManager()->getNodesByService(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME);
    }
    
    /**
     * returns nodes where domains of a certain client are assigned
     * 
     * @param string $clientId
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByClient($clientId)
    {
        $nodes = array();
     
        foreach(array_keys(MazelabVpopqmail_Model_DiFactory::getDomainManager()
                ->getEmailDomainsByOwner($clientId)) as $domainId) {
            if(($node = $this->getNodeOfDomain($domainId)) && !key_exists($node->getId(), $nodes)) {
                $nodes[$node->getId()] = $node;
            }
        }
        
        return $nodes;
    }
    
    /**
     * returns a certain node where a certain domain is assigned
     * 
     * @param string $domainId
     * @return Core_Model_ValueObject_Node|null
     */
    public function getNodeOfDomain($domainId)
    {
        if(!($module = MazelabVpopqmail_Model_DiFactory::getConfigManager()->getModule())) {
            return null;
        }
        
        foreach($module->getNodeConfig() as $nodeId => $node) {
            if(isset($node['domains'][$domainId])) {
                return Core_Model_DiFactory::getNodeManager()->getNode($nodeId);
            }
        }
        
        return null;
    }
    
}
