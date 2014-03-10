<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

interface MazelabVpopqmail_Model_Dataprovider_Interface_Account
{

    /**
     * deletes a certain account
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Account $account
     * @return boolean
     */
    public function deleteAccount(MazelabVpopqmail_Model_ValueObject_Account $account);
    
    /**
     * gets a certain email account by id
     * 
     * @param string $accountId
     * @return array
     */
    public function getAccount($accountId);
    
    /**
     * returns a account by email
     * 
     * @param string $email
     * @return array
     */
    public function getAccountByEmail($email);
    
    /**
     * returns all accounts
     * 
     * @return array
     */
    public function getAccounts();
    
    /**
     * returns all accounts of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getAccountsByDomain($domainId);
    
    /**
     * saves email account in the data backend
     * 
     * @param array $data
     * @param string $accountId
     * @return $accountId
     */
    public function saveAccount($data, $accountId = null);
    
}
