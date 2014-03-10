<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_Pager_Specials 
    extends MazelabVpopqmail_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Search
{
    
    /**
     * @var string
     */
    protected $_domainId;
    
    /**
     * @var string
     */
    protected $_ownerId;
    
    /**
     * sets pager dependencies
     * 
     * @param string $ownerId
     * @param string $domainId
     */
    public function __construct($ownerId = null, $domainId = null)
    {
        parent::__construct();
        
        $this->_getCollection()->ensureIndex(array(
            self::KEY_OWNERID => 1
        ));
        
        $this->_getCollection()->ensureIndex(array(
            self::KEY_DOMAINID => 1
        ));
        
        $this->_domainId = $domainId;
        $this->_ownerId = $ownerId;
    }
    
    /**
     * gets last data set with limit
     * 
     * return should be build like:
     * array(
     *  'data' => array(),
     *  'total' => '55'
     * )
     * 
     * @param int $limit
     * @param string $searchTerm
     * @return array
     */
    public function last($limit, $searchTerm = null)
    {
        $result = array();
        $query = array(
            '$or' => array(
                array(self::KEY_TYPE => self::KEY_TYPE_CATCHALL),
                array(self::KEY_TYPE => self::KEY_TYPE_LIST),
                array(self::KEY_TYPE => self::KEY_TYPE_ROBOT),
            )
        );
        
        if ($searchTerm) {
            $query[self::KEY_LABEL] = new MongoRegex("/$searchTerm/i");
        }
        
        if($this->_ownerId) {
            $query[self::KEY_OWNERID] = $this->_ownerId;
        }
        
        if($this->_domainId) {
           $query[self::KEY_DOMAINID] = $this->_domainId;
        }
        
        $sort = array(
            self::KEY_DOMAINNAME => -1, self::KEY_ID => 1
        );
        
        $mongoCursor = $this->_getCollection()->find($query);
        $result['total'] = $total = $mongoCursor->count();
        if($total > $limit) {
            $rest = ($total / $limit) - floor($total / $limit);
            if($rest != 0) {
                $limit = bcmul($rest, $limit);
            }
        }
        
        foreach($mongoCursor->sort($sort)->limit($limit) as $specialId => $special) {
            $special[self::KEY_ID] = $specialId;
            $result['data'][$specialId] = $special;
        }
        
        $result['data'] = array_reverse($result['data']);
        
        return $result;
    }

    /**
     * gets a certain page
     * 
     * return should be build like:
     * array(
     *  'data' => array(),
     *  'total' => '55'
     * )
     * 
     * @param int $limit
     * @param int $page
     * @param string $searchTerm
     * @return array
     */
    public function page($limit, $page, $searchTerm = null)
    {
        $result = array();
        $query = array(
            '$or' => array(
                array(self::KEY_TYPE => self::KEY_TYPE_CATCHALL),
                array(self::KEY_TYPE => self::KEY_TYPE_LIST),
                array(self::KEY_TYPE => self::KEY_TYPE_ROBOT),
            )
        );
        
        if ($searchTerm) {
            $query[self::KEY_LABEL] = new MongoRegex("/$searchTerm/i");
        }
        
        if($this->_ownerId) {
            $query[self::KEY_OWNERID] = $this->_ownerId;
        }
        
        if($this->_domainId) {
           $query[self::KEY_DOMAINID] = $this->_domainId;
        }
        
        $sort = array(
            self::KEY_DOMAINNAME => 1, self::KEY_ID => -1
        );
        
        $mongoCursor = $this->_getCollection()->find($query);
        $result['total'] = $mongoCursor->count();
        
        $skip = ($limit * $page) - $limit;
        foreach($mongoCursor->sort($sort)->skip($skip)->limit($limit) as $specialId => $special) {
            $special[self::KEY_ID] = $specialId;
            $result['data'][$specialId] = $special;
        }
        
        return $result;
    }
}

