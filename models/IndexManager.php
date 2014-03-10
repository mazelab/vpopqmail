<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_IndexManager
{

    /**
     * search category string for accounts
     */
    CONST SEARCH_CATEGORY_ACCOUNT = 'Vpopqmail account';
    
    /**
     * search category string for catch all
     */
    CONST SEARCH_CATEGORY_CATCHALL = 'Vpopqmail catch-all';
    
    /**
     * search category string for forwarders
     */
    CONST SEARCH_CATEGORY_FORWARDER = 'Vpopqmail forwarder';

    /**
     * search category string for mailing list
     */
    CONST SEARCH_CATEGORY_LIST = 'Vpopqmail mailing list';
    
    /**
     * search category string for mail robot
     */
    CONST SEARCH_CATEGORY_ROBOT = 'Vpopqmail mail robot';
    
    /**
     * Zend_View_Helper_Url
     */
    protected  $_urlHelper;
    
    /**
     * get search index core model
     * 
     * @return Core_Model_Search_Index
     */
    public function _getSearchIndex()
    {
        return Core_Model_DiFactory::getSearchIndex();
    }
    
    /**
     * get zend url helper
     * 
     * @return Zend_View_Helper_Url
     */
    public function _getUrlHelper()
    {
        if(!$this->_urlHelper) {
            $this->_urlHelper = new Zend_View_Helper_Url();
        }
        
        return $this->_urlHelper;
    }
    
    /**
     * builds and save core search index of a certain account
     * 
     * @return boolean
     */
    public function setAccount($accountId)
    {
        if(!($account = MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccount($accountId))) {
            return false;
        }
        
        $data['id'] = $accountId;
        $data['search'] = $account->getEmail();
        $data['teaser'] = 'In order to view or edit this account you have to login as the client';
        $data['headline'] = $account->getEmail();
        
        if(($owner = $account->getOwner())) {
            $data['link'] = $this->_getUrlHelper()->url(array($owner->getId(), $owner->getLabel()), 'clientDetail');
        }
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_ACCOUNT, $accountId, $data);
    }
    
    /**
     * builds and save core search index of a certain catch all
     * 
     * @return boolean
     */
    public function setCatchAll($catchAllId)
    {
        if(!($catchAll = MazelabVpopqmail_Model_DiFactory::getCatchAllManager()->getCatchAll($catchAllId))) {
            return false;
        }
        
        $data['id'] = $catchAllId;
        $data['search'] = $catchAll->getData('domainName');
        $data['teaser'] = 'In order to view or edit this catch-all you have to login as the client';
        $data['headline'] = $catchAll->getData('domainName');
        
        if(($owner = $catchAll->getOwner())) {
            $data['link'] = $this->_getUrlHelper()->url(array($owner->getId(), $owner->getLabel()), 'clientDetail');
        }
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_CATCHALL, $catchAllId, $data);
    }
    
    /**
     * builds and save core search index of a certain forwarder
     * 
     * @return boolean
     */
    public function setForwarder($forwarderId)
    {
        if(!($forwarder = MazelabVpopqmail_Model_DiFactory::getForwarderManager()->getForwarder($forwarderId))) {
            return false;
        }
        
        $data['id'] = $forwarderId;
        $data['search'] = $forwarder->getEmail();
        $data['teaser'] = 'In order to view or edit this forwarder you have to login as the client';
        $data['headline'] = $forwarder->getEmail();
        
        if(($owner = $forwarder->getOwner())) {
            $data['link'] = $this->_getUrlHelper()->url(array($owner->getId(), $owner->getLabel()), 'clientDetail');
        }
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_FORWARDER, $forwarderId, $data);
    }
    
    /**
     * builds and save core search index of a certain mailing list
     * 
     * @return boolean
     */
    public function setList($listId)
    {
        if(!($list = MazelabVpopqmail_Model_DiFactory::getMailingListManager()->getMailingList($listId))) {
            return false;
        }
        
        $data['id'] = $listId;
        $data['search'] = $list->getEmail();
        $data['teaser'] = 'In order to view or edit this mailing list you have to login as the client';
        $data['headline'] = $list->getEmail();
        
        if(($owner = $list->getOwner())) {
            $data['link'] = $this->_getUrlHelper()->url(array($owner->getId(), $owner->getLabel()), 'clientDetail');
        }
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_LIST, $listId, $data);
    }
    
    /**
     * builds and save core search index of a certain mail robot
     * 
     * @return boolean
     */
    public function setRobot($robotId)
    {
        if(!($robot = MazelabVpopqmail_Model_DiFactory::getMailRobotManager()->getMailRobot($robotId))) {
            return false;
        }
        
        $data['id'] = $robotId;
        $data['search'] = $robot->getEmail();
        $data['teaser'] = 'In order to view or edit this mail robot you have to login as the client';
        $data['headline'] = $robot->getEmail();
        
        if(($owner = $robot->getOwner())) {
            $data['link'] = $this->_getUrlHelper()->url(array($owner->getId(), $owner->getLabel()), 'clientDetail');
        }
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_ROBOT, $robotId, $data);
    }
    
    /**
     * builds and save core search index of all accounts
     */
    public function setAccounts()
    {
        foreach(array_keys(MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountsAsArray()) as $accountId) {
            $this->setAccount($accountId);
        }
    }
    
    /**
     * builds and save core search index of all catch alls
     */
    public function setCatchAlls()
    {
        foreach(array_keys(MazelabVpopqmail_Model_DiFactory::getCatchAllManager()->getCatchAllsAsArray()) as $catchAllId) {
            $this->setCatchAll($catchAllId);
        }
    }
    
    /**
     * builds and save core search index of all forwarder
     */
    public function setForwarders()
    {
        foreach(array_keys(MazelabVpopqmail_Model_DiFactory::getForwarderManager()->getForwardersAsArray()) as $forwarderId) {
            $this->setForwarder($forwarderId);
        }
    }
    
    /**
     * builds and save core search index of all mailing lists
     */
    public function setLists()
    {
        foreach(array_keys(MazelabVpopqmail_Model_DiFactory::getMailingListManager()->getMailingListsAsArray()) as $listId) {
            $this->setList($listId);
        }
    }
    
    /**
     * builds and save core search index of all mail robots
     */
    public function setRobots()
    {
        foreach(array_keys(MazelabVpopqmail_Model_DiFactory::getMailRobotManager()->getMailRobotsAsArray()) as $robotId) {
            $this->setRobot($robotId);
        }
    }
    
    /**
     * sets all indexing categories
     */
    public function setIndexes()
    {
        $this->setAccounts();
        $this->setCatchAlls();
        $this->setForwarders();
        $this->setLists();
        $this->setRobots();
    }
    
    /**
     * unsets a certain account in core search index
     * 
     * @param string $accountId
     * @return boolean
     */
    public function unsetAccount($accountId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_ACCOUNT, $accountId);
    }
    
    /**
     * unsets a certain catch all in core search index
     * 
     * @param string $catchAllId
     * @return boolean
     */
    public function unsetCatchAll($catchAllId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_CATCHALL, $catchAllId);
    }
    
    /**
     * unsets a certain forwarder in core search index
     * 
     * @param string $forwarderId
     * @return boolean
     */
    public function unsetForwarder($forwarderId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_FORWARDER, $forwarderId);
    }
    
    /**
     * unsets a certain mailing list in core search index
     * 
     * @param string $listId
     * @return boolean
     */
    public function unsetList($listId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_LIST, $listId);
    }
    
    /**
     * unsets a certain mailing robot in core search index
     * 
     * @param string $robotId
     * @return boolean
     */
    public function unsetRobot($robotId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_ROBOT, $robotId);
    }
    
}