<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Apply_Account
    extends MazelabVpopqmail_Model_Apply_Commands
{
    
    /**
     * @var MazelabVpopqmail_Model_ValueObject_Account
     */
    protected $_account;

    /**
     * gets current Account instance
     * 
     * @return MazelabVpopqmail_Model_ValueObject_Account
     */
    protected function _getAccount()
    {
        return $this->_account;
    }
    
    /**
     * gets commands in order to achieve desired state
     * 
     * @return array|null
     */
    protected function _getCommands()
    {
        if($this->_getAccount()->getData('delete') === true) {
            return $this->_getCommandsDelete();
        }
        
        // no conflicts then do nothing and manuell conflicts must be resolved before
        if(!$this->_getAccount()->getConflicts() || 
                $this->_getAccount()->getConflicts(MazeLib_Bean::STATUS_MANUALLY)) {
            return null;
        }
        
        if(!$this->_getAccount()->getStatus() && $this->_getAccount()->getBean()->hasConflict('status')) {
            return $this->_getCommandsDeactivate();
        } elseif ($this->_getAccount()->getBean()->hasConflict('status')) {
            return $this->_getCommandsCreate();
        }
        
        return $this->_getCommandsSet();
    }
    
    /**
     * get create commands for current forwarder
     * 
     * @return array
     */
    protected function _getCommandsCreate()
    {
        $commands = array();
        if(($commandsSet = $this->_getCommandsSet(true))) {
            $commands[] = "domain add {$this->_getEscapedDomain()}";
            $commands[] = "account add {$this->_getEscapedEmail()}";
            $commands[] = "account enable {$this->_getEscapedEmail()}";
            
            $commands = array_merge($commands, $commandsSet);
        }
        
        return $commands;
    }
    
    /**
     * get deactivate commands for current account
     * 
     * @return array
     */
    protected function _getCommandsDeactivate()
    {
        $commands[] = "account disable {$this->_getEscapedEmail()}";
        return $commands;
    }
    
    /**
     * get delete commands for current account
     * 
     * @return array
     */
    protected function _getCommandsDelete()
    {
        $commands[] = "account delete {$this->_getEscapedEmail()}";
        return $commands;
    }
    
    /**
     * get deactivate commands for current account
     * 
     * @return array
     */
    protected function _getCommandsSet($force = false)
    {
        $commands = array();
        if($force || $this->_getAccount()->getBean()->hasConflict('password')) {
            $commands[] = "account password {$this->_getEscapedEmail()} {$this->_getEscapedPassword()}";
        }
        
        if($force || $this->_getAccount()->getBean()->hasConflict('quota')) {
            $commands[] = "account quota {$this->_getEscapedEmail()} {$this->_getEscapedQuota()}";
        }
        
        return $commands;
    }
    
    /**
     * get escaped email from current account
     * 
     * @return string
     */
    protected function _getEscapedEmail()
    {
        return escapeshellarg($this->_getAccount()->getEmail());
    }
    
    /**
     * get escaped domain from current account
     * 
     * @return string
     */
    protected function _getEscapedDomain()
    {
        return escapeshellarg($this->_getAccount()->getData('domainName'));
    }
    
    /**
     * get escaped password from current account
     * 
     * @return string
     */
    protected function _getEscapedPassword()
    {
        return escapeshellarg($this->_getAccount()->getData('password'));
    }
    
    /**
     * get escaped quota from current account
     * 
     * calculates quota mb into quota byte
     * 
     * @return string
     */
    protected function _getEscapedQuota()
    {
        $quota = $this->_getAccount()->getQuota();
        if(!$quota || $quota == "") {
            $quota = 'NOQUOTA';
        } else {
            $quota = $quota * 1048576;
        }
            
        return escapeshellarg($quota);
    }

    /**
     * apply given account instance
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Account $catchAll
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply(MazelabVpopqmail_Model_ValueObject_Account $account, $save = true)
    {
        $this->_account = $account;
        if(!($node = $this->_getAccount()->getNode())) {
            return false;
        }
        
        if(!($commands = $this->_getCommands())) {
            return true;
        }
        
        $key = "account {$account->getId()}";
        if(($result = $node->getCommands()->addContextCommands(self::MODULE_NAME, $key, $commands)) && $save) {
            return $node->getCommands()->save();
        }
        
        return $result;
    }
    
    /**
     * remove commands from current node
     * 
     * @param $account MazelabVpopqmail_Model_ValueObject_Account 
     * @return boolean
     */
    public function remove(MazelabVpopqmail_Model_ValueObject_Account $account)
    {
        $this->_account = $account;
        if(!($node = $this->_getAccount()->getNode()) || !($commands = $node->getCommands())) {
            return true;
        }
        
        $key = "account {$account->getId()}";
        if(!$commands->addContextCommands(self::MODULE_NAME, $key, array())) {
            return false;
        }
        
        return $commands->save();
    }
    
}
