<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_AddForwarder extends Zend_Form 
{

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
        $this->addElement("select", "status", array(
            "label" => "the forwarder is",
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
     * @return MazelabVpopqmail_Form_AddForwarder
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

