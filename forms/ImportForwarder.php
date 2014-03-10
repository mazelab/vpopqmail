<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_ImportForwarder extends MazelabVpopqmail_Form_AddForwarder 
{
    
    public function init()
    {
        parent::init();
        
        $this->addSubForm(new Zend_Form, 'forwardTo');
        $this->getSubForm('forwardTo')->setOptions(array('isArray' => true));
        
        $this->addElement('text', 'status');
    }
    
    public function addForwarderTargets(array $forwardTo)
    {
        foreach ($forwardTo as $key => $value) {
            $this->getSubForm('forwardTo')->addElement('text', (string) $key, array(
                'label' => 'E-mail address:',
                'value' => $value,
                'readOnly' => true,
                'validators' => array(
                    array('emailAddress')
                )
            ));
        }
    }

}