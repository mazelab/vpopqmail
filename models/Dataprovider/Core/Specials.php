<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_Specials
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_Specials
{
    
    /**
     * returns mongoDb find keys for the special types
     * 
     * @return array
     */
    protected function _getSpecialTypesWithOrOperator()
    {
        $types = array(
            '$or' => array(
                array(self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL),
                array(self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST),
                array(self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT)
            )
        );
        
        return $types;
    }
    
    /**
     * gets a certain special without typification
     * 
     * @param string $specialId
     * @return array
     */
    public function getSpecial($specialId)
    {
        $query = array(
            '$and' => array(
                array(self::KEY_ID => new MongoId($specialId)),
                $this->_getSpecialTypesWithOrOperator()
            )
        );
        
        $special = $this->_getCollection()->findOne($query);
        if(!empty($special)) {
            $special[self::KEY_ID] = (string) $special[self::KEY_ID];
        }
        
        return $special;
    }
    
    /**
     * returns a special by email
     * 
     * @param string $email
     * @return array
     */
    public function getSpecialByEmail($email)
    {
        $query = array(
            '$and' => array(
                array(self::KEY_LABEL => (string) $email),
                array('$or' => array(
                    array(self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST),
                    array(self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT)
                ))
            )
        );
        
        $special = $this->_getCollection()->findOne($query);
        if(!empty($special)) {
            $special[self::KEY_ID] = (string) $special[self::KEY_ID];
        }
        
        return $special;
    }
    
    /**
     * returns all specials of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getSpecialsByDomain($domainId)
    {
        $specials = array();
        $query = array(
            '$and' => array(
                array(self::KEY_DOMAINID => $domainId),
                $this->_getSpecialTypesWithOrOperator()
            )
        );
        
        foreach($this->_getCollection()->find($query) as $specialId => $special) {
            $special[self::KEY_ID] = $specialId;
            $specials[$specialId] = $special;
        }
        
        return $specials;
    }
    
}
