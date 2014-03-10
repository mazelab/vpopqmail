<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_ValueObject extends Core_Model_ValueObject
{

    /**
     * escape shell arguments without stripping non-ASCII characters
     * 
     * @param string $str
     * @return string
     */
    protected function _escapeshellargSpecial($str) {
      return "'" . str_replace("'", "'\"'\"'", $str) . "'";
    }
    
}
