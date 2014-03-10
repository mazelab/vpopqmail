<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_Account extends Zend_Form
{
    CONST FORWARDERS_AVAILABLE = "add Forwarding";
    CONST FORWARDERS_NOT_AVAILABLE = "no forwarding available";

    public function __construct(array $options = null)
    {
        $this->addPrefixPath('MazeLib_Form_Element', 'MazeLib/Form/Element/', Zend_Form::ELEMENT);

        parent::__construct($options);
    }

    public function init()
    {
        $this->addElement('password', 'password', array(
            'jsLabel' => 'password',
            'label' => 'new password',
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4)),
                array('identical', true, array('confirmPassword'))
            )
        ));
        $this->addElement('password', 'confirmPassword', array(
            'label' => 'confirm password',
            'ignore' => true,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', NULL, array(4)),
                array('identical', true, array('password'))
            )
        ));
        
        $this->addElement('text', 'quota', array(
            'jsLabel' => 'mail quota in MB',
            'label' => 'mail quota in MB',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan',
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
    
    /**
     * sets given forwarder as used forwarder in this form with references to
     * forwardTo - forwarderId and forwarder email
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Form_Account
     */
    public function setUsedForwarders($accountId)
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        $account = $accountManager->getAccount($accountId);
        
        $this->addSubForm(new Zend_Form(), 'forwardTo');
        
        foreach($forwarderManager->getForwardersOfTarget($account->getEmail()) as $forwarderId => $forwarder) {
            $this->getSubForm('forwardTo')->addElement('text', $forwarder->getEmail(), array(
                'label' => 'Forwarder',
                'helper' => 'formTextAsSpan',
                'value' => $forwarder->getEmail(),
                'ref' => 'forwardTo[' . md5($account->getEmail()) .  ']',
                'href' => $this->getView()->url(array($forwarder->getEmail()), 'mazelab-vpopqmail_forwarderdetail')
            ));
        }
        
        return $this;
    }
    
    /**
     * sets selectable/available forwarder for this email account
     * 
     * @param string $accountId
     * @return MazelabVpopqmail_Form_Account
     */
    public function setAvailableForwarders($accountId)
    {
        $accountManager = MazelabVpopqmail_Model_DiFactory::getAccountManager();
        $forwarderManager = MazelabVpopqmail_Model_DiFactory::getForwarderManager();
        
        $account = $accountManager->getAccount($accountId);
        $forwarders = $forwarderManager->getForwardersByOwner($account->getOwner()->getId());
        $usedForwarders = $forwarderManager->getForwardersOfTargetAsArray($account->getEmail());
        $forwardersNotPresent = array_diff_key($forwarders, $usedForwarders);

        $this->addElement('selectAttribs', 'forwarders', array(
            'label' => 'Forwarder',
            'multiOptions' => array("" => $forwardersNotPresent ? self::FORWARDERS_AVAILABLE : self::FORWARDERS_NOT_AVAILABLE),
            'value' => array('')
        ));

        foreach ($forwardersNotPresent as $forwarderId => $forwarder) {
            $this->getElement('forwarders')->setOption($forwarderId, $forwarder->getEmail(), array(
                'rel' => $this->getView()->url(array($forwarder->getEmail()), "MazelabVpopqmail_addForwarderTarget"),
                'ref' => 'forwardTo[' . md5($account->getEmail()) .  ']',
                'href' => $this->getView()->url(array($forwarder->getEmail()), 'MazelabVpopqmail_forwarderdetail')
            ));
        }

        return $this;
    }

    /**
     * disables the select elements in the form if they are empty
     * 
     * @return MazelabVpopqmail_Form_Account
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

}

