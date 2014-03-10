<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

interface MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
{

    /**
     * add forwarder target
     * 
     * @param string $accountId
     * @param array $data
     * @return boolean
     */
    public function addForwarderTarget($accountId, $data);
    
    /**
     * deletes a certain forwarder
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder
     * @return boolean
     */
    public function deleteForwarder(MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder);
    
    /**
     * gets a certain forwarder by id
     * 
     * @param string $accountId
     * @return array
     */
    public function getForwarder($accountId);
    
    /**
     * gets a certain forwarder by email
     * 
     * @param string $email
     * @return array
     */
    public function getForwarderByEmail($email);
    
    /**
     * gets all forwarder which are attached to the given email address
     * 
     * @param string $email
     * @return array
     */
    public function getForwarderByTarget($email);
    
    /**
     * get all forwarders
     * 
     * @return array
     */
    public function getForwarders();
    
    /**
     * returns all forwarder of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getForwardersByDomain($domainId);
    
    /**
     * saves forwarder account in the data backend
     * 
     * @param array $data
     * @param string $accountId
     * @return string $accountId
     */
    public function saveForwarder($data, $accountId = null);
    
}
