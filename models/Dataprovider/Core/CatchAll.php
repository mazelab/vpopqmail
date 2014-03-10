<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_CatchAll
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll
{
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getCollection()->ensureIndex(array(
            self::KEY_TYPE => 1,
            self::KEY_DOMAINNAME => 1
        ));

        $this->_getCollection()->ensureIndex(array(
            self::KEY_DOMAINID => 1,
            self::KEY_TYPE => 1
        ));
    }
    
    /**
     * deletes a certain catch-all
     * 
     * @param MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll
     * @return boolean
     */
    public function deleteCatchAll(MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll) {
        $mongoId = new MongoId($catchAll->getId());
        
        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL
        );
        
        $options = array(
            "j" => true
        );
        
        return $this->_getCollection()->remove($query, $options);
    }
 
    /**
     * returns content of catch-all from given id
     * 
     * @param string $id
     * @return array
     */
    public function getCatchAll($id)
    {
        $mongoId = new MongoId($id);
        
        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL
        );
        
        $catchAll = $this->_getCollection()->findOne($query);
        if(!empty($catchAll)) {
            $catchAll[self::KEY_ID] = (string) $catchAll[self::KEY_ID];
        }
        
        return $catchAll;
    }
    
    /**
     * returns content of catch from a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getCatchAllByDomain($domainId)
    {
        $query = array(
            self::KEY_DOMAINID => $domainId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL
        );
        
        $catchAll = $this->_getCollection()->findOne($query);
        if(!empty($catchAll)) {
            $catchAll[self::KEY_ID] = (string) $catchAll[self::KEY_ID];
        }
        
        return $catchAll;
    }
    
    /**
     * returns content of catch from a certain domain name
     * 
     * @param string $domainName
     * @return array
     */
    public function getCatchAllByDomainName($domainName)
    {
        $query = array(
            self::KEY_DOMAINNAME => $domainName,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL
        );
        
        $catchAll = $this->_getCollection()->findOne($query);
        if(!empty($catchAll)) {
            $catchAll[self::KEY_ID] = (string) $catchAll[self::KEY_ID];
        }
        
        return $catchAll;
    }
    
    /**
     * gets all catch alls
     * 
     * @return array
     */
    public function getCatchAlls()
    {
        $catchAlls = array();
        $query = array(
            self::KEY_TYPE => self::KEY_TYPE_CATCHALL
        );
        
        foreach($this->_getCollection()->find($query) as $catchAllId => $catchAll) {
            $catchAll[self::KEY_ID] = $catchAllId;
            $catchAlls[$catchAllId] = $catchAll;
        }
        
        return $catchAlls;
    }
    
    /**
     * saves given data set to a certain catch-all
     * 
     * if id is not provided, it will create a new item
     * 
     * @param string $catchAllId
     * @param array $data
     * @return string|false id of saved item
     */
    public function saveCatchAll(array $data, $catchAllId = null)
    {
        $mongoId = new MongoId($catchAllId);

        $data[self::KEY_ID] = $mongoId;
        $data[self::KEY_TYPE] = MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL;
        
        $options = array(
            "j" => true
        );
        
        if(!($result = $this->_getCollection()->save($data, $options))) {
            return false;
        }
        
        return (string) $mongoId;
    }
    
}
