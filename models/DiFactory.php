<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_DiFactory
{

    /**
     * @var array MazelabVpopqmail_Model_ValueObject_Account
     */
    static protected $_account;
    
    /**
     * @var MazelabVpopqmail_Model_AccountManager
     */
    static protected $_accountManager;
    
    /**
     * @var MazelabVpopqmail_Model_Apply_Account
     */
    static protected $_applyAccount;
    
    /**
     * @var MazelabVpopqmail_Model_Apply_CatchAll
     */
    static protected $_applyCatchAll;
    
    /**
     * @var MazelabVpopqmail_Model_Apply_Forwarder
     */
    static protected $_applyForwarder;
    
    /**
     * @var MazelabVpopqmail_Model_Apply_List
     */
    static protected $_applyList;
    
    /**
     * @var MazelabVpopqmail_Model_Apply_Robot
     */
    static protected $_applyRobot;
    
    /**
     * @var array contains MazelabVpopqmail_Model_ValueObject_CatchAll
     */
    static protected $_catchAll;
    
    /**
     * @var MazelabVpopqmail_Model_CatchAllManager
     */
    static protected $_catchAllManager;
    
    /**
     * @var MazelabVpopqmail_Model_ClientManager
     */
    static protected $_clientManager;
    
    /**
     * @var MazelabVpopqmail_Model_ConfigManager
     */
    static protected $_configManager;
    
    /**
     * @var MazelabVpopqmail_Model_DomainManager
     */
    static protected $_domainManager;
    
    /**
     * @var Nginx_Model_IndexManager
     */
    static protected $_indexManager;
    
    /**
     * @var array MazelabVpopqmail_Model_ValueObject_Forwarder
     */
    static protected $_forwarder;
    
    /**
     * @var MazelabVpopqmail_Model_ForwarderManager
     */
    static protected $_forwarderManager;
    
    /**
     * @var array MazelabVpopqmail_Model_ValueObject_MailRobot
     */
    static protected $_mailRobot;

    /**
     * @var MazelabVpopqmail_Model_MailRobotManager
     */
    static protected $_mailRobotManager;
    
    /**
     * @var array MazelabVpopqmail_Model_ValueObject_MailingList
     */
    static protected $_mailingList;
    
    /**
     * @var MazelabVpopqmail_Model_MailingListManager
     */
    static protected $_mailingListManager;
    
    /**
     * @var array
     */
    static protected $_mapAccountEmail;
    
    /**
     * @var array
     */
    static protected $_mapCatchAllDomain;
    
    /**
     * @var array
     */
    static protected $_mapCatchAllDomainName;
    
    /**
     * @var array
     */
    static protected $_mapForwarderEmail;
    
    /**
     * @var array
     */
    static protected $_mapMailRobotEmail;
    
    /**
     * @var array
     */
    static protected $_mapMailingListEmail;
    
    /**
     * @var MazelabVpopqmail_Model_NodeManager
     */
    static protected $_nodeManager;
    
    /**
     * @var MazelabVpopqmail_Model_SpecialsManager
     */
    static protected $_specialsManager;
    
    /**
     * @var MazelabVpopqmail_Model_ReportManager
     */
    static protected $_reportManager;    
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_Account
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Model_ValueObject_Account|null
     */
    static public function getAccount($accountId)
    {
        if(!isset(self::$_account[$accountId])) {
            return null;
        }

        return self::$_account[$accountId];
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_Account by email
     * 
     * uses _mapForwarderEmail
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_ValueObject_Account|null
     */
    static public function getAccountByEmail($email)
    {
        if(isset(self::$_mapAccountEmail[$email])) {
            return self::getAccount(self::$_mapAccountEmail[$email]);
        }
        
        return null;
    }
    
    /**
     * @return MazelabVpopqmail_Model_AccountManager
     */
    static public function getAccountManager()
    {
        if (!self::$_accountManager instanceof MazelabVpopqmail_Model_AccountManager) {
            self::$_accountManager = self::newAccountManager();
        }

        return self::$_accountManager;
    }
    
    /**
     * @return MazelabVpopqmail_Model_Apply_Account
     */
    static public function getApplyAccount()
    {
        if (!self::$_applyAccount instanceof MazelabVpopqmail_Model_Apply_Account) {
            self::$_applyAccount = self::newApplyAccount();
        }

        return self::$_applyAccount;
    }
    
    /**
     * @return MazelabVpopqmail_Model_Apply_CatchAll
     */
    static public function getApplyCatchAll()
    {
        if (!self::$_applyCatchAll instanceof MazelabVpopqmail_Model_Apply_CatchAll) {
            self::$_applyCatchAll = self::newApplyCatchAll();
        }

        return self::$_applyCatchAll;
    }
    
    /**
     * @return MazelabVpopqmail_Model_Apply_List
     */
    static public function getApplyList()
    {
        if (!self::$_applyList instanceof MazelabVpopqmail_Model_Apply_List) {
            self::$_applyList = self::newApplyList();
        }

        return self::$_applyList;
    }
    
    /**
     * @return MazelabVpopqmail_Model_Apply_Robot
     */
    static public function getApplyRobot()
    {
        if (!self::$_applyRobot instanceof MazelabVpopqmail_Model_Apply_Robot) {
            self::$_applyRobot = self::newApplyRobot();
        }

        return self::$_applyRobot;
    }
    
    /**
     * @return MazelabVpopqmail_Model_Apply_Forwarder
     */
    static public function getApplyForwarder()
    {
        if (!self::$_applyForwarder instanceof MazelabVpopqmail_Model_Apply_Forwarder) {
            self::$_applyForwarder = self::newApplyForwarder();
        }

        return self::$_applyForwarder;
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_CatchAll
     * 
     * @param string $catchAllId
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll
     */
    static public function getCatchAll($catchAllId)
    {
        if(!isset(self::$_catchAll[$catchAllId])) {
            return null;
        }

        return self::$_catchAll[$catchAllId];
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_CatchAll by domain
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll
     */
    static public function getCatchAllByDomain($domainId)
    {
        if(isset(self::$_mapCatchAllDomain[$domainId])) {
            return self::getCatchAll(self::$_mapCatchAllDomain[$domainId]);
        }
        
        return null;
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_CatchAll by domain name
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll
     */
    static public function getCatchAllByDomainName($domainId)
    {
        if(isset(self::$_mapCatchAllDomainName[$domainId])) {
            return self::getCatchAll(self::$_mapCatchAllDomainName[$domainId]);
        }
        
        return null;
    }
    
    /**
     * @return MazelabVpopqmail_Model_CatchAllManager
     */
    static public function getCatchAllManager()
    {
        if (!self::$_catchAllManager instanceof MazelabVpopqmail_Model_CatchAllManager) {
            self::$_catchAllManager = self::newCatchAllManager();
        }

        return self::$_catchAllManager;
    }
    
    /**
     * get client manager instance
     * 
     * @return MazelabVpopqmail_Model_ClientManager
     */
    static public function getClientManager()
    {
        if (!self::$_clientManager instanceof MazelabVpopqmail_Model_ClientManager) {
            self::$_clientManager = self::newClientManager();
        }

        return self::$_clientManager;
    }
    
    /**
     * @return MazelabVpopqmail_Model_ConfigManager
     */
    static public function getConfigManager()
    {
        if (!self::$_configManager instanceof MazelabVpopqmail_Model_ConfigManager) {
            self::$_configManager = self::newConfigManager();
        }

        return self::$_configManager;
    }
    
    /**
     * @return MazelabVpopqmail_Model_DomainManager
     */
    static public function getDomainManager()
    {
        if (!self::$_domainManager instanceof MazelabVpopqmail_Model_DomainManager) {
            self::$_domainManager = self::newDomainManager();
        }

        return self::$_domainManager;
    }
    
    /**
     * get actual instance of MazelabVpopqmail_Model_IndexManager
     * 
     * @return MazelabVpopqmail_Model_IndexManager
     */
    static public function getIndexManager()
    {
        if (!self::$_indexManager instanceof MazelabVpopqmail_Model_IndexManager) {
            self::$_indexManager = self::newIndexManager();
        }

        return self::$_indexManager;
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_Forwarder
     * 
     * @param string $forwarderId
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder|null
     */
    static public function getForwarder($forwarderId)
    {
        if(!isset(self::$_forwarder[$forwarderId])) {
            return null;
        }

        return self::$_forwarder[$forwarderId];
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_Forwarder by email
     * 
     * uses _mapForwarderEmail
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder|null
     */
    static public function getForwarderByEmail($email)
    {
        if(isset(self::$_mapForwarderEmail[$email])) {
            return self::getForwarder(self::$_mapForwarderEmail[$email]);
        }
        
        return null;
    }
    
    /**
     * @return MazelabVpopqmail_Model_ForwarderManager
     */
    static public function getForwarderManager()
    {
        if (!self::$_forwarderManager instanceof MazelabVpopqmail_Model_ForwarderManager) {
            self::$_forwarderManager = self::newForwarderManager();
        }

        return self::$_forwarderManager;
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_MailRobot
     * 
     * @param string $mailRobotId
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot|null
     */
    static public function getMailRobot($mailRobotId)
    {
        if(!isset(self::$_mailRobot[$mailRobotId])) {
            return null;
        }
        
        return self::$_mailRobot[$mailRobotId];
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_MailRobot by email
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot|null
     */
    static public function getMailRobotByEmail($email)
    {
        if(!isset(self::$_mapMailRobotEmail[$email])) {
            return null;
        }

        return self::$_mailRobot[self::$_mapMailRobotEmail[$email]];
    }
    
    /**
     * @return MazelabVpopqmail_Model_MailRobotManager
     */
    static public function getMailRobotManager()
    {
        if (!self::$_mailRobotManager instanceof MazelabVpopqmail_Model_MailRobotManager) {
            self::$_mailRobotManager = self::newMailRobotManager();
        }

        return self::$_mailRobotManager;
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_MailingList
     * 
     * @param string $mailingListId
     * @return MazelabVpopqmail_Model_ValueObject_MailingList|null
     */
    static public function getMailingList($mailingListId)
    {
        if(!isset(self::$_mailingList[$mailingListId])) {
            return null;
        }

        return self::$_mailingList[$mailingListId];
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_MailingList by email
     * 
     * @param string $email
     * @return MazelabVpopqmail_Model_ValueObject_MailingList|null
     */
    static public function getMailingListByEmail($email)
    {
        if(!isset(self::$_mapMailingListEmail[$email])) {
            return null;
        }

        return self::$_mailingList[self::$_mapMailingListEmail[$email]];
    }
    
    /**
     * @return MazelabVpopqmail_Model_MailingListManager
     */
    static public function getMailingListManager()
    {
        if (!self::$_mailingListManager instanceof MazelabVpopqmail_Model_MailingListManager) {
            self::$_mailingListManager = self::newMailingListManager();
        }

        return self::$_mailingListManager;
    }
    
    /**
     * @return MazelabVpopqmail_Model_NodeManager
     */
    static public function getNodeManager()
    {
        if (!self::$_nodeManager instanceof MazelabVpopqmail_Model_NodeManager) {
            self::$_nodeManager = self::newNodeManager();
        }

        return self::$_nodeManager;
    }
    
    /**
     * returns certain instance of MazelabVpopqmail_Model_ValueObject_MailRobot or
     * MazelabVpopqmail_Model_ValueObject_MailingList
     * 
     * @param string $specialId
     * @return MazelabVpopqmail_Model_ValueObject_MailingList|MazelabVpopqmail_Model_ValueObject_MailRobot|null
     */
    static public function getSpecial($specialId)
    {
        if(($mailRobot = self::getMailRobot($specialId))) {
            return $mailRobot;
        }
        
        if(($mailingList = self::getMailingList($specialId))) {
            return $mailingList;
        }
        
        return null;
    }
    
    /**
     * @return MazelabVpopqmail_Model_SpecialsManager
     */
    static public function getSpecialsManager()
    {
        if (!self::$_specialsManager instanceof MazelabVpopqmail_Model_SpecialsManager) {
            self::$_specialsManager = self::newSpecialsManager();
        }

        return self::$_specialsManager;
    }
    
    /**
     * @return MazelabVpopqmail_Model_ReportManager
     */
    static public function getReportManager()
    {
        if (!self::$_reportManager instanceof MazelabVpopqmail_Model_ReportManager) {
            self::$_reportManager = self::newReportManager();
        }

        return self::$_reportManager;
    }
    
    /**
     * checks if a certain account instance is allready registered
     * 
     * @param string $accountId
     * @return boolean
     */
    static public function isAccountRegistered($accountId)
    {
        if(!isset(self::$_account[$accountId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain catch all instance is allready registered
     * 
     * @param string $catchAllId
     * @return boolean
     */
    static public function isCatchAllRegistered($catchAllId)
    {
        if(!isset(self::$_catchAll[$catchAllId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain forwarder instance is allready registered
     * 
     * @param string $forwarderId
     * @return boolean
     */
    static public function isForwarderRegistered($forwarderId)
    {
        if(!isset(self::$_forwarder[$forwarderId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain mail robot instance is allready registered
     * 
     * @param string $mailRobotId
     * @return boolean
     */
    static public function isMailRobotRegistered($mailRobotId)
    {
        if(!isset(self::$_mailRobot[$mailRobotId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain mailing list instance is allready registered
     * 
     * @param string $mailingListId
     * @return boolean
     */
    static public function isMailingListRegistered($mailingListId)
    {
        if(!isset(self::$_mailingList[$mailingListId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * create new MazelabVpopqmail_Model_ValueObject_Account instance
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Model_ValueObject_Account
     */
    static public function newAccount($accountId = null)
    {
        return new MazelabVpopqmail_Model_ValueObject_Account($accountId);
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_AccountManager
     * 
     * @return MazelabVpopqmail_Model_AccountManager
     */
    static public function newAccountManager()
    {
        return new MazelabVpopqmail_Model_AccountManager();
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with initialized account pager
     * 
     * @param string $ownerId
     * @param string $domainId
     * @return Core_Model_SearchManager
     */
    static public function newAccountPager($ownerId = null, $domainId = null)
    {
        $pager = Core_Model_DiFactory::newSearchManager();
        
        $pager->setProvider(MazelabVpopqmail_Model_Dataprovider_DiFactory::newAccountPager($ownerId, $domainId));
        
        return $pager;
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_Apply_Account
     * 
     * @return MazelabVpopqmail_Model_Apply_Account
     */
    static public function newApplyAccount()
    {
        return new MazelabVpopqmail_Model_Apply_Account();
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_Apply_CatchAll
     * 
     * @return MazelabVpopqmail_Model_Apply_CatchAll
     */
    static public function newApplyCatchAll()
    {
        return new MazelabVpopqmail_Model_Apply_CatchAll();
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_Apply_Forwarder
     * 
     * @return MazelabVpopqmail_Model_Apply_Forwarder
     */
    static public function newApplyForwarder()
    {
        return new MazelabVpopqmail_Model_Apply_Forwarder();
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_Apply_List
     * 
     * @return MazelabVpopqmail_Model_Apply_List
     */
    static public function newApplyList()
    {
        return new MazelabVpopqmail_Model_Apply_List();
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_Apply_Robot
     * 
     * @return MazelabVpopqmail_Model_Apply_Robot
     */
    static public function newApplyRobot()
    {
        return new MazelabVpopqmail_Model_Apply_Robot();
    }
    
    /**
     * @return MazelabVpopqmail_Model_ValueObject_CatchAll
     */
    static public function newCatchAll($id = null)
    {
        return new MazelabVpopqmail_Model_ValueObject_CatchAll($id);
    }
    
    /**
     * @return MazelabVpopqmail_Model_CatchAllManager
     */
    static public function newCatchAllManager()
    {
        return new MazelabVpopqmail_Model_CatchAllManager();
    }
    
    /**
     * @return MazelabVpopqmail_Model_ClientManager
     */
    static public function newClientManager()
    {
        return new MazelabVpopqmail_Model_ClientManager();
    }
    
    /**
     * @return MazelabVpopqmail_Model_ConfigManager
     */
    static public function newConfigManager()
    {
        return new MazelabVpopqmail_Model_ConfigManager();
    }
    
   /**
     * create new MazelabVpopqmail_Model_DomainManager instance
     * 
     * @return MazelabVpopqmail_Model_DomainManager
     */
    static public function newDomainManager()
    {
        return new MazelabVpopqmail_Model_DomainManager();
    }

    /**
     * get new instance of MazelabVpopqmail_Model_IndexManager
     * 
     * @return MazelabVpopqmail_Model_IndexManager
     */
    static public function newIndexManager()
    {
        return new MazelabVpopqmail_Model_IndexManager();
    }
    
    /**
     * create new MazelabVpopqmail_Model_ValueObject_Forwarder instance
     * 
     * @param string $forwardeId
     * @return MazelabVpopqmail_Model_ValueObject_Forwarder
     */
    static public function newForwarder($forwardeId = null)
    {
        return new MazelabVpopqmail_Model_ValueObject_Forwarder($forwardeId);
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_ForwarderManager
     * 
     * @return MazelabVpopqmail_Model_ForwarderManager
     */
    static public function newForwarderManager()
    {
        return new MazelabVpopqmail_Model_ForwarderManager();
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with initialized forwarder pager
     * 
     * @param string $ownerId
     * @param string $domainId
     * @return Core_Model_SearchManager
     */
    static public function newForwarderPager($ownerId = null, $domainId = null)
    {
        $pager = Core_Model_DiFactory::newSearchManager();
        
        $pager->setProvider(MazelabVpopqmail_Model_Dataprovider_DiFactory::newForwarderPager($ownerId, $domainId));
        
        return $pager;
    }
    
    /**
     * @return MazelabVpopqmail_Model_ValueObject_MailRobot
     */
    static public function newMailRobot($id = null)
    {
        return new MazelabVpopqmail_Model_ValueObject_MailRobot($id);
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_MailRobotManager
     * 
     * @return MazelabVpopqmail_Model_MailRobotManager
     */
    static public function newMailRobotManager()
    {
        return new MazelabVpopqmail_Model_MailRobotManager();
    }
    
    /**
     * @return MazelabVpopqmail_Model_ValueObject_MailingList
     */
    static public function newMailingList($id = null)
    {
        return new MazelabVpopqmail_Model_ValueObject_MailingList($id);
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_MailingListManager
     * 
     * @return MazelabVpopqmail_Model_MailingListManager
     */
    static public function newMailingListManager()
    {
        return new MazelabVpopqmail_Model_MailingListManager();
    }
    
    /**
     * returns new instance of MazelabVpopqmail_Model_NodeManager
     * 
     * @return MazelabVpopqmail_Model_NodeManager
     */
    static public function newNodeManager()
    {
        return new MazelabVpopqmail_Model_NodeManager();
    }

    /**
     * @return MazelabVpopqmail_Model_SpecialsManager
     */
    static public function newSpecialsManager()
    {
        return new MazelabVpopqmail_Model_SpecialsManager();
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with initialized specials pager
     * 
     * @param string $ownerId
     * @param string $domainId
     * @return Core_Model_SearchManager
     */
    static public function newSpecialsPager($ownerId = null, $domainId = null)
    {
        $pager = Core_Model_DiFactory::newSearchManager();
        
        $pager->setProvider(MazelabVpopqmail_Model_Dataprovider_DiFactory::newSpecialsPager($ownerId, $domainId));
        
        return $pager;
    }
    
    /**
     * @return MazelabVpopqmail_Model_ReportManager
     */
    static public function newReportManager()
    {
        return new MazelabVpopqmail_Model_ReportManager();
    }
    
    /**
     * registers a certain account instance
     * 
     * @param string $accountId
     * @param MazelabVpopqmail_Model_ValueObject_Account $account
     */
    static public function registerAccount($accountId, MazelabVpopqmail_Model_ValueObject_Account $account)
    {
        self::$_account[$accountId] = $account;
        self::$_mapAccountEmail[$account->getEmail()] = $accountId;
    }
    
    /**
     * registers a certain catch all instance
     * 
     * @param string $catchAllId
     * @param MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll
     */
    static public function registerCatchAll($catchAllId, MazelabVpopqmail_Model_ValueObject_CatchAll $catchAll)
    {
        self::$_catchAll[$catchAllId] = $catchAll;
        if(($domain = $catchAll->getDomain())) {
            self::$_mapCatchAllDomain[$domain->getId()] = $catchAllId;
            self::$_mapCatchAllDomainName[$domain->getName()] = $catchAllId;
        }
    }
    
    /**
     * registers a certain forwarder instance
     * 
     * @param string $forwarderId
     * @param MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder
     */
    static public function registerForwarder($forwarderId, MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder)
    {
        self::$_forwarder[$forwarderId] = $forwarder;
        self::$_mapForwarderEmail[$forwarder->getEmail()] = $forwarderId;
    }
    
    /**
     * registers a certain mail robot instance
     * 
     * @param string $mailRobotId
     * @param MazelabVpopqmail_Model_ValueObject_MailRobot $mailRobot
     */
    static public function registerMailRobot($mailRobotId, MazelabVpopqmail_Model_ValueObject_MailRobot $mailRobot)
    {
        self::$_mailRobot[$mailRobotId] = $mailRobot;
        self::$_mapMailRobotEmail[$mailRobot->getEmail()] = $mailRobotId;
    }
    
    /**
     * registers a certain mailing list instance
     * 
     * @param string $mailingListId
     * @param MazelabVpopqmail_Model_ValueObject_MailingList $mailingList
     */
    static public function registerMailingList($mailingListId, MazelabVpopqmail_Model_ValueObject_MailingList $mailingList)
    {
        self::$_mailingList[$mailingListId] = $mailingList;
        self::$_mapMailingListEmail[$mailingList->getEmail()] = $mailingListId;
    }
    
    /**
     * @param MazelabVpopqmail_Model_AccountManager $manager 
     */
    static public function setAccountManager(MazelabVpopqmail_Model_AccountManager $manager)
    {
        self::$_accountManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_CatchAllManager $manager 
     */
    static public function setCatchAllManager(MazelabVpopqmail_Model_CatchAllManager $manager)
    {
        self::$_catchAllManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_ClientManager $manager 
     */
    static public function setClientManager(MazelabVpopqmail_Model_ClientManager $manager)
    {
        self::$_clientManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_ConfigManager $manager 
     */
    static public function setConfigManager(MazelabVpopqmail_Model_ConfigManager $manager)
    {
        self::$_configManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_DomainManager $manager 
     */
    static public function setDomainManager(MazelabVpopqmail_Model_DomainManager $manager)
    {
        self::$_domainManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_ForwarderManager $manager 
     */
    static public function setForwarderManager(MazelabVpopqmail_Model_ForwarderManager $manager)
    {
        self::$_forwarderManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_MailRobotManager $manager 
     */
    static public function setMailRobotManager(MazelabVpopqmail_Model_MailRobotManager $manager)
    {
        self::$_mailRobotManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_MailingListManager $manager 
     */
    static public function setMailingListManager(MazelabVpopqmail_Model_MailingListManager $manager)
    {
        self::$_mailingListManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_NodeManager $manager 
     */
    static public function setNodeManager(MazelabVpopqmail_Model_NodeManager $manager)
    {
        self::$_nodeManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_SpecialsManager $manager 
     */
    static public function setSpecialsManager(MazelabVpopqmail_Model_SpecialsManager $manager)
    {
        self::$_specialsManager = $manager;
    }
    
    /**
     * @param MazelabVpopqmail_Model_ReportManager $manager 
     */
    static public function setReportManager(MazelabVpopqmail_Model_ReportManager $manager)
    {
        self::$_reportManager = $manager;
    }
    
    /**
     * unregisters a certain account instance
     * 
     * @param string $accountId
     */
    static public function unregisterAccount($accountId)
    {
        if(isset(self::$_account[$accountId]) &&
                ($account =  self::getAccount($accountId))) {
            unset(self::$_mapAccountEmail[$account->getEmail()]);
            unset(self::$_account[$accountId]);
        }
    }
    
    /**
     * unregisters a certain catch all instance
     * 
     * @param string $catchAllId
     */
    static public function unregisterCatchAll($catchAllId)
    {
        if(isset(self::$_catchAll[$catchAllId]) &&
                ($catchAll =  self::getCatchAll($catchAllId))) {
            unset(self::$_catchAll[$catchAllId]);
            
            if(($domain = $catchAll->getDomain())) {
                unset(self::$_mapCatchAllDomain[$domain->getId()]);
                unset(self::$_mapCatchAllDomainName[$domain->getName()]);
            }
        }
    }
    
    /**
     * unregisters a certain forwarder instance
     * 
     * @param string $forwarderId
     */
    static public function unregisterForwarder($forwarderId)
    {
        if(isset(self::$_forwarder[$forwarderId]) &&
                ($forwarder =  self::getForwarder($forwarderId))) {
            unset(self::$_mapForwarderEmail[$forwarder->getEmail()]);
            unset(self::$_forwarder[$forwarderId]);
        }
    }
    
    /**
     * unregisters a certain mail robot instance
     * 
     * @param string $mailRobotId
     */
    static public function unregisterMailRobot($mailRobotId)
    {
        if(isset(self::$_mailRobot[$mailRobotId])  &&
                ($mailRobot = self::getMailRobot($mailRobotId))) {
            unset(self::$_mapMailRobotEmail[$mailRobot->getEmail()]);
            unset(self::$_mailRobot[$mailRobotId]);
        }
    }
    
    /**
     * unregisters a certain mailing list instance
     * 
     * @param string $mailingListId
     */
    static public function unregisterMailingList($mailingListId)
    {
        if(isset(self::$_mailingList[$mailingListId]) &&
                ($mailingList = self::getMailingList($mailingListId))) {
            unset(self::$_mapMailingListEmail[$mailingList->getEmail()]);
            unset(self::$_mailingList[$mailingListId]);
        }
    }
    
}

