<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Demo_Forwarder
    extends MazelabVpopqmail_Model_Dataprovider_Demo_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
{
    
    /**
     * saving given forwarder data
     * 
     * @param array $data
     * @return string
     */
    public function _createForwarder($data)
    {
        $collection = $this->_getCollection(self::COLLECTION);
        $id = $this->_getRndId(100000);

        $collection[$id] = array(
            'user' => $data['user'],
            'domainId' => $data['domainId'],
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER,
            '_id' => $id
        );

        $this->_setCollection(self::COLLECTION, $collection);

        return $id;
    }
    
    /**
     * add forwarder target
     * 
     * @param string $accountId
     * @param array $data
     * @return boolean
     */
    public function addForwarderTarget($accountId, $data) {
        return false;
    }
    
    /**
     * deletes a certain forwarder
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder
     * @return boolean
     */
    public function deleteForwarder(MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder)
    {
        $collection = $this->_getCollection(self::COLLECTION);
        $accountId = $forwarder->getId();

        if ($forwarder->getData(self::KEY_TYPE) == self::KEY_TYPE_FORWARDER &&
                array_key_exists($accountId, $collection)) {
            unset($collection[$accountId]);
            
            $this->_setCollection(self::COLLECTION, $collection);
            return true;
        }
        
        return false;
    }

    /**
     * gets a certain forwarder by id
     * 
     * @param type $accountId
     * @return array
     */
    public function getForwarder($accountId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if (isset($collection[$accountId]) && $collection[$accountId]['type'] == self::KEY_TYPE_FORWARDER) {
            return $collection[$accountId];
        }

        return false;
    }
    
    /**
     * gets a certain forwarder by email
     * 
     * @param string $email
     * @return array
     */
    public function getForwarderByEmail($email)
    {
        return array();
    }
    
    /**
     * gets all forwarder which are attached to the given email address
     * 
     * @param string $email
     * @return array
     */
    public function getForwarderByTarget($email)
    {
        $accounts = $this->_getCollection(self::COLLECTION);
        $return = array();

        foreach ($accounts as $accountId => $account) {
            if (!isset($account['type']) || !$account['type'] == self::KEY_TYPE_FORWARDER
                    || !isset($account['forwardTo']) || !in_array($email, $account['forwardTo'])) {
                continue;
            }
            
            $return[$accountId] = $account;
        }

        return $return;
    }

    /**
     * get all forwarders
     * 
     * @return array
     */
    public function getForwarders()
    {
        return array();
    }
    
    /**
     * returns all forwarder of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getForwardersByDomain($domainId)
    {
        $accounts = $this->_getCollection(self::COLLECTION);
        $return = array();

        foreach ($accounts as $accountId => $account) {
            if (isset($account['domainId']) && $account['domainId'] == $domainId &&
                    $account['type'] == self::KEY_TYPE_FORWARDER) {
                $return[$accountId] = $account;
            }
        }

        return $return;
    }

    /**
     * saves forwarder account in the data backend
     * 
     * @param array $data
     * @param string $accountId
     * @return string $accountId
     */
    public function saveForwarder($data, $accountId = null)
    {
        if (!$accountId) {
            return $this->_createForwarder($data);
        }

        $collection = $this->_getCollection(self::COLLECTION);
        $collection[$accountId] = array_merge($collection[$accountId], $data);

        $this->_setCollection(self::COLLECTION, $collection);

        return $accountId;
    }

}
