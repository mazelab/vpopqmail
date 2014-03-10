<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Demo_Data
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase
{

    /**
     * array key for type
     */
    CONST KEY_TYPE = 'type';
    
    /**
     * value for account type
     */
    CONST KEY_TYPE_ACCOUNT = 'mailAccount';
    
    /**
     * value for forwarder type
     */
    CONST KEY_TYPE_FORWARDER = 'forwarder';
    
    /**
     * create rnd id
     * 
     * @param int $max
     * @return int
     */
    protected function _getRndId($max)
    {
        return rand(1, $max);
    }

}
