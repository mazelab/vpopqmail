<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_MailRobot 
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot
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
     * deletes a certain mail robot
     * 
     * @param MazelabVpopqmail_Model_ValueObject_MailRobot $mailRobot
     * @return boolean
     */
    public function deleteRobot(MazelabVpopqmail_Model_ValueObject_MailRobot $mailRobot)
    {
        $mongoId = new MongoId($mailRobot->getId());

        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT
        );
        
        $options = array(
            'j' => true
        );

        return $this->_getCollection()->remove($query, $options);
    }

    /**
     * returns content of mail robot from given id
     * 
     * @param string $id
     * @return array
     */
    public function getMailRobot($id)
    {
        $mongoId = new MongoId($id);

        $query = array(
            self::KEY_ID => $mongoId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT
        );
        
        $mailRobot = $this->_getCollection()->findOne($query);
        if (!empty($mailRobot)) {
            $mailRobot[self::KEY_ID] = (string) $mailRobot[self::KEY_ID];
        }

        return $mailRobot;
    }
    
    /**
     * returns content of mail robot by email
     * 
     * @param string $email
     * @return array
     */
    public function getMailRobotByEmail($email)
    {
        $query = array(
            self::KEY_LABEL => $email,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT
        );
        
        $mailRobot = $this->_getCollection()->findOne($query);
        if (!empty($mailRobot)) {
            $mailRobot[self::KEY_ID] = (string) $mailRobot[self::KEY_ID];
        }

        return $mailRobot;
    }
    
    /**
     * get all mail robots
     * 
     * @return array
     */
    public function getMailRobots()
    {
        $robots = array();
        $query = array(
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT
        );

        foreach($this->_getCollection()->find($query) as $robotId => $robot) {
            $robot[self::KEY_ID] = $robotId;
            $robots[$robotId] = $robot;
        }
        
        return $robots;
    }
    
    /**
     * returns content of all mail robots from given domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getMailRobotsByDomain($domainId)
    {
        $robots = array();
        $query = array(
            self::KEY_DOMAINID => $domainId,
            self::KEY_TYPE => MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT
        );

        foreach($this->_getCollection()->find($query) as $robotId => $robot) {
            $robot[self::KEY_ID] = $robotId;
            $robots[$robotId] = $robot;
        }
        
        return $robots;
    }

    /**
     * saves given data set to a certain mail robot
     * 
     * if id is not provided, it will create a new item
     * 
     * @param string $id
     * @param array $data
     * @return string|false id of saved item
     */
    public function saveMailRobot(array $data, $id = null)
    {
        $mongoId = new MongoId($id);

        $data[self::KEY_ID] = $mongoId;
        $data[self::KEY_TYPE] = MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT;
        
        $options = array(
            'j' => true
        );
        
        if(!($result = $this->_getCollection()->save($data, $options))) {
            return false;
        }
        
        return (string) $mongoId;
    }

}

