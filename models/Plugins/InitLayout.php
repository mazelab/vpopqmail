<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Plugins_InitLayout extends Zend_Controller_Plugin_Abstract
{
    /**
     * Called after Zend_Controller_Router exits.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown($request)
    {
        if ($request->getModuleName() == "mazelab-vpopqmail"){
            $view = Zend_Layout::getMvcInstance()->getView();
            $view->headLink()->prependStylesheet("/module/mazelab/vpopqmail/css/default.css");
        }
    }
    
}