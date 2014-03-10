<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_Validate_AvailableAccounts extends Zend_Validate_Abstract
{

    const INVALID_CLIENTID = 'invalidClientId';
    const LIMIT_REACHED = 'reachedLimit';
    
    protected $_messageTemplates = array(
        self::LIMIT_REACHED => 'Reached limit for email accounts. To create a new email account, you have to delete another one.',
        self::INVALID_CLIENTID => 'ClientId not given. Can not proceed...',
    );
    
    /**
     * reference for quota context
     * 
     * @var string
     */
    protected $_clientId;
    
    /**
     * Sets validator options
     *
     * @param string $clientId
     * @return void
     */
    public function __construct($clientId)
    {
        $this->_clientId = $clientId;
    }
    
    public function isValid($value)
    {
        $result = $this->validate();

        if (!$result){
            return false;
        }
 
        return true;

    }

    protected function validate()
    {
        if(!($clientId = $this->_clientId)) {
            $this->_error(self::INVALID_CLIENTID);
            return false;
        }
        
        if(MazelabVpopqmail_Model_DiFactory::getClientManager()->getUsedAccountsInPercent($clientId) >= 100) {
            $this->_error(self::LIMIT_REACHED);
            return false;
        }
        
        return true;
    }

}
