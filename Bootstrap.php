<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Bootstrap extends Zend_Application_Module_Bootstrap
{

    protected function _initAcl()
    {
        $aclPath = __DIR__ . '/configs/acl.ini';

        if (file_exists($aclPath)) {
            $acl = Zend_Registry::getInstance()->get('MazeLib_Acl_Builder');
            $acl->addConfig(new Zend_Config_Ini($aclPath));
        }
    }
    
    protected function _initPlugins()
    {
        $bootstrap = $this->getApplication();
        $bootstrap->bootstrap('FrontController');
        $front = $bootstrap->getResource('FrontController');
        
        $front->registerPlugin(new MazelabVpopqmail_Model_Plugins_MailSubnavi)
              ->registerPlugin(new MazelabVpopqmail_Model_Plugins_InitLayout)
              ->registerPlugin(new MazelabVpopqmail_Model_Plugins_Events());
    }
    
    public function _initRouter()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        // if routes.ini exitsts then use it
        if (file_exists(__DIR__. '/configs/routes.ini')) {
            $routingFile = __DIR__. '/configs/routes.ini';
            $router->addConfig(new Zend_Config_Ini($routingFile, $this->getEnvironment()), 'routes');
        }

        return $router;
    }

    protected function _initTranslate()
    {
        if (Zend_Registry::getInstance()->isRegistered("Zend_Translate")){
            $translate = Zend_Registry::getInstance()->get("Zend_Translate");

            $translate->getAdapter()->addTranslation(__DIR__. "/data/locales/");
        }
    }
    
}
