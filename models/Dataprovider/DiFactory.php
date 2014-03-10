<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Dataprovider_DiFactory
{

    /**
     * @var MazelabVpopqmail_Model_Dataprovider_Interface_Account
     */
    static protected $_account;

    /**
     * current adapter for data provider
     * 
     * @var string
     */
    static protected $_adapter;
    
    /**
     * @var MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll
     */
    static protected $_catchAll;

    /**
     * @var MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
     */
    static protected $_forwarder;
    
    /**
     * @var MazelabVpopqmail_Model_Dataprovider_Interface_MailingList
     */
    static protected $_mailingList;
    
    /**
     * @var MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot
     */
    static protected $_mailRobot;
    
    /**
     * @var MazelabVpopqmail_Model_Dataprovider_Interface_Specials
     */
    static protected $_specials;
    
    /**
     * @var MazelabVpopqmail_Model_Dataprovider_Interface_Collection
     */
    static protected $_collection;

    /**
     * default adapter for class building
     */
    CONST DEFAULT_ADAPTER = 'Core';
    
    /**
     * class prefix for provider class building
     */
    CONST PROVIDER_CLASS_PATH_PRE = 'MazelabVpopqmail_Model_Dataprovider_';
    
    /**
     * get account instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Account
     */
    static public function getAccount()
    {
        if (!self::$_account instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Account) {
            self::$_account = self::newAccount();
        }

        return self::$_account;
    }
    
    /**
     * returns the current adapter
     * 
     * @return string
     */
    static public function getAdapter()
    {
        if (is_null(self::$_adapter)) {
            self::setAdapter(self::DEFAULT_ADAPTER);
        }

        return self::$_adapter;
    }
    
    /**
     * get catchall instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll
     */
    static public function getCatchAll()
    {
        if (!self::$_catchAll instanceof MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll) {
            self::$_catchAll = self::newCatchAll();
        }

        return self::$_catchAll;
    }
    
    /**
     * get forwarder instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
     */
    static public function getForwarder()
    {
        if (!self::$_forwarder instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder) {
            self::$_forwarder = self::newForwarder();
        }

        return self::$_forwarder;
    }
    
    /**
     * get mail robot instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot
     */
    static public function getMailRobot()
    {
        if (!self::$_mailRobot instanceof MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot) {
            self::$_mailRobot = self::newMailRobot();
        }

        return self::$_mailRobot;
    }
    
    /**
     * get mailing list instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailingList
     */
    static public function getMailingList()
    {
        if (!self::$_mailingList instanceof MazelabVpopqmail_Model_Dataprovider_Interface_MailingList) {
            self::$_mailingList = self::newMailingList();
        }

        return self::$_mailingList;
    }
    
    /**
     * get specials instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Specials
     */
    static public function getSpecials()
    {
        if (!self::$_specials instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Specials) {
            self::$_specials = self::newSpecials();
        }

        return self::$_specials;
    }
    
    /**
     * get collection instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Collection
     */
    static public function getCollection()
    {
        if (!self::$_collection instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Collection) {
            self::$_collection = self::newCollection();
        }

        return self::$_collection;
    }
    
    /**
     * create account instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Account
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newAccount()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Account';

        $newOne = new $className();
        if ($newOne instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Account) {
            return $newOne;
        }

        throw new MazelabVpopqmail_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid account implementation.'
        );
    }
    
    /**
     * create account pager instance
     * 
     * @param string $owner
     * @param string $domainId
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newAccountPager($ownerId = null, $domainId = null)
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Pager_Account';

        $newOne = new $className($ownerId, $domainId);
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid pager account implementation.'
        );
    }
    
    /**
     * creates catch all instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newCatchAll()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_CatchAll';

        $newOne = new $className();
        if ($newOne instanceof MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll) {
            return $newOne;
        }

        throw new MazelabVpopqmail_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid catch-all implementation.'
        );
    }
    
    /**
     * creates forwarder instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newForwarder()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Forwarder';

        $newOne = new $className();
        if ($newOne instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder) {
            return $newOne;
        }

        throw new MazelabVpopqmail_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid forwarder implementation.'
        );
    }
    
    /**
     * creates forwarder pager instance
     * 
     * @param string $ownerId
     * @param string $domainId
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newForwarderPager($ownerId = null, $domainId = null)
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Pager_Forwarder';

        $newOne = new $className($ownerId, $domainId);
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid pager forwarder implementation.'
        );
    }
    
    /**
     * create mail robot instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newMailRobot()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_MailRobot';

        $newOne = new $className();
        if ($newOne instanceof MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot) {
            return $newOne;
        }

        throw new MazelabVpopqmail_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid mail robot implementation.'
        );
    }
    
    /**
     * creates mailing list instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_MailingList
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newMailingList()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_MailingList';

        $newOne = new $className();
        if ($newOne instanceof MazelabVpopqmail_Model_Dataprovider_Interface_MailingList) {
            return $newOne;
        }

        throw new MazelabVpopqmail_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid mailing list implementation.'
        );
    }
    
    /**
     * create specials instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Specials
     * @throws MazelabVpopqmail_Model_Dataprovider_Exception
     */
    static public function newSpecials()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Specials';

        $newOne = new $className();
        if ($newOne instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Specials) {
            return $newOne;
        }

        throw new MazelabVpopqmail_Model_Dataprovider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid specials implementation.'
        );
    }
    
    /**
     * create specials pager instance
     * 
     * @param string $domainId
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws MazelabVpopqmail_Model_DataProvider_Exception
     */
    static public function newSpecialsPager($ownerId = null, $domainId = null)
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Pager_Specials';

        $newOne = new $className($ownerId, $domainId);
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid pager specials implementation.'
        );
    }
    
    /**
     * create collection instance
     * 
     * @return MazelabVpopqmail_Model_Dataprovider_Interface_Collection
     * @throws MazelabVpopqmail_Model_Dataprovider_Exception
     */
    static public function newCollection()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Collection';

        $newOne = new $className();
        if ($newOne instanceof MazelabVpopqmail_Model_Dataprovider_Interface_Collection) {
            return $newOne;
        }

        throw new MazelabVpopqmail_Model_Dataprovider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid specials implementation.'
        );
    }
    
    /**
     * resets instance and allready builded objects
     */
    static public function reset()
    {
        self::setAdapter();
        self::setAccount();
        self::setCatchAll();
        self::setForwarder();
        self::getMailRobot();
        self::setMailingList();
        self::setSpecials();
    }
    
    /**
     * set account instance
     * 
     * if null is given it will reset the instance
     * 
     * @param MazelabVpopqmail_Model_Dataprovider_Interface_Account $account
     */
    static public function setAccount(MazelabVpopqmail_Model_Dataprovider_Interface_Account $account = null)
    {
        self::$_account = $account;
    }
    
    /**
     * sets adapter for the dataprovider
     * 
     * if no adapter is given it will reset the current adapter to default
     * 
     * @param string $adapter
     */
    static public function setAdapter($adapter = null)
    {
        if (self::$_adapter) {
            self::reset();
        }

        self::$_adapter = $adapter;
    }
    
    /**
     * set forwarder instance
     * 
     * if null is given it will reset the instance
     * 
     * @param MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder $forwarder
     */
    static public function setForwarder(MazelabVpopqmail_Model_Dataprovider_Interface_Forwarder $forwarder = null)
    {
        self::$_forwarder = $forwarder;
    }
    
    /**
     * set catchall instance
     * 
     * if null is given it will reset the instance
     * 
     * @param MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll $catchAll
     */
    static public function setCatchAll(MazelabVpopqmail_Model_Dataprovider_Interface_CatchAll $catchAll = null)
    {
        self::$_catchAll = $catchAll;
    }
    
    /**
     * set robot instance
     * 
     * if null is given it will reset the instance
     * 
     * @param MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot $mailRobot
     */
    static public function setMailRobot(MazelabVpopqmail_Model_Dataprovider_Interface_MailRobot $mailRobot = null)
    {
        self::$_mailRobot = $mailRobot;
    }
    
    /**
     * set mailing list instance
     * 
     * if null is given it will reset the instance
     * 
     * @param MazelabVpopqmail_Model_Dataprovider_Interface_MailingList $mailingList
     */
    static public function setMailingList(MazelabVpopqmail_Model_Dataprovider_Interface_MailingList $mailingList = null)
    {
        self::$_mailingList = $mailingList;
    }
    
    /**
     * set specials instance
     * 
     * if null is given it will reset the instance
     * 
     * @param MazelabVpopqmail_Model_Dataprovider_Interface_Specials $specials
     */
    static public function setSpecials(MazelabVpopqmail_Model_Dataprovider_Interface_Specials $specials = null)
    {
        self::$_specials = $specials;
    }
    
}
