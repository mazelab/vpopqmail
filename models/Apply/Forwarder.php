<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Apply_Forwarder
    extends MazelabVpopqmail_Model_Apply_Commands
{
    
    /**
     * @var MazelabVpopqmail_Model_ValueObject_Forwarder
     */
    protected $_forwarder;

    /**
     * gets commands in order to achieve desired state
     * 
     * @return array|null
     */
    protected function _getCommands()
    {
        if($this->_getForwarder()->getData('delete') === true) {
            return $this->_getCommandsDelete();
        }
        
        // no conflicts then do nothing and manuell conflicts must be resolved before
        if(!$this->_getForwarder()->getConflicts() || 
                $this->_getForwarder()->getConflicts(MazeLib_Bean::STATUS_MANUALLY) ||
                !$this->_getForwarder()->getForwarderTargets(false, false)) {
            return null;
        }
        
        if(!$this->_getForwarder()->getStatus() && $this->_getForwarder()->getBean()->hasConflict('status')) {
            return $this->_getCommandsDelete();
        } elseif ($this->_getForwarder()->getBean()->hasConflict('status')) {
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
        if(($commandsSet = $this->_getCommandsSet())) {
            $commands[] = "domain add {$this->_getEscapedDomain()}";
            
            $commands = array_merge($commands, $commandsSet);
        }
        
        return $commands;
    }
    
    /**
     * get delete commands for current forwarder
     * 
     * @return array
     */
    protected function _getCommandsDelete()
    {
        $commands[] = "forwarder delete {$this->_getEscapedEmail()}";
        return $commands;
    }
    
    /**
     * get deactivate commands for current forwarder
     * 
     * @return array
     */
    protected function _getCommandsSet()
    {
        $commands[] = "forwarder delete {$this->_getEscapedEmail()}";
        foreach($this->_getForwarder()->getForwarderTargets() as $target) {
            $commands[] = "forwarder add {$this->_getEscapedEmail()} {$this->_getEscapedTargets($target)}";
        }
        
        return $commands;
    }
    
    /**
     * get escaped email from current forwarder
     * 
     * @return string
     */
    protected function _getEscapedEmail()
    {
        return escapeshellarg($this->_getForwarder()->getEmail());
    }
    
    /**
     * get escaped domain from current forwarder
     * 
     * @return string
     */
    protected function _getEscapedDomain()
    {
        return escapeshellarg($this->_getForwarder()->getData('domainName'));
    }
    
    /**
     * escape given forwarder target
     * 
     * @param string $target
     * @return string
     */
    protected function _getEscapedTargets($target)
    {
        return $this->_escapeshellargSpecial($target);
    }
    
    /**
     * gets current forwarder instance
     * 
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder
     */
    protected function _getForwarder()
    {
        return $this->_forwarder;
    }

    /**
     * apply given forwarder instance
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply(MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder, $save = true)
    {
        $this->_forwarder = $forwarder;
        if(!($node = $this->_getForwarder()->getNode())) {
            return false;
        }
        
        if(!($commands = $this->_getCommands())) {
            return true;
        }
        
        $key = "forwarder {$forwarder->getId()}";
        if(($result = $node->getCommands()->addContextCommands(self::MODULE_NAME, $key, $commands)) && $save) {
            return $node->getCommands()->save();
        }
        
        return $result;
    }
    
    /**
     * remove commands from current node
     * 
     * @param $forwarder MazelabVpopqmail_Model_ValueObject_Forwarder
     * @return boolean
     */
    public function remove(MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder)
    {
        $this->_forwarder = $forwarder;
        if(!($node = $this->_getForwarder()->getNode()) || !($commands = $node->getCommands())) {
            return true;
        }
        
        $key = "forwarder {$forwarder->getId()}";
        if(!$commands->addContextCommands(self::MODULE_NAME, $key, array())) {
            return false;
        }
        
        return $commands->save();
    }
    
}
