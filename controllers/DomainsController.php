<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_DomainsController extends Zend_Controller_Action
{
    protected $_identity;
    
    public function init()
    {
        $this->_identity = Zend_Auth::getInstance()->getIdentity();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        $identity = Zend_Auth::getInstance()->getIdentity();
        
        $this->view->client = $clientManager->getClientAsArray($this->_identity['_id']);
        $this->view->domains = MazelabVpopqmail_Model_DiFactory::getDomainManager()
                ->getDomainsAndCatchAllAndConfigByOwnerAsArray($this->_identity['_id']);
        $this->view->clientId = $identity['_id'];
    }

}

