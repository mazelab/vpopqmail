<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_MailingList
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_MailingList
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
     * deletes a certain mailing list
     * 
     * @param MazelabVpopqmail_Model_ValueObject_MailingList $mailingList
     * @return boolean
     */
    public function deleteMailingList(MazelabVpopqmail_Model_ValueObject_MailingList $mailingList)
    {
        $mongoId = new MongoId($mailingList->getId());

        $keys = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST
        );

        $options = array(
            'j' => true
        );
        
        return $this->_getCollection()->remove($keys, $options);
    }
    
    /**
     * returns content of mailing list from given id
     * 
     * @param string $id
     * @return array
     */
    public function getMailingList($id)
    {
        $mongoId = new MongoId($id);
        
        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST
        );
        
        $mailingList = $this->_getCollection()->findOne($query);
        if(!empty($mailingList)) {
            $mailingList[self::KEY_ID] = (string) $mailingList[self::KEY_ID];
        }
        
        return $mailingList;
    }
    
    /**
     * returns content of mailing list from given email
     * 
     * @param string $email
     * @return array
     */
    public function getMailingListByEmail($email)
    {
        $query = array(
            self::KEY_LABEL => $email,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST
        );
        
        $mailingList = $this->_getCollection()->findOne($query);
        if(!empty($mailingList)) {
            $mailingList[self::KEY_ID] = (string) $mailingList[self::KEY_ID];
        }
        
        return $mailingList;
    }
    
    /**
     * get all mailing lists
     * 
     * @return array
     */    
    public function getMailingLists()
    {
        $lists = array();
        $query = array(
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST
        );

        foreach($this->_getCollection()->find($query) as $listId => $list) {
            $list[self::KEY_ID] = $listId;
            $lists[$listId] = $list;
        }
        
        return $lists;
    }
    
    /**
     * returns all mailing lists of a certain domain
     * 
     * @param string $id
     * @return array
     */    
    public function getMailingListsByDomain($domainId)
    {
        $lists = array();
        $query = array(
            self::KEY_DOMAINID => $domainId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST
        );

        foreach($this->_getCollection()->find($query) as $listId => $list) {
            $list[self::KEY_ID] = $listId;
            $lists[$listId] = $list;
        }
        
        return $lists;
    }
    
    /**
     * saves given data set to a certain mailing list
     * 
     * if id is not provided, it will create a new item
     * 
     * @param string $mailingListId
     * @param array $data
     * @return string|false id of saved item
     */
    public function saveMailingList(array $data, $mailingListId = null)
    {
        $mongoId = new MongoId($mailingListId);

        $data[self::KEY_ID] = $mongoId;
        $data[self::KEY_TYPE] = MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST;
        
        $options = array(
            'j' => true
        );
        
        if(!($this->_getCollection()->save($data, $options))) {
            return false;
        }
        
        return (string) $mongoId;
    }
    
}
