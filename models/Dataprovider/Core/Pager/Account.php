<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_Pager_Account
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
     * @var MongoCollection
     */
    protected $_emailCollection;

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
            self::KEY_TYPE => 1
        ));
        
        $this->_getCollection()->ensureIndex(array(
            self::KEY_TYPE => 1,
            self::KEY_OWNERID => 1
        ));
        
        $this->_getCollection()->ensureIndex(array(
            self::KEY_TYPE => 1,
            self::KEY_OWNERID => 1,
            self::KEY_DOMAINID => 1
        ));

        $this->_ownerId = $ownerId;
        $this->_domainId = $domainId;
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
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT
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
        
        foreach($mongoCursor->sort($sort)->limit($limit) as $accountId => $account) {
            $account[self::KEY_ID] = $accountId;
            $result['data'][$accountId] = $account;
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
            self::KEY_TYPE => self::KEY_TYPE_ACCOUNT
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
        foreach($mongoCursor->sort($sort)->skip($skip)->limit($limit) as $accountId => $account) {
            $account[self::KEY_ID] = $accountId;
            $result['data'][$accountId] = $account;
        }
        
        return $result;
    }
    
}

