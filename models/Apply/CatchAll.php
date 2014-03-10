<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazelabVpopqmail_Model_Apply_CatchAll
    extends MazelabVpopqmail_Model_Apply_Commands
{
    
    /**
     * @var MazelabVpopqmail_Model_ValueObject_CatchAll
     */
    protected $_catchAll;

    /**
     * gets current catch all instance
     * 
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll
     */
    protected function _getCatchAll()
    {
        return $this->_catchAll;
    }
    
    /**
     * gets commands in order to achieve desired state
     * 
     * @return array|null
     */
    protected function _getCommands()
    {
        if(!$this->_getCatchAll()->getConflicts()) {
            return null;
        }
        
        return $this->_getCommandsSet();
    }
    
    /**
     * get deactivate commands for current catchall
     * 
     * @return array
     */
    protected function _getCommandsSet()
    {
        $commands[] = "domain add {$this->_getEscapedDomain()}";
        $commands[] = "catchall set {$this->_getEscapedDomain()} {$this->_getEscapedBehavior()}";
        
        return $commands;
    }
    
    /**
     * get escaped domain from current catch all
     * 
     * @return string
     */
    protected function _getEscapedDomain()
    {
        return escapeshellarg($this->_getCatchAll()->getData('domainName'));
    }
    
    /**
     * get escaped behavior from current catch all
     * 
     * @return string
     */
    protected function _getEscapedBehavior()
    {
        return $this->_escapeshellargSpecial($this->_getCatchAll()->getData('behavior'));
    }

    /**
     * apply given catchAll instance
     * 
     * @param MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply(MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll, $save = true)
    {
        $this->_catchAll = $catchAll;
        if(!($node = $this->_getCatchAll()->getNode())) {
            return false;
        }
        
        if(!($commands = $this->_getCommands())) {
            return true;
        }
        
        $key = "catchall {$this->_getEscapedDomain()}";
        if(($result = $node->getCommands()->addContextCommands(self::MODULE_NAME, $key, $commands)) && $save) {
            return $node->getCommands()->save();
        }
        
        return $result;
    }
    
    /**
     * remove commands from current node
     * 
     * @param $account MazelabVpopqmail_Model_ValueObject_CatchAll 
     * @return boolean
     */
    public function remove(MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll)
    {
        $this->_catchAll = $catchAll;
        if(!($node = $this->_getCatchAll()->getNode()) || !($commands = $node->getCommands())) {
            return false;
        }
        
        $key = "catchall {$this->_getEscapedDomain()}";
        if(!$commands->addContextCommands(self::MODULE_NAME, $key, array())) {
            return false;
        }
        
        return $commands->save();
    }
    
}
