<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_Collection
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_Collection
{
    /**
     * drops the module collection
     * 
     * @return boolean
     */
    public function drop()
    {
        if (($result = $this->_getCollection()->drop()) && $result["ok"] == 1) {
            return true;
        }

        return false;
    }
}