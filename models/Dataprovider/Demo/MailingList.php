<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Demo_MailingList
    extends MazelabVpopqmail_Model_Dataprovider_Demo_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_MailingList
{
    
    /**
     * deletes a certain mailing list
     * 
     * @param MazelabVpopqmail_Model_ValueObject_MailingList $mailingList
     * @return boolean
     */
    public function deleteMailingList(MazelabVpopqmail_Model_ValueObject_MailingList $mailingList)
    {
        return false;
    }
    
    /**
     * returns content of mailing list from given id
     * 
     * @param string $id
     * @return array
     */
    public function getMailingList($id)
    {
        return array();
    }
    
    /**
     * returns content of mailing list from given email
     * 
     * @param string $email
     * @return array
     */
    public function getMailingListByEmail($email)
    {
        return array();
    }
    
    /**
     * get all mailing lists
     * 
     * @return array
     */    
    public function getMailingLists()
    {
        return array();
    }
    
    /**
     * returns all mailing lists of a certain domain
     * 
     * @param string $id
     * @return array
     */    
    public function getMailingListsByDomain($domainId)
    {
        return array();
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
        return false;
    }
    
}
