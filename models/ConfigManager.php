<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Model_ConfigManager
{

    /**
     * error message when saving vpopqmail configuration didn't worked
     */
    CONST ERROR_WHILE_SAVING = 'Something went wrong while saving vpopqmail configuration';
    
    /**
     * name of this module
     */
    CONST MODULE_NAME = 'vpopqmail';
    
    /**
     * adds given data set into a certain vpopqmail client configuration
     * 
     * @param string $clientId
     * @param array $data
     * @return boolean
     */
    public function addClientConfig($clientId, array $data)
    {
        if(!($module = $this->getModule())) {
            return false;
        }
        
        if(!$module->addClientConfig($clientId, $data)->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_WHILE_SAVING);
            return false;
        };
        
        return true;
    }
    
    /**
     * adds given data to the vpopqmail config data set
     * 
     * @return boolean
     */
    public function addConfig(array $data)
    {
        if(!($module = $this->getModule())) {
            return false;
        }
        
        if(!$module->addConfig($data)->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_WHILE_SAVING);
            return false;
        };
        
        return true;
    }
    
    /**
     * adds given data set into a certain vpopqmail domain configuration
     * 
     * @param string $domainId
     * @param array $data
     * @return boolean
     */
    public function addDomainConfig($domainId, array $data)
    {
        if(!($module = $this->getModule())) {
            return false;
        }
        
        if(!$module->addDomainConfig($domainId, $data)->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_WHILE_SAVING);
            return false;
        };
        
        return true;
    }

    /**
     * adds given data set into a certain vpopqmail node configuration
     * 
     * @param string $nodeId
     * @param array $data
     * @return boolean
     */
    public function addNodeConfig($nodeId, array $data)
    {
        if(!($module = $this->getModule())) {
            return false;
        }
        
        if(!$module->addNodeConfig($nodeId, $data)->save()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_WHILE_SAVING);
            return false;
        };
        
        return true;
    }
    
    /**
     * gets complete vpopqmail configuration data set
     * 
     * @return array
     */
    public function getConfig()
    {
        if(!($module = $this->getModule())) {
            return array();
        }
        
        return $module->getConfig();
    }
    
    
    /**
     * returns a certain vpopqmail client configuration as array
     * 
     * @param string $clientId
     * @return array
     */
    public function getClientConfig($clientId)
    {
        if(!($module = $this->getModule())) {
            return array();
        }
        
        return $module->getClientConfig($clientId);
    }
    
    /**
     * returns a certain vpopqmail domain configuration as array
     * 
     * @param string $domainId
     * @return array
     */
    public function getDomainConfig($domainId)
    {
        if(!($module = $this->getModule())) {
            return array();
        }
        
        return $module->getDomainConfig($domainId);
    }
    
    /**
     * returns a certain vpopqmail node configuration as array
     * 
     * @param string $nodeId
     * @return array
     */
    public function getNodeConfig($nodeId)
    {
        if(!($module = $this->getModule())) {
            return array();
        }
        
        return $module->getNodeConfig($nodeId);
    }
    
    /**
     * returns initialized module object of this module
     * 
     * @return Core_Model_ValueObject_Module|null
     */
    public function getModule()
    {
        if(!($module = Core_Model_DiFactory::getModuleManager()
                ->getModule(self::MODULE_NAME))) {
            return null;
        }
        
        return $module;
    }
    
}
