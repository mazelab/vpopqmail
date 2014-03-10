<?php
/**
 * vpopqmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

class MazelabVpopqmail_Form_Validate_EmailUser extends Zend_Validate_Abstract
{

    const INVALID = 'invalid';
    const INVALID_BEGIN = 'invalidCharAtBegin';
    
    protected $_messageTemplates = array(
        self::INVALID => 'Value is not a valid local part for email address',
        self::INVALID_BEGIN => 'The characters .-,%&/are not allowed at the beginning'
    );
    
    /**
     * ALPHA / DIGIT / and "!", "#", "'", "+", "-", "=", "?", "^", "_", "`", "{", "}", "~"
     * 
     * @var string
     */
    protected $_chars = 'a-zA-Z0-9\x21\x23\x27\x2b\x2d\x3d\x3f\x5e\x5f\x60\x7b\x7d\x7e';

    /**
     * ".", %", "&", "-", "/"
     * 
     * @var string
     */
    protected $_disallow = '\x2e\x25\x26\x2d\x2f';
    
    public function isValid($value)
    {
        $result = $this->validate($value);

        if (!$result){
            return false;
        }
 
        return true;

    }

    /**
     * Dot-atom characters are: 1*atext *("." 1*atext)
     * 
     * @return boolean
     */
    protected function validate($value)
    {
        if(preg_match('/^['. $this->_disallow. ']/', $value)) {
            $this->_error(self::INVALID_BEGIN);
            return false;
        }
        
        $pattern = '/^[' . $this->_chars . ']+(\x2e+[' . $this->_chars . ']+)*$/';

        if(!preg_match($pattern, $value)) {
            $this->_error(self::INVALID);
            return false;
        }
        
        return true;
    }

}
