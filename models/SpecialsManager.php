<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_SpecialsManager
{
    
    /**
     * identifier for catch-all instances
     * 
     * @var string
     */
    CONST CATCH_ALL = "catchAll";
    
    /**
     * identifier for mail robots instances
     * 
     * @var string
     */
    CONST MAIL_ROBOT = 'mailRobot';

    /**
     * * identifier for mailing list instances
     * 
     * @var string
     */
    CONST MAILING_LIST = 'mailingList';
    
    /**
     * changes the status of a certain special
     * 
     * @param string $specialId
     * @return boolean
     */
    public function changeSpecialState($specialId)
    {
        if(!($special = $this->getSpecial($specialId))) {
            return false;
        }
        
        if(!($currentStatus = $special->getStatus()) || $currentStatus === false) {
            if(!$special->activate()) {
                return false;
            }
        } else {
            if(!$special->deactivate()) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * deletes special by id
     * 
     * @param string $specialId
     * @return boolean
     */
    public function deleteSpecial($specialId)
    {
        if(!($special = $this->getSpecial($specialId))) {
            return false;
        }
        
        if ($special instanceof MazelabVpopqmail_Model_ValueObject_MailingList) {
            $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
            
            return $mailingListManager->flagDelete($specialId);
        } else if ($special instanceof MazelabVpopqmail_Model_ValueObject_MailRobot) {
            $mailRobotManager = MazelabVpopqmail_Model_DiFactory::getMailRobotManager();
            
            return $mailRobotManager->flagDelete($specialId);
        }
        
        return false;
    }
       
    /**
     * deletes all specials of the given domain
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function deleteSpecialsByDomain($domainId)
    {
        if (!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return false;
        }
        
        $mailRobotManager = MazelabVpopqmail_Model_DiFactory::getMailRobotManager();
        $mailListManager  = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
        
        foreach ($mailRobotManager->getMailRobotsByDomain($domain->getId()) as $mailrobot) {
            $this->deleteSpecial($mailrobot->getId());
        }
        foreach ($mailListManager->getMailingListsByDomain($domain->getId()) as $mailinglist) {
            $this->deleteSpecial($mailinglist->getId());
        }
        
        return !sizeof($mailRobotManager->getMailRobotsByDomain($domain->getId())) && 
               !sizeof($mailListManager->getMailingListsByDomain($domain->getId()));
    }
    
    /**
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Specials
     */
    public function getProvider()
    {
        return MazelabVpopqmail_Model_Dataprovider_DiFactory::getSpecials();
    }
    
    /**
     * gets certain instance of special by id
     * 
     * @param string $specialId
     * @return mixed iintance according to special type
     */
    public function getSpecial($specialId)
    {
        if(($special = MazelabVpopqmail_Model_DiFactory::getSpecial($specialId))) {
            return $special;
        }
        
        if(!($special = $this->getProvider()->getSpecial($specialId))) {
            return null;
        }
        
        if(!array_key_exists('type', $special) || !array_key_exists('_id', $special)) {
            return null;
        }
        
        if($special['type'] == self::CATCH_ALL) {
            $catchAllManager = MazelabVpopqmail_Model_DiFactory::getCatchAllManager();
            $catchAllManager->registerCatchAll($special['_id'], $special);
            
            return $catchAllManager->getCatchAll($special['_id']);
        } else if ($special['type'] == self::MAILING_LIST) {
            $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
            $mailingListManager->registerMailingList($special['_id'], $special);
            
            return $mailingListManager->getMailingList($special['_id']);
        } else if ($special['type'] == self::MAIL_ROBOT) {
            $mailRobotManager = MazelabVpopqmail_Model_DiFactory::getMailRobotManager();
            $mailRobotManager->registerMailRobot($special['_id'], $special);
            
            return $mailRobotManager->getMailRobot($special['_id']);
        }
        
        return null;
    }
    
    /**
     * gets certain instance of mailing list or mailrobot by email
     * 
     * @param string $email
     * @return mixed intance according to special type
     */
    public function getSpecialByEmail($email)
    {
        if(($special = MazelabVpopqmail_Model_DiFactory::getMailRobotByEmail($email)) ||
                ($special = MazelabVpopqmail_Model_DiFactory::getMailingListByEmail($email))) {
            return $special;
        }
        
        if(!($special = $this->getProvider()->getSpecialByEmail($email))) {
            return null;
        }
        
        if(!array_key_exists('type', $special) || !array_key_exists('_id', $special)) {
            return null;
        }
        
        if ($special['type'] == self::MAILING_LIST) {
            $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
            $mailingListManager->registerMailingList($special['_id'], $special);
            
            return $mailingListManager->getMailingList($special['_id']);
        } else if ($special['type'] == self::MAIL_ROBOT) {
            $mailRobotManager = MazelabVpopqmail_Model_DiFactory::getMailRobotManager();
            $mailRobotManager->registerMailRobot($special['_id'], $special);
            
            return $mailRobotManager->getMailRobot($special['_id']);
        }
        
        return null;
    }
    
}
