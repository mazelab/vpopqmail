<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Apply_Commands
{

    CONST MODULE_NAME = MazelabVpopqmail_Model_ConfigManager::MODULE_NAME;
    
    /**
     * non-ASCII-character safe replacement of escapeshellarg()
     * 
     * @param string $str
     * @return string
     */
    protected function _escapeshellargSpecial($str) {
        return "'" . str_replace("'", "'\"'\"'", $str) . "'";
    }
    
}
