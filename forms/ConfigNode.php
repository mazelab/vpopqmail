<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_ConfigNode extends Zend_Form
{
    
    /**
     * @var array
     */
    protected $_behaviorOptions = array(
        MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_DELETE => '',
        MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_SEND_TO_EMAIL => '',
    );
    
    public function __construct()
    {
        
        $this->addElement('radio', 'selectedBehavior', array(
            'multiOptions' => $this->_behaviorOptions
        ));
        $this->addElement('text', 'sendToEmail', array(
            'validators' => array(
                array('EmailAddress')
            ),
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
        
        if(key_exists('selectedBehavior', $data)) {
            $behavior = $data['selectedBehavior'];
        }
        
        if($behavior == MazelabVpopqmail_Model_CatchAllManager::CATCH_ALL_BEHAVIOR_SEND_TO_EMAIL) {
            $this->getElement('sendToEmail')->setRequired(true);
        }
        
        return parent::isValid($data);
    }
    
}
