<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_AddAccount extends Zend_Form
{

    /**
    * @todo own validator
    */
    public function init()
    {
        
        $this->addElement('text', 'user', array(
            'required' => true,
            'label' => 'E-mail address:',
            'validators' => array(
               new MazelabVpopqmail_Form_Validate_EmailUser()
            )
        ));
        $this->addElement('select', 'domainId', array(
            'required' => true,
            'label' => '@',
            'class' => 'selectFormMenu'
        ));
        $this->addElement('password', 'password', array(
            'label' => 'password',
            'required' => 'true',
            'validators' => array(
                array('StringLength', NULL, array(4))
            )
        ));
        $this->addElement('text', 'quota', array(
            'label' => 'mail quota in MB',
            'validators' => array(
                'digits',
                 array('greaterThan', false, array(0))
            ),
        ));
        $this->addElement('select', 'status', array(
            "label" => "the account is",
            "multiOptions" => array(
                "0" => "deactivated",
                "1" => "activated"
            ),
            "class" => "selectFormMenu",
            "value" => array(1)
        ));
    }

    /**
     * sets domains select with given domainId
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Form_AddAccount
     */
    public function setDomainSelectByDomain($domainId)
    {
        if(($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return $this->setDomainSelectByOwner($domain->getData('owner'));
        }
        
        return $this;
    }
    
    /**
     * sets domains select with given clientId
     * 
     * @param string $clientId
     * @return MazelabVpopqmail_Form_AddAccount
     */
    public function setDomainSelectByOwner($clientId)
    {
        foreach (MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByOwner($clientId) as $id => $domain) {
            $this->getElement('domainId')->addMultiOption($id, $domain->getName());
        }
        
        return $this;
    }

    /**
     * if quota limit is set for given client it adds validator for quota limits
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Form_AddAccount
     */
    public function setQuotaLimitValidator($clientId)
    {
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $clientConfig = $configManager->getClientConfig($clientId);
        
        // no quota limit set
        if(!is_array($clientConfig) || !array_key_exists('quota', $clientConfig)
                || !is_numeric($clientConfig['quota'])) {
            return $this;
        }
        
        $this->getElement('quota')->setRequired()->addValidator(
                new MazelabVpopqmail_Form_Validate_AvailableQuotas(array('clientId' => $clientId)));
        
        return $this;
    }
    
    /**
     * if account limit is set for given client it adds validator for account limits
     * 
     * @param string $clientId
     * @return MazelabVpopqmail_Form_AddAccount
     */
    public function setAccountLimitValidator($clientId)
    {
        $configManager = MazelabVpopqmail_Model_DiFactory::getConfigManager();
        $clientConfig = $configManager->getClientConfig($clientId);
        
        // no quota limit set
        if(!is_array($clientConfig) || !array_key_exists('countAccounts', $clientConfig)
                || !is_numeric($clientConfig['countAccounts'])) {
            return $this;
        }
        
        $this->getElement('user')
                ->addValidator(new MazelabVpopqmail_Form_Validate_AvailableAccounts($clientId));
        
        return $this;
    }
    
    /**
     * selects given domain and disables domain select
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Form_AddAccount
     */
    public function showOnlyDomainAndDisableSelectbox($domainId)
    {
        if (!is_string($domainId)){
            return;
        }
        
        $this->setDefault("domainId", $domainId);
        foreach($this->getElement("domainId")->getMultiOptions() as $optionId => $option){
            if ($optionId != $domainId){
                $this->getElement("domainId")->removeMultiOption($optionId);
            }
        }
        $this->getElement("domainId")->setAttrib("disabled", "disabled");
        
        return $this;
    }

}

