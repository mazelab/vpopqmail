<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_Forwarder extends Zend_Form 
{
    
    /**
     * message when accounts are available
     */
    CONST ACCOUNTS_AVAILABLE = "add Email account";
    
    /**
     * message when accounts aren't available
     */
    CONST ACCOUNTS_NOT_AVAILABLE = "no email account available";

    public function init()
    {
        $this->addSubForm(new Zend_Form, 'forwardTo');
        $this->getSubForm('forwardTo')->setOptions(array('isArray' => true));

        $this->addElement('select', 'accounts', array(
            'label' => 'Email account',
            'class' => 'selectFormMenu',
            'value' => array(''),
        ));

        $this->addElement('text', 'addForward', array(
            'label' => 'E-mail address:',
            'validators' => array(
                array('EmailAddress')
            )
        ));
    }
    
    /**
     * disables account select if empty
     * 
     * @return MazelabVpopqmail_Form_Forwarder
     */
    public function disableAccountSelectboxIfEmpty()
    {
        /* @var $element Zend_Form_Element_Select  */
        foreach ($this->getElements() as $element) {
            if (!$element instanceof Zend_Form_Element_Select) {
                continue;
            }

            $listOptions = $element->getMultiOptions();
            if (count($listOptions) == 1 && array_key_exists("", $element->getMultiOptions())){
                $element->setAttrib("disabled", "disabled");
            }
        }
        
        return $this;
    }
    
    /**
     * sets account selection
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder
     * @return MazelabVpopqmail_Form_Forwarder
     */
    public function setAccountSelect(MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder)
    {
        $mailAccounts = array();

        if (($owner = $forwarder->getOwner())) {
            $targets = $forwarder->getForwarderTargets();
            
            foreach (MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountsByOwner($owner->getId()) as $account) {
                if (!in_array($account->getEmail(), $targets)){
                    $mailAccounts[$account->getEmail()] = $account->getEmail();
                }
            }
        }
        
        $mailAccounts[""] = empty($mailAccounts) ? self::ACCOUNTS_NOT_AVAILABLE : self::ACCOUNTS_AVAILABLE;
        
        $this->getElement('accounts')->setMultiOptions($mailAccounts);
        
        return $this;
    }
    
    /**
     * sets forwarder Target entries
     * 
     * @param MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder
     * @return MazelabVpopqmail_Form_Forwarder
     */
    public function setForwarderTargets(MazelabVpopqmail_Model_ValueObject_Forwarder $forwarder)
    {
        foreach ($forwarder->getForwarderTargets() as $key => $value) {
            $this->getSubForm('forwardTo')->addElement('text', (string) $key, array(
                'class' => 'jsEditable',
                'label' => 'E-mail address:',
                'helper' => 'formTextAsSpan',
                'validators' => array(
                    array('emailAddress')
                )
            ));
        }
        
        return $this;
    }
    
    /**
     *  sets forwarderTarget validator to addForward element
     * 
     * @param string $forwarderId
     * @return MazelabVpopqmail_Form_ForwarderTarget
     */
    public function setValidatorForwarderTarget($forwarderId)
    {
        $this->getElement('addForward')
                ->addValidator(new MazelabVpopqmail_Form_Validate_ForwarderTarget($forwarderId));

        return $this;
    }

}
