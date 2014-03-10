<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_Forwarder
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
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
     * add forwarder target
     * 
     * @param string $accountId
     * @param array $data
     * @return boolean
     */
    public function addForwarderTarget($accountId, $data)
    {
        $accountId = new MongoId($accountId);
        
        $query = array(
            self::KEY_ID => $accountId,
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER
        );

        $newData = array(
            '$set' => $this->_getDatabase()->prepareUpdateDataSet(array(
                'forwardTo' => $data
            ))
        );
        
        $options = array(
            'upsert' => true,
            'j' => true
        );

        return $this->_getCollection()->update($query, $newData, $options);
    }
    
    /**
     * deletes a certain forwarder
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder
     * @return boolean
     */
    public function deleteForwarder(MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder) {
        $mongoId = new MongoId($forwarder->getId());
        
        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER
        );
        
        $options = array(
            'j' => true
        );
        
        return $this->_getCollection()->remove($query, $options);
    }
    
    /**
     * gets a certain forwarder by id
     * 
     * @param type $accountId
     * @return array
     */
    public function getForwarder($accountId)
    {
        $mongoId = new MongoId($accountId);
        
        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER
        );
        
        $forwarder = $this->_getCollection()->findOne($query);
        if(!empty($forwarder)) {
            $forwarder['_id'] = (string) $forwarder['_id'];
        }
        
        return $forwarder;
    }
    
    /**
     * gets a certain forwarder by email
     * 
     * @param string $email
     * @return array
     */
    public function getForwarderByEmail($email)
    {
        $query = array(
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER,
            self::KEY_LABEL => (string) $email,
        );
        
        $forwarder = $this->_getCollection()->findOne($query);
        if(!empty($forwarder)) {
            $forwarder['_id'] = (string) $forwarder['_id'];
        }
        
        return $forwarder;
    }
    
    /**
     * gets all forwarder which are attached to the given email address
     * 
     * @param string $email
     * @return array
     */
    public function getForwarderByTarget($email)
    {
        $forwarders = array();
        $query = array(
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER,
            self::KEY_FORWARDTO . '.' . md5($email) => array(
                '$exists' => true
            )
        );
        
        foreach($this->_getCollection()->find($query) as $forwarderId => $forwarder) {
            $forwarder[self::KEY_ID] = $forwarderId;
            $forwarders[$forwarderId] = $forwarder;
        }
        
        return $forwarders;
    }
    
    /**
     * get all forwarders
     * 
     * @return array
     */
    public function getForwarders()
    {
        $forwarders = array();
        $query = array(
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER
        );
        
        $sort = array(
            self::KEY_ID => 1
        );
        
        foreach($this->_getCollection()->find($query)->sort($sort) as $forwarderId => $forwarder) {
            $forwarder[self::KEY_ID] = $forwarderId;
            $forwarders[$forwarderId] = $forwarder;
        }
        
        return $forwarders;
    }
    
    /**
     * returns all forwarder of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getForwardersByDomain($domainId)
    {
        $forwarders = array();
        $query = array(
            self::KEY_DOMAINID => (string) $domainId, 
            self::KEY_TYPE => self::KEY_TYPE_FORWARDER
        );
        
        $sort = array(
            self::KEY_ID => 1
        );
        
        foreach($this->_getCollection()->find($query)->sort($sort) as $forwarderId => $forwarder) {
            $forwarder[self::KEY_ID] = $forwarderId;
            $forwarders[$forwarderId] = $forwarder;
        }
        
        return $forwarders;
    }
    
    /**
     * saves forwarder account in the data backend
     * 
     * @param array $data
     * @param string $accountId
     * @return string $accountId
     */
    public function saveForwarder($data, $id = null)
    {
        $mongoId = new MongoId($id);

        $data[self::KEY_ID] = $mongoId;
        $data[self::KEY_TYPE] = self::KEY_TYPE_FORWARDER;
        
        $options = array(
            'j' => true
        );
        
        if(!($result = $this->_getCollection()->save($data, $options))) {
            return false;
        }
        
        return (string) $mongoId;
    }

}
