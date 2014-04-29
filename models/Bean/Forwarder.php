<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Bean_Forwarder extends MazeLib_Bean
{
    
    protected $mapping = array(
        'status' => MazeLib_Bean::STATUS_PRIO_MAZE,
        'forwardTo/*' => MazeLib_Bean::STATUS_MANUALLY
    );
    
    protected function _mergeMazeProperty($path, $orig, $update, $remote = false)
    {
        $property = parent::_mergeMazeProperty($path, $orig, $update, $remote);

        if(!array_key_exists("create", $property)) {
            $property["create"] = time();
        }
        
        return $property;
    }
}
