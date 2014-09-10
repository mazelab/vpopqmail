<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_DiffForwarder extends Zend_Form
{
    
    public function init()
    {
        $this->addSubForm(new Zend_Form(), 'local');
        $this->addSubForm(new Zend_Form(), 'remote');
    }

    /**
     * @return MazelabVpopqmail_Form_DiffForwarder
     */
    public function reset()
    {
        foreach ($this->getSubForms() as $subForm) {
            $subForm->reset();
        }
        $this->init();

        return $this;
    }

    /**
     * @param string $forwarderId
     * @return MazelabVpopqmail_Form_DiffForwarder
     */
    public function initDiff($forwarderId)
    {
        if (!($forwarder = MazelabVpopqmail_Model_DiFactory::getForwarderManager()->getForwarder($forwarderId))){
            return false;
        }

        $raw = $forwarder->getBean()->asArray(true);
        if(!array_key_exists('forwardTo', $raw) || !($targets = $raw['forwardTo'])) {
            return $this;
        }
        
        foreach ($targets as $id => $target) {
            $elementLocal = $this->createElement("checkbox", $id, array(
                'label' => $target["local"],
                'belongsTo' => 'forwardTo',
                'checkedValue' => $target["local"],
                'checked' => true
            ));
            $elementRemote = $this->createElement("checkbox", $id, array(
                'label' => $target["remote"],
                'belongsTo' => 'forwardTo',
                'checkedValue' => $target["remote"]
            ));

            if (empty($target["local"]) || empty($target["remote"])){
                if (empty($target["local"])) {
                  $elementLocal->removeDecorator("ViewHelper");
                }else if (empty($target["remote"])) {
                    $elementRemote->removeDecorator("ViewHelper");
                }
                $elementLocal->getDecorator("HtmlTag")->setOption("status", "del");
                $elementRemote->getDecorator("HtmlTag")->setOption("status", "del");
            }else if ($target["local"] == $target["remote"]) {
                $elementLocal->setDecorators(array(array('Label')));
                $elementRemote->setDecorators(array(array('Label')));
            }
 
            $this->getSubForm("local")->addElement($elementLocal);
            $this->getSubForm("remote")->addElement($elementRemote);
        }
        
        return $this;
    }

}

