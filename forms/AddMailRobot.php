<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_AddMailRobot extends Zend_Form
{
    public function init()
    {
        $this->addElement('text', 'user', array(
            'label' => 'mailingrobot for the account',
            'required' => true,
            'validators' => array(
                new MazelabVpopqmail_Form_Validate_EmailUser()
            )
        ));

        $this->addElement('select', 'domainId', array(
            'label' => '@',
            'class' => 'selectFormMenu',
            'required' => true
        ));

        $this->addElement('text', 'copyTo', array(
            'label' => 'Copy of the sent email to',
            'validators' => array(
                'emailAddress'
            )
        ));

        $this->addElement('textarea', 'content', array(
            'required' => true,
            'label' => 'answer text'
        ));

        $this->addElement("select", "status", array(
            "label" => "the mail robot is",
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
     * @return MazelabVpopqmail_Form_AddMailingList
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
     * @return MazelabVpopqmail_Form_AddMailingList
     */
    public function setDomainSelectByOwner($clientId)
    {
        foreach (MazelabVpopqmail_Model_DiFactory::getDomainManager()->getEmailDomainsByOwner($clientId) as $id => $domain) {
            $this->getElement('domainId')->addMultiOption($id, $domain->getName());
        }
        
        return $this;
    }
    
    /**
     * sets domains select with given domain
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Form_MailRobot
     */
    public function setDomainSelectValue($domainId)
    {
        $domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId);

        $this->getElement('domainId')
                ->addMultiOption($domainId, $domain->getName())
                ->setValue($domainId)
                ->setAttrib("disabled", "disabled");
        
        return $this;
    }

}