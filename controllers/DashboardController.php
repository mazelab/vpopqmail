<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_DashboardController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('dashboard', 'html')
                ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function dashboardAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $clientManager = MazelabVpopqmail_Model_DiFactory::getClientManager();

        $this->view->client = Core_Model_DiFactory::getClientManager()->getClientAsArray($identity['_id']);
        $this->view->config = $configManager->getClientConfig($identity['_id']);
        $this->view->quotaUsed = $clientManager->getUsedQuotas($identity['_id']);
        $this->view->accountsUsed = $clientManager->getUsedAccounts($identity['_id']);
        $this->view->quotaInPercent = $clientManager->getUsedQuotasInPercent($identity['_id']);
        $this->view->accoountsInPercent = $clientManager->getUsedAccountsInPercent($identity['_id']);
    }

}

