<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_Validate_AvailableQuotas extends Zend_Validate_Abstract
{

    CONST INVALID_ACCOUNT_QUOTA = 'invalidAccountQuota';
    CONST INVALID_CLIENTID = 'invalidClientId';
    CONST REACHED_LIMIT = 'reachedLimit';
    CONST LIMIT_EXCEEDED = 'limitExceeded';
    
    protected $_messageTemplates = array(
        self::INVALID_ACCOUNT_QUOTA => 'Invalid account quota found in %value%. Please set email account quota before proceeding.',
        self::REACHED_LIMIT => 'Reached limit for quota. To create a new account, you have to release at least %value% MB quota.',
        self::LIMIT_EXCEEDED => 'Quota limit exceeded. You can use up to %value% MB quota.',
        self::INVALID_CLIENTID => 'ClientId not given. Can not proceed...',
    );
    
    /**
     * reference for quota context
     * 
     * @var string
     */
    protected $_clientId;
    
    /**
     * does not add quota of this account in available quota calculation
     * 
     * @var string
     */
    protected $_selectedAccountId;

    /**
     * Sets validator options
     *
     * @param array $config
     * @return void
     */
    public function __construct(array $config)
    {
        if(!is_array($config)) {
            return false;
        }
        
        if(array_key_exists('clientId', $config)) {
            $this->_clientId = $config['clientId'];
        }
            
        if(array_key_exists('selectedAccountId', $config)) {
            $this->_selectedAccountId = $config['selectedAccountId'];
        }
    }
    
    public function isValid($quota, $context = null)
    {
        $result = $this->validate($quota);

        if (!$result){
            return false;
        }
 
        return true;

    }

    protected function validate($quota)
    {
        if(!($clientId = $this->_clientId)) {
            $this->_error(self::INVALID_CLIENTID);
            return false;
        }
        
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();

        $clientConfig = $configManager->getClientConfig($clientId);
        $usedQuota = MazelabVpopqmail_Model_DiFactory::getClientManager()->getUsedQuotas($clientId);

        // substract quota of selected account on usedQuota
        if($this->_selectedAccountId) {
            $selectedAccount = $accountManager->getAccount($this->_selectedAccountId);
            
            if($selectedAccount->getQuota()) {
                $usedQuota = ($usedQuota - $selectedAccount->getQuota());
            }
        }
        
        // search for invalid quotas in email accounts of given client
        if(!isset($selectedAccount) || $selectedAccount->getQuota()) {
            $invalidAccounts = array();
            foreach($accountManager->getAccountsByOwner($clientId) as $account) {
                if(!$account->getQuota()) {
                    $invalidAccounts[] = $account->getEmail();
                }
            }

            if(!empty($invalidAccounts)) {
                $invalidAccounts = implode(', ', $invalidAccounts);
                $this->_error(self::INVALID_ACCOUNT_QUOTA, $invalidAccounts);
                return false;
            }
        }
        
        // check if limit is allready reached
        $availableQuota = ($clientConfig['quota'] - $usedQuota);
        if($availableQuota < 1) {
            if($availableQuota == 0) {
                $this->_error(self::REACHED_LIMIT, 1);
            } else {
                $this->_error(self::REACHED_LIMIT, abs($availableQuota) + 1);
            }
            
            return false;
        }
        
        // check that given quota is within limit
        if($quota > $availableQuota) {
            $this->_error(self::LIMIT_EXCEEDED, $availableQuota);
            return false;
        }
        
        return true;
    }

}
