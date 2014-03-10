<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Demo_Account
    extends MazelabVpopqmail_Model_Dataprovider_Demo_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_Account
{

    /**
     * saving given account data
     * 
     * @param array $data
     * @return string
     */
    protected function _createAccount($data)
    {
        $collection = $this->_getCollection(self::COLLECTION);
        $id = $this->_getRndId(100000);

        $collection[$id] = array(
            'user' => $data['user'],
            'domainId' => $data['domainId'],
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT,
            '_id' => $id,
            'quota' => $data['quota']
        );

        $this->_setCollection(self::COLLECTION, $collection);

        return $id;
    }
    
    /**
     * deletes a certain account
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Account $account
     * @return boolean
     */
    public function deleteAccount(MazelabVpopqmail_Model_ValueObject_Account $account)
    {
        $collection = $this->_getCollection(self::COLLECTION);
        $accountId = $account->getId();

        if ($account->getData(self::KEY_TYPE) == self::KEY_TYPE_ACCOUNT &&
                array_key_exists($accountId, $collection)) {
            unset($collection[$accountId]);
            $this->_setCollection(self::COLLECTION, $collection);
            return true;
        }

        return false;
    }
    
    /**
     * gets a certain email account by id
     * 
     * @param string $accountId
     * @return array
     */
    public function getAccount($accountId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if (isset($collection[$accountId]) && $collection[$accountId]['type'] == self::KEY_TYPE_ACCOUNT) {
            return $collection[$accountId];
        }

        return false;
    }
    
    /**
     * returns all accounts
     * 
     * @return array
     */
    public function getAccounts()
    {
        return array();
    }
    
    /**
     * returns a account by email
     * 
     * @param string $email
     * @return array
     */
    public function getAccountByEmail($email)
    {
        return array();
    }
    
    /**
     * returns all accounts of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getAccountsByDomain($id)
    {
        $accounts = $this->_getCollection(self::COLLECTION);
        $return = array();

        foreach ($accounts as $accountId => $account) {
            if (isset($account['domainId']) && $account['domainId'] == $id &&
                    $account['type'] == self::KEY_TYPE_ACCOUNT) {
                $return[$accountId] = $account;
            }
        }

        return $return;
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
        if (!$accountId) {
            return $this->_createAccount($data);
        }

        $collection = $this->_getCollection(self::COLLECTION);
        $data[self::KEY_TYPE] = self::KEY_TYPE_ACCOUNT;
        $collection[$accountId] = array_merge($collection[$accountId], $data);

        $this->_setCollection(self::COLLECTION, $collection);

        return $accountId;
    }

}

