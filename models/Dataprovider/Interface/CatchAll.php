<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

interface MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll
{

    /**
     * deletes a certain catch-all
     * 
     * @param MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll
     * @return boolean
     */
    public function deleteCatchAll(MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll);

    /**
     * returns content of catch-all from given id
     * 
     * @param string $id
     * @return array
     */
    public function getCatchAll($id);
    
    /**
     * returns content of catch from a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getCatchAllByDomain($domainId);
    
    /**
     * returns content of catch from a certain domain name
     * 
     * @param string $domainName
     * @return array
     */
    public function getCatchAllByDomainName($domainName);
    
    /**
     * gets all catch alls
     * 
     * @return array
     */
    public function getCatchAlls();

    /**
     * saves given data set to a certain catch-all
     * 
     * if id is not provided, it will create a new item
     * 
     * @param string $catchAllId
     * @param array $data
     * @return string|false id of saved item
     */
    public function saveCatchAll(array $data, $catchAllId = null);

}
