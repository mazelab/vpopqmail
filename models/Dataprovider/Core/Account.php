<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_Account
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_Account
{
 
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getCollection()->ensureIndex(array(
            self::KEY_TYPE => 1,
            self::KEY_LABEL => 1
        ));

        $this->_getCollection()->ensureIndex(array(
            self::KEY_DOMAINID => 1,
            self::KEY_TYPE => 1
        ));
    }
    
    /**
     * deletes a certain account
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Account $account
     * @return boolean
     */
    public function deleteAccount(MazelabVpopqmail_Model_ValueObject_Account $account) {
        $mongoId = new MongoId($account->getId());
        
        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT
        );
        
        $options = array(
            "j" => true
        );
        
        return $this->_getCollection()->remove($query, $options);
    }
    
    /**
     * gets a certain email account by id
     * 
     * @param string $accountId
     * @return array
     */
    public function getAccount($accountId)
    {
        $mongoId = new MongoId($accountId);
        
        $query = array(
            self::KEY_ID => $mongoId, 
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT
        );
        
        $account = $this->_getCollection()->findOne($query);
        if(!empty($account)) {
            $account[self::KEY_ID] = (string) $account[self::KEY_ID];
        }

        return $account;
    }
    
    /**
     * returns a account by email
     * 
     * @param string $email
     * @return array
     */
    public function getAccountByEmail($email)
    {
        $index = array(
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT,
            self::KEY_LABEL => (string) $email,
        );
        
        $account = $this->_getCollection()->findOne($index);
        if(!empty($account)) {
            $account[self::KEY_ID] = (string) $account[self::KEY_ID];
        }
        
        return $account;
    }
    
    /**
     * returns all accounts
     * 
     * @return array
     */
    public function getAccounts()
    {
        $accounts = array();
        $query = array(
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT
        );
        
        foreach($this->_getCollection()->find($query) as $accountId => $account) {
            $account[self::KEY_ID] = $accountId;
            $accounts[$accountId] = $account;
        }
        
        return $accounts;
    }
    
    /**
     * returns all accounts of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getAccountsByDomain($domainId)
    {
        $accounts = array();
        $query = array(
            self::KEY_DOMAINID => (string) $domainId,
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT
        );
        
        foreach($this->_getCollection()->find($query) as $accountId => $account) {
            $account[self::KEY_ID] = $accountId;
            $accounts[$accountId] = $account;
        }
        
        return $accounts;
    }
    
    /**
     * saves email account in the data backend
     * 
     * @param array $data
     * @param string $accountId
     * @return $accountId
     */
    public function saveAccount($data, $accountId = null)
    {
        $mongoId = new MongoId($accountId);

        $data[self::KEY_ID] = $mongoId;
        $data[self::KEY_TYPE] = self::KEY_TYPE_ACCOUNT;
        
        $options = array(
            "j" => true
        );
        
        if(!($result = $this->_getCollection()->save($data, $options))) {
            return false;
        }
        
        return (string) $mongoId;
    }

}

