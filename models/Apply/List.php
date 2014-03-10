<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Apply_List
    extends MazelabVpopqmail_Model_Apply_Commands
{
    
    /**
     * @var MazelabVpopqmail_Model_ValueObject_MailingList
     */
    protected $_list;

    /**
     * gets commands in order to achieve desired state
     * 
     * @return array|null
     */
    protected function _getCommands()
    {
        if($this->_getList()->getData('delete') === true) {
            return $this->_getCommandsDelete();
        }
        
        if(!$this->_getList()->getConflicts()) {
            return null;
        }
        
        if(!$this->_getList()->getStatus() && $this->_getList()->getBean()->hasConflict('status')) {
            return $this->_getCommandsDisable();
        } elseif ($this->_getList()->getBean()->hasConflict('status')) {
            return $this->_getCommandsCreate();
        }
        
        return $this->_getCommandsSet();
    }
    
    /**
     * get create commands for current list
     * 
     * @return array
     */
    protected function _getCommandsCreate()
    {
        $commands = array();
        if(($commandsSet = $this->_getCommandsSet())) {
            $commands[] = "domain add {$this->_getEscapedDomain()}";
            $commands[] = "list add {$this->_getEscapedEmail()}";
            
            $commands = array_merge($commands, $commandsSet);
        }
        
        return $commands;
    }
    
    /**
     * get delete commands for current list
     * 
     * @return array
     */
    protected function _getCommandsDelete()
    {
        $commands[] = "list del {$this->_getEscapedEmail()}";
        return $commands;
    }
    
    /**
     * get disable commands for current list
     * 
     * @return array
     */
    protected function _getCommandsDisable()
    {
        $commands[] = "list sub del {$this->_getEscapedEmail()} .";
        return $commands;
    }
    
    /**
     * get deactivate commands for current robot
     * 
     * @return array
     */
    protected function _getCommandsSet()
    {
        $commands[] = "list sub del {$this->_getEscapedEmail()} .";
        
        if(($subs = $this->_getEscapedSubscribers())) {
            $commands[] = "list sub add {$this->_getEscapedEmail()} {$subs}";
        }
        
        return $commands;
    }
    
    /**
     * get escaped domain from current list
     * 
     * @return string
     */
    protected function _getEscapedDomain()
    {
        return escapeshellarg($this->_getList()->getData('domainName'));
    }
    
    /**
     * get escaped email from current list
     * 
     * @return string
     */
    protected function _getEscapedEmail()
    {
        return escapeshellarg($this->_getList()->getEmail());
    }
    
    /**
     * get escaped subscribers from current list
     * 
     * @return string|null
     */
    protected function _getEscapedSubscribers()
    {
        if(!($subs = $this->_getList()->getSubscribers())) {
            return null;
        }
        
        $subsString = '';
        foreach($subs as $sub) {
            $subsString .= $sub . " ";
        }
        
        return $this->_escapeshellargSpecial($subsString);
    }
    
    /**
     * gets current list instance
     * 
     * @return MazelabVpopqmail_Model_ValueObject_MailingList
     */
    protected function _getList()
    {
        return $this->_list;
    }

    /**
     * apply given list instance
     * 
     * @param MazelabVpopqmail_Model_ValueObject_MailingList $list
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply(MazelabVpopqmail_Model_ValueObject_MailingList $list, $save = true)
    {
        $this->_list = $list;
        if(!($node = $this->_getList()->getNode())) {
            return false;
        }
        
        if(!($commands = $this->_getCommands())) {
            return true;
        }
        
        $key = "mailing list {$list->getId()}";
        if(($result = $node->getCommands()->addContextCommands(self::MODULE_NAME, $key, $commands)) && $save) {
            return $node->getCommands()->save();
        }
        
        return $result;
    }
    
    /**
     * remove commands from current node
     * 
     * @param $list MazelabVpopqmail_Model_ValueObject_MailingList
     * @return boolean
     */
    public function remove(MazelabVpopqmail_Model_ValueObject_MailingList $list)
    {
        $this->_list = $list;
        if(!($node = $list->getNode()) || !($commands = $node->getCommands())) {
            return true;
        }
        
        $key = "mailing list {$list->getId()}";
        if(!$commands->addContextCommands(self::MODULE_NAME, $key, array())) {
            return false;
        }
        
        return $commands->save();
    }
    
}
