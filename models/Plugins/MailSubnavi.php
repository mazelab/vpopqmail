<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Plugins_MailSubnavi extends Zend_Controller_Plugin_Abstract
{
    
    /**
     * @param Zend_Controller_Request_Abstract $request 
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if(!($identity = Zend_Auth::getInstance()->getIdentity()) || !key_exists('group', $identity) || 
                !key_exists('_id', $identity)  || $identity['group'] !== Core_Model_UserManager::GROUP_CLIENT) {
            return false;
        }

        if($this->clientHasServiceAndActiveDomains($identity['_id'])) {
            $this->initNavigation($request);
        }
    }
    
    /**
     * checks that given client has the vpopqmail service and domains for it
     * 
     * @param string $clientId
     * @return boolean
     */
    public function clientHasServiceAndActiveDomains($clientId)
    {
        if(!($client = Core_Model_DiFactory::getClientManager()->getClient($clientId))) {
            return false;
        }
        
        if(!$client->hasService(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME) ||
                !MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByOwner($clientId)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * add vpopqmail navigation to navigation instance
     */
    public function initNavigation()
    {
        $naviPath = __DIR__ . '/../../configs/navigation.ini';

        if (file_exists($naviPath)) {
            $view = Zend_Layout::getMvcInstance()->getView();
            $config = new Zend_Config_Ini($naviPath);

            $view->navigation()->addPages($config);
        }
    }

}

