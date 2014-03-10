<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Bean_Account extends MazeLib_Bean
{
    
    protected $mapping = array(
        'quota' => MazeLib_Bean::STATUS_MANUALLY,
        'password' => MazeLib_Bean::STATUS_PRIO_MAZE,
        'status' => MazeLib_Bean::STATUS_PRIO_MAZE
    );
    
}

