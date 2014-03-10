<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_ImportCatchAll extends MazelabVpopqmail_Form_CatchAll
{
    public function init()
    {
        parent::init();
        
        $this->addElement('text', 'status');
    }

}

