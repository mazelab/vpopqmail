<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_ImportMailRobot extends MazelabVpopqmail_Form_MailRobot
{
    public function init()
    {
        parent::init();

        foreach ($this->getElements() as $element) {
            $element->setOptions(array("helper" => null));
        }

        $this->addElement('text', 'status');
    }

}

