<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Apply_Robot
    extends MazelabVpopqmail_Model_Apply_Commands
{
    
    /**
     * @var MazelabVpopqmail_Model_ValueObject_MailRobot
     */
    protected $_robot;

    /**
     * gets commands in order to achieve desired state
     * 
     * @return array|null
     */
    protected function _getCommands()
    {
        if($this->_getRobot()->getData('delete') === true) {
            return $this->_getCommandsDelete();
        }
        
        if(!$this->_getRobot()->getConflicts()) {
            return null;
        }
        
        if(!$this->_getRobot()->getStatus() && $this->_getRobot()->getBean()->hasConflict('status')) {
            return $this->_getCommandsDelete();
        } elseif ($this->_getRobot()->getBean()->hasConflict('status')) {
            return $this->_getCommandsCreate();
        }
        
        return $this->_getCommandsSet();
    }
    
    /**
     * get create commands for current robot
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
     * get delete commands for current robot
     * 
     * @return array
     */
    protected function _getCommandsDelete()
    {
        $commands[] = "robot del {$this->_getEscapedEmail()}";
        return $commands;
    }
    
    /**
     * get deactivate commands for current robot
     * 
     * @return array
     */
    protected function _getCommandsSet()
    {
        $commands[] = "robot add {$this->_getEscapedEmail()} {$this->_getEscapedContent()} {$this->_getEscapedCopyTo()}";
        
        return $commands;
    }
    
    /**
     * get escaped content from current robot
     * 
     * @return string
     */
    protected function _getEscapedContent()
    {
        return $this->_escapeshellargSpecial($this->_getRobot()->getData('content'));
    }
    
    /**
     * get escaped copy to from current robot
     * 
     * @return string|null
     */
    protected function _getEscapedCopyTo()
    {
        if(!($copyTo = $this->_getRobot()->getData('copyTo'))) {
            return null;
        }
        
        return $this->_escapeshellargSpecial($copyTo);
    }
    
    /**
     * get escaped domain from current robot
     * 
     * @return string
     */
    protected function _getEscapedDomain()
    {
        return escapeshellarg($this->_getRobot()->getData('domainName'));
    }
    
    /**
     * get escaped email from current robot
     * 
     * @return string
     */
    protected function _getEscapedEmail()
    {
        return escapeshellarg($this->_getRobot()->getEmail());
    }
    
    /**
     * gets current robot instance
     * 
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot
     */
    protected function _getRobot()
    {
        return $this->_robot;
    }

    /**
     * apply given robot instance
     * 
     * @param MazelabVpopqmail_Model_ValueObject_MailRobot $robot
     * @param boolean $save (default true) save or only set commands
     * @return boolean
     */
    public function apply(MazelabVpopqmail_Model_ValueObject_MailRobot $robot, $save = true)
    {
        $this->_robot = $robot;
        if(!($node = $this->_getRobot()->getNode())) {
            return false;
        }
        
        if(!($commands = $this->_getCommands())) {
            return true;
        }
        
        $key = "mail robot {$robot->getId()}";
        if(($result = $node->getCommands()->addContextCommands(self::MODULE_NAME, $key, $commands)) && $save) {
            return $node->getCommands()->save();
        }
        
        return $result;
    }
    
    /**
     * remove commands from current node
     * 
     * @param $robot MazelabVpopqmail_Model_ValueObject_MailRobot
     * @return boolean
     */
    public function remove(MazelabVpopqmail_Model_ValueObject_MailRobot $robot)
    {
        $this->_robot = $robot;
        if(!($node = $this->_getRobot()->getNode()) || !($commands = $node->getCommands())) {
            return true;
        }
        
        $key = "mail robot {$robot->getId()}";
        if(!$commands->addContextCommands(self::MODULE_NAME, $key, array())) {
            return false;
        }
        
        return $commands->save();
    }
    
}
