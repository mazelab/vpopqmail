<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_Validate_ForwarderTarget extends Zend_Validate_Abstract
{
    CONST ALREADY_EXISTS = "emailAlreadyExists";
    CONST NOT_AVAILABLE = "forwarderNotAvailable";
    CONST INVALID_ACCOUNTID = "invalidAccountId";

    protected $_accountId = null;
    protected $_messageTemplates = array(
        self::ALREADY_EXISTS => "Email address '%value%' already exists",
        self::NOT_AVAILABLE => "No forwarder available",
        self::INVALID_ACCOUNTID => "Account ID not given"
    );

    public function __construct($accountId = null)
    {
        $this->_accountId = $accountId;
    }

    public function isValid($value)
    {
        $result = $this->validate($value);

        if (!$result){
            return false;
        }
 
        return true;
    }

    protected function validate($email)
    {
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();

        if ($this->_accountId == null){
            $this->_error(self::INVALID_ACCOUNTID);
            return false;
        }
        
        $forwarder = $forwarderManager->getForwarder($this->_accountId);
        if ($forwarder instanceof MazelabVpopqmail_Model_ValueObject_Forwarder == false){
            $this->_error(self::NOT_AVAILABLE);
            return false;
        }

        $targets = $forwarder->getForwarderTargets();

        if (in_array(strtolower($email), array_map("strtolower", $targets))){
            $this->_error(self::ALREADY_EXISTS, $email);
            return false;
        }

        return true;
    }

}