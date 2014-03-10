<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

interface MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot
{

    /**
     * deletes a certain mail robot
     * 
     * @param MazelabVpopqmail_Model_ValueObject_MailRobot $mailRobot
     * @return boolean
     */
    public function deleteRobot(MazelabVpopqmail_Model_ValueObject_MailRobot $mailRobot);

    /**
     * returns content of mail robot from given id
     * 
     * @param string $id
     * @return array
     */
    public function getMailRobot($id);
    
    /**
     * returns content of mail robot by email
     * 
     * @param string $email
     * @return array
     */
    public function getMailRobotByEmail($email);

    /**
     * get all mail robots
     * 
     * @return array
     */
    public function getMailRobots();
    
    /**
     * returns content of all mail robots from given domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getMailRobotsByDomain($domainId);
    
    /**
     * saves given data set to a certain mail robot
     * 
     * if id is not provided, it will create a new item
     * 
     * @param string $id
     * @param array $data
     * @return string|false id of saved item
     */
    public function saveMailRobot(array $data, $id = null);

}
