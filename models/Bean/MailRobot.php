<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Bean_MailRobot extends MazeLib_Bean
{
    
    protected $mapping = array(
        'content' => MazeLib_Bean::STATUS_PRIO_MAZE,
        'copyTo' => MazeLib_Bean::STATUS_PRIO_MAZE,
        'status' => MazeLib_Bean::STATUS_PRIO_MAZE
    );
    
}

