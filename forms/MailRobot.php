<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_MailRobot extends Zend_Form
{

    public function init()
    {
        $this->addElement('text', 'user', array(
            'label' => 'mailingrobot for the account',
            'helper' => 'formTextAsSpan',
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
            'jsLabel' => 'Copy of the sent email to',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan',
            'validators' => array(
                'emailAddress'
            )
        ));
        
        $this->addElement('textarea', 'content', array(
            'required' => true,
            'label' => 'answer text',
            'class' => 'jsEditable textarea',
            'helper' => 'formTextAsSpan',
        ));
    }
    
    /**
     * sets domains select with given domain
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Form_MailRobot
     */
    public function setDomainSelect($domainId)
    {
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return $this;
        }
        
        $this->getElement('domainId')
                ->addMultiOption($domainId, $domain->getName())
                ->setValue($domainId)
                ->setAttrib("disabled", "disabled");
        
        return $this;
    }

}

