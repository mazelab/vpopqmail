<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_CatchAll extends Zend_Form
{
    CONST ACCOUNTS_AVAILABLE = "select Email account";
    CONST ACCOUNTS_NOT_AVAILABLE = "no email account available";

    /**
     * @var array
     */
    protected $_behaviorOptions = array(
        MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_DELETE => '',
        MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_SEND_TO_EMAIL => '',
        MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_MOVE_TO_ACCOUNT => '',
    );
     
    public function init()
    {
        $this->addElement('radio', 'selectedBehavior', array(
            'required' => true,
            'value' => 'delete',
            'multiOptions' => $this->_behaviorOptions
        ));
        
        $this->addElement('text', 'sendToEmail', array(
            'validators' => array(
                array('EmailAddress')
            ),
        ));
        
        $this->addElement('select', 'sendToAccount', array(
            'class' => 'selectFormMenu',
            'value' => array(''),
        ));
    }

    /**
     * adds validators depending on given behavior and validates the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
        $behavior = null;
        
        if(key_exists('selectedBehavior', $data))
            $behavior = $data['selectedBehavior'];
        
        if($behavior == MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_SEND_TO_EMAIL) {
            $this->getElement('sendToEmail')->setRequired(true);
        } elseif ($behavior == MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_MOVE_TO_ACCOUNT) {
            $this->getElement('sendToAccount')->setRequired(true);
        }
        
        return parent::isValid($data);
    }
    
    public function addClientAccounts($clientId)
    {
        if (($accounts = MazelabVpopqmail_Model_DiFactory::getAccountManager()->getAccountsByOwner($clientId))){
            $this->getElement('sendToAccount')->addMultiOption("", self::ACCOUNTS_AVAILABLE);
            foreach($accounts as $accountId => $account) {
                $this->getElement('sendToAccount')->addMultiOption($accountId, $account->getEmail());
            }
        }else{
            $this->getElement('sendToAccount')->addMultiOption("", self::ACCOUNTS_NOT_AVAILABLE)
                                              ->setAttrib("disabled", "disabled");
        }

        return $this;
    }
    
}

