<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_ImportMailingList extends MazelabVpopqmail_Form_MailingList
{

    public function init()
    {
        parent::init();

        $this->addElement('text', 'status');
    }

    /**
     * sets content of mailing list targets
     * 
     * @param array $subscriber
     * @return MazelabVpopqmail_Form_ImportMailingList
     */
    public function setMailingListTargets(array $subscriber)
    {
        foreach($subscriber as $id => $target) {
            $this->getSubForm('subscriber')->addElement('text', (string) $id, array(
                'label' => 'E-mail address:',
                'readonly' => true,
                'validators' => array(
                    array('emailAddress')
                ),
                'value' => $target
            ));
        }
        
        return $this;
    }

}

