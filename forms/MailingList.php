<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_MailingList extends Zend_Form
{

    public function init()
    {
        
        $this->addElement('text', 'user', array(
            'label' => 'mailinglist for the account',
            'required' => true,
            'helper' => 'formTextAsSpan',
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
            'validators' => array(
                array('EmailAddress')
            ),
        ));
        
        $this->addSubForm(new Zend_Form, 'subscriber');
        $this->getSubForm('subscriber')->setOptions(array('isArray' => true));
    }
    
    /**
     * sets domains select with given domain
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Form_MailRobot
     */
    public function setDomainSelect($domainId)
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
        
        foreach($mailingListManager->getSubscribers($mailingListId, true) as $id => $target) {
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

