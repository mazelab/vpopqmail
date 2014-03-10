<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Demo_Collection
    extends MazelabVpopqmail_Model_Dataprovider_Demo_Data
    implements MazelabVpopqmail_Model_Dataprovider_Interface_Collection
{
    /**
     * drops the module collection
     * 
     * @return boolean
     */
    public function drop()
    {
        $this->_setCollection(self::COLLECTION, null);
        
        return empty($this->_getCollection(self::COLLECTION));
    }
}