<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Bean_MailingList extends MazeLib_Bean
{
    
    protected $mapping = array(
        'status' => MazeLib_Bean::STATUS_PRIO_MAZE,
        'subscriber/*' => MazeLib_Bean::STATUS_PRIO_MAZE
    );
    
}

