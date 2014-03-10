<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_Core_Data
{

    /**
     * @var MongoDb_Mongo
     */
    protected $_mongoDb;

    /**
     * @var MongoCollection
     */
    protected $_vpopqmailCollection;
    
    /**
     * name of the used collection
     */
    CONST COLLECTION = 'vpopqmail';

    /**
     * index key for domain id
     */
    CONST KEY_DOMAINID = 'domainId';
    
    /**
     * index key for domain name
     */
    CONST KEY_DOMAINNAME = 'domainName';
    
    /**
     * index key for forwarder targets
     */
    CONST KEY_FORWARDTO = 'forwardTo';
    
    /**
     * index key for id
     */
    CONST KEY_ID = '_id';
    
    /**
     * index key for label
     */
    CONST KEY_LABEL = 'label';
    
    /**
     * index key for mailing list subscribers
     */
    CONST KEY_MAIL_TO = 'subscriber';
    
    /**
     * index key for owner id
     */
    CONST KEY_OWNERID = 'ownerId';
    
    /**
     * index key for pager index
     */
    CONST KEY_PAGER_INDEX = 'pagerIndex';
    
    /**
     * index key for type
     */
    CONST KEY_TYPE = 'type';

    /**
     * value for type account
     */
    CONST KEY_TYPE_ACCOUNT = 'mailAccount';
    
    /**
     * value for type catch all
     */
    CONST KEY_TYPE_CATCHALL = MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL;
    
    /**
     * value for type mail robot
     */
    CONST KEY_TYPE_ROBOT = MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT;
    
    /**
     * value for type mailing list
     */
    CONST KEY_TYPE_LIST = MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST;
    
    /**
     * value for type forwarder
     */
    CONST KEY_TYPE_FORWARDER = 'forwarder';
    
    /**
     * index key for user
     */
    CONST KEY_USER = 'user';
    
    /**
     * init mongo db
     */
    public function __construct()
    {
        $this->_mongoDb = Core_Model_DiFactory::getMongoDb();
    }
    
    /**
     * @return MongoDb_Mongo
     */
    protected function _getDatabase()
    {
        return $this->_mongoDb;
    }

    /**
     * @return MongoCollection
     */
    protected function _getCollection()
    {
        if (!$this->_vpopqmailCollection instanceof MongoCollection) {
            $this->_vpopqmailCollection = $this->_getDatabase()->getCollection(self::COLLECTION);
        }

        return $this->_vpopqmailCollection;
    }

}
