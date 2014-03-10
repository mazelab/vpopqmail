<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_ConfigDomain extends Zend_Form
{

    /**
     * @var array
     */
    private $_standardDecorators = array(
        'ViewHelper'
    );
    
    CONST NODE_NOT_ASSIGNED = "Not assigned";
    
    public function __construct()
    {
        $this->addElement("text", "incomingMailServer", array(
            "label" => "incoming mail server",
            "jsLabel" => "incoming mail server",
            "class" => "jsEditable",
            "helper" => "formTextAsSpan"
        ));
        $this->addElement("text", "outgoingMailServer", array(
            "label" => "outgoing mail server",
            "jsLabel" => "outgoing mail server",
            "class" => "jsEditable",
            "helper" => "formTextAsSpan"
        ));
        
        $this->addElement('select', 'nodes', array(
            'class' => 'selectpicker show-menu-arrow show-tick dropup'
        ));
        
        $this->setElementDecorators($this->_standardDecorators);
    }
    
    /**
     * builds node select from module
     * 
     * sets only available nodes
     * 
     * @param string $domainId
     * @return MazelabVpopqmail_Form_ConfigDomain
     */
    public function setNodeSelect($domainId)
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $nodeManager = MazelabVpopqmail_Model_DiFactory::getNodeManager();
        $nodeElement = $this->getElement('nodes');

        $domain = $domainManager->getDomain($domainId);
        $owner = $domain->getOwner();

        if($owner->hasService(MazelabVpopqmail_Model_ConfigManager::MODULE_NAME)) {
            foreach($nodeManager->getEmailNodes() as $nodeId => $node) {
                $nodeElement->addMultiOption($nodeId, $node->getName() . ' ' . $node->getIp());
            }
        }
        
        $nodeElement->addMultiOption('', self::NODE_NOT_ASSIGNED);
        if (count($nodeElement->getMultiOptions())){
            $node = $nodeManager->getNodeOfDomain($domainId);
            
            if($node) {
                $nodeElement->setValue($node->getId());
            } else {
                $nodeElement->setValue("");
            }
        } else {
            $nodeElement->setAttrib("disabled", "disabled");
        }
        
        return $this;
    }

}
