<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Demo_Pager_Specials
    extends MazelabVpopqmail_Model_Dataprovider_Demo_Data
    implements Core_Model_Dataprovider_Interface_Search
{
    
    /**
     * gets current data set with index
     * 
     * @param string $index
     * @param int $limit
     * @param string $searchTerm
     * @return array
     */
    public function current($index, $limit = null, $searchTerm = null)
    {
        return array();
    }
    
    /**
     * returns total data set from data backend
     * 
     * @param string $searchTerm
     * @return int
     */
    public function total($searchTerm = null)
    {
        return 0;
    }
    
    /*
     * loads pager position from data backend
     * 
     * @param string $index
     * @param string $searchTerm
     * @return int
     */
    public function position($index = null, $searchTerm = null)
    {
        return 0;
    }
    
    /**
     * gets first data set with limit
     * 
     * @param int $limit
     * @param string $searchTerm
     * @return array
     */
    public function first($limit = null, $searchTerm = null)
    {
        return array();
    }
    
    /**
     * gets last data set with limit
     * 
     * @param int $limit
     * @param string $searchTerm
     * @return array
     */
    public function last($limit, $searchTerm = null)
    {
        return array();
    }
    
    /**
     * gets next data set with limit and index
     * 
     * @param string $index
     * @param int $limit
     * @param string $searchTerm
     * @return array
     */
    public function next($index, $limit, $searchTerm = null)
    {
        return array();
    }
    
    /**
     * gets previous data set with limit and index
     * 
     * @param string $index
     * @param int $limit
     * @param string $searchTerm
     * @return array
     */
    public function previous($index, $limit, $searchTerm = null)
    {
        return array();
    }
    
}

