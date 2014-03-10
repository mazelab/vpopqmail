<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_AddMailingList extends Zend_Form
{

    public function init()
    {
        
        $this->addElement('text', 'user', array(
            'label' => 'mailinglist for the account',
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
        
        $this->addElement('text', 'addSubscriber', array(
            'label' => 'E-mail address:',
            'ignore' => true
        ));

        $this->addElement("select", "status", array(
            "label" => "the mailing list is",
            "multiOptions" => array(
                "0" => "deactivated",
                "1" => "activated"
            ),
            "class" => "selectFormMenu",
            "value" => array(1)
        ));
        
        $this->addSubForm(new Zend_Form, 'subscriber');
        $this->getSubForm('subscriber')->setOptions(array('isArray' => true));
        
    }
    
    /**
     * adds posted subscriber values as form fields
     * 
     * @param array $subscriber
     * @return MazelabVpopqmail_Form_MailRobot
     */
    public function addSubscriberFromPost(array $subscriber) {
        
        foreach($subscriber as $key => $email) {
            $this->getSubForm('subscriber')->addElement('text', (string) $key, array(
                'class' => 'jsEditable',
                'label' => 'E-mail address:',
                'helper' => 'formTextAsSpan',
                'validators' => array(
                    array('emailAddress')
                ),
                'value' => $email
            ));
        }
        
        return $this;
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
     * @return MazelabVpopqmail_Form_MailingList
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
    
    /**
     * sets content of mailing list targets
     * 
     * @param string $mailingListId
     * @return MazelabVpopqmail_Form_MailingList
     */
    public function setMailingListTargets($mailingListId)
    {
        $mailingListManager = MazelabVpopqmail_Model_DiFactory::getMailingListManager();
        $mailingList = $mailingListManager->getMailingList($mailingListId);
        
        foreach($mailingList->getSubscribers() as $id => $target) {
            $this->getSubForm('subscriber')->addElement('text', (string) $id, array(
                'class' => 'jsEditable',
                'label' => 'E-mail address:',
                'helper' => 'formTextAsSpan',
                'validators' => array(
                    array('emailAddress')
                ),
                'value' => $target
            ));
        }
        
        return $this;
    }

}

