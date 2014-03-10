<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_DiffAccountRemote extends Zend_Form
{
    
    public function init()
    {
        
        $this->addElement('text', 'quota', array(
            'label' => 'mail quota in MB',
            'class' => 'cssDisabled',
            'readonly' => true,
            'validators' => array(
                array('Digits')
            ),
        ));
        
    }
    
    /**
     * adds validator for quota limits
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Form_Account
     */
    public function setQuotaLimitValidator($accountId)
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $owner = $accountManager->getAccount($accountId)->getOwner();
        $clientConfig = $configManager->getClientConfig($owner->getId());
        
        // no quota limit set
        if(empty($clientConfig) || !key_exists('quota', $clientConfig) || !is_numeric($clientConfig['quota'])) {
            return true;
        }
        
        $this->getElement('quota')->setRequired()
                ->addValidator(new MazelabVpopqmail_Form_Validate_AvailableQuotas(array(
                    'clientId' => $owner->getId(),
                    'selectedAccountId' => $accountId
                )));

        return $this;
    }

}

