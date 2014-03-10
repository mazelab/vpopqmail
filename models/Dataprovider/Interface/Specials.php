<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

interface MazelabVpopqmail_Model_Dataprovider_Interface_Specials
{
    
    /**
     * gets a certain special without typification
     * 
     * @param string $specialId
     * @return array
     */
    public function getSpecial($specialId);
    
    /**
     * gets a certain special without typification by email
     * 
     * @param string $email
     * @return null|array
     */
    public function getSpecialByEmail($email);
    
    /**
     * returns all specials of a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getSpecialsByDomain($domainId);
    
}
