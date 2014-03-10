<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_Validate_SpecialsCatchAll extends Zend_Validate_Abstract
{
    const CATCHALL_EXISTS = 'catchAllAlreadyUsed';
    const DOMAINID_NOT_EXIST = 'domainIdNotExist';

    protected $_messageTemplates = array(
        self::CATCHALL_EXISTS => 'Catch-all already exists',
        self::DOMAINID_NOT_EXIST => 'DomainId not given'
    );

    /**
     * Returns true if and only if $value meets the validation requirements
     * 
     * @param string $value type of special
     * @param mixed $context form context elements
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if ($context == null){
            return false;
        }

        if(!array_key_exists('domainId', $context)){
            $this->_error(self::DOMAINID_NOT_EXIST);

            return false;
        }

        return $this->validate($value, $context["domainId"]);
    }

    /**
     * Returns true if and only if $type meets the validation requirements
     * 
     * @param string $type type of special
     * @param string $domainId
     * @return boolean
     */
    protected function validate($type, $domainId)
    {
        $catchAllManager = MazelabVpopqmail_Model_DiFactory::getCatchAllManager();

        if ($type == MazelabVpopqmail_Model_SpecialsManager::CATCH_ALL){
            if ($catchAllManager->getCatchAllByDomain($domainId) instanceof MazelabVpopqmail_Model_ValueObject_CatchAll){
                $this->_error(self::CATCHALL_EXISTS);

                return false;
            }
        }

        return true;
    }

}
