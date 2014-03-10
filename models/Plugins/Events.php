<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_Plugins_Events extends Zend_Controller_Plugin_Abstract
{
    /**
     * Called after Zend_Controller_Router exits.
     *
     * init Events
     * 
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown($request)
    {
        $this->_buildSearchIndexes();
    }
    
    /**
     * init event for rebuilding the complete search index
     */
    protected function _buildSearchIndexes()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('Core_Model_IndexManager', 'setIndexes', function ($e) {
            MazelabVpopqmail_Model_DiFactory::getIndexManager()->setIndexes();
        });
    }

}