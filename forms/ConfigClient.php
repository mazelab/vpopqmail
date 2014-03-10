<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_ConfigClient extends Zend_Form
{
    private $_standardDecorators = array(
        'ViewHelper',
    );
    
    public function init()
    {
        $this->addElement('text', 'quota', array(
            'jsLabel' => 'mail quota in MB',
            'label' => 'mail quota in MB',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan',
            'validators' => array(
                array('digits')
            )
        ));
        $this->addElement('text', 'countAccounts', array(
            'jsLabel' => 'number of email accounts',
            'label' => 'number of email accounts',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan',
            'validators' => array(
                array('digits')
            )
        ));

        $this->setElementDecorators($this->_standardDecorators);
    }
    
}

