<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_AddSpecial extends Zend_Form
{

    protected $_specialsTypes = array(
        MazelabVpopqmail_Model_SpecialsManager::MAIL_ROBOT => 'mailrobot',
        MazelabVpopqmail_Model_SpecialsManager::MAILING_LIST => 'mailinglist'
    );

    public function init()
    {
        $this->addElement('select', 'type', array(
            'required' => true,
            'label' => 'special type',
            'class' => 'selectFormMenu',
            'multiOptions' => $this->_specialsTypes,
            'validators' => array(
               new MazelabVpopqmail_Form_Validate_SpecialsCatchAll
            )
        ));
        
        $this->addElement('select', 'domainId', array(
            'required' => true,
            'label' => 'special for domain',
            'class' => 'selectFormMenu'
        ));
    }
    
    /**
     * sets domains select with given clientId
     * 
     * @param string $clientId
     * @return MazelabVpopqmail_Form_AddSpecial
     */
    public function addDomainSelect($clientId)
    {
        foreach (MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByOwner($clientId) as $id => $domain) {
            $this->getElement('domainId')->addMultiOption($id, $domain->getName());
        }
        
        return $this;
    }

    public function showOnlyDomainAndDisableSelectbox($domainId)
    {
        if (!is_string($domainId)){
            return false;
        }
        
        $this->setDefault("domainId", $domainId);
        foreach($this->getElement("domainId")->getMultiOptions() as $optionId => $option){
            if ($optionId != $domainId){
                $this->getElement("domainId")->removeMultiOption($optionId);
            }
        }
        $this->getElement("domainId")->setAttrib("disabled", "disabled");
    }

}

