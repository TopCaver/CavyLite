<?php
/**
 * Cavy Lite Framework 
 * 
 * Cavy validator manager
 * Cavy 验证器
 *
 * @author     TopCaver
 * @copyright  Copyright (c) 2013 TopCaver
 * @license    New BSD License
 * @version    $Id$
 */

class Cavy_Controller_Validator {

	private $_controller = null;
	
	public function __construct(&$controller) {
		$this->_controller = $controller;
	}

	/***************************************************************
	 * Validator Methods
	 * @return boolean 
	 ***************************************************************/

	public function validatePresenceOf($field, $data, $message = null) {
		$value = $data[$field];
		if ($value == null || $value == '') {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	public function validateFieldsEquals($field1,$field2,$data,$message=null) {
		if ($data[$field1] != $data[$field2]) {
			$this->addFieldError($field2, $message);
			return false;
		}
		return true;
	}
	
	public function validateEquals($field, $data, $value, $message=null) {
		if ($data[$field] != $value) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	public function validateFormatOf($field, $data, $reg, $message = null) {
		if (preg_match($reg, $data[$field]) == false) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}

	public function validateDigits($field, $data, $message = null, $addError=false) {
		$value = $data[$field];
		if (!ctype_digit((string) $value)) {
			if($addError) $this->addFieldError($field, $message);	
			return false;
		}
		return true;
	}

	public function validateFloat($field, $data, $params, $message = null) {
		if (preg_match('/^[0-9]+\.[0-9]*$/', $data[$field]) == false) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	/**
	 * @param params array('min'=>?,'max'=>?)
	 */
	public function validateDigitsRange($field, $data, $params, $message = null) {
		$value = $data[$field];
		if (!ctype_digit((string) $value)) {
			$this->addFieldError($field, $message);
			return false;
		}
		$intval = intval($value);
		extract($params);
		if (isset ($min) && $intval < intval($min)) {
			$this->addFieldError($field, $message);
			return false;
		}
		if (isset ($max) && $intval >= intval($max)) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	public function validateIp($field, $data, $message=null, $addError=false) {
		$value = $data[$field];
		if (!((bool) ip2long($value))) {
			if ($addError) $this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	public function validateIpmask($field, $data, $message=null, $addError=false) {
		$value = $data[$field];
		$regexLocal = '/^((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])(\/([0-9]{1}|[0-2][0-9]|3[0-2]))$/';
		if (!preg_match($regexLocal, $value)) {
			if ($addError) $this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	public function validateIp6($field, $data, $message=null, $addError=false) {
		$value = $data[$field];
		$regexLocal = '/^((([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4})|(:((:[0-9a-fA-F]{1,4}){1,6}|:))|([0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,5}|:))|(([0-9a-fA-F]{1,4}:){2}((:[0-9a-fA-F]{1,4}){1,4}|:))|(([0-9a-fA-F]{1,4}:){3}((:[0-9a-fA-F]{1,4}){1,3}|:))|(([0-9a-fA-F]{1,4}:){4}((:[0-9a-fA-F]{1,4}){1,2}|:))|(([0-9a-fA-F]{1,4}:){5}:([0-9a-fA-F]{1,4})?)|(([0-9a-fA-F]{1,4}:){6}:))(\/([0][0-9]{1,2}|[0-9]{1,2}|1[0-1][0-9]|12[0-8]))?$/';
		if (!preg_match($regexLocal, $value)) {
			if ($addError) $this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
		
	public function validateHost($field, $data, $message=null) {
        $host = $data[$field];
        $regexLocal = "/^[a-zA-Z0-9\-\_\.]+$/";
		if (!preg_match($regexLocal, $host)) {
        	$this->addFieldError($field, $message);
        	return false;
		}
		return true;
	}

	/**
	 * @param params array('min'=>?,'max'=>?)
	 */
	public function validateFloatRange($field, $data, $params, $message = null) {
		$value = $data[$field];
		if (preg_match('/^[0-9]+\.?[0-9]*$/', $value) == false) {
			$this->addFieldError($field, $message);
			return false;
		}
		$floatval = floatval($value);
		extract($params);
		if (isset ($min) && $floatval < floatval($min)) {
			$this->addFieldError($field, $message);
			return false;
		}
		if (isset ($max) && $floatval >= floatval($max)) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	/**
     * Validate value if every character is alphabetic
     *
     * @param mixed $value
     */
	public function validateAlpha($field, $data, $message=null) {
		$value = $data[$field];
		if (!ctype_alpha($value)) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	public function validateLengthOf($field, $data, $min, $max, $message=null) {
		$len = strlen($data[$field]);
		if ($min != null && $len < $min) {
			$this->addFieldError($field, $message);
			return false;
		}
		if ($max != null && $len >= $max) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	public function validateEmail($field,$data,$message=null) {
		$value = $data[$field];
		if (preg_match('/^([a-zA-Z0-9_\.-])+@([a-zA-Z0-9_-]+\.)+[a-zA-Z0-9]+$/', $value) == false) {
			$this->addFieldError($field, $message);
			return false;
		}
		return true;
	}
	
	
	/**
	 * 	validate Illegal Chars
	 */
	public function validateIllegalChars($field,$data,$message=null,$addError=false) {
		$value = $data[$field];
		if (preg_match('/^([ a-zA-Z0-9\x{0391}-\x{ffe5}\~!@#\$\^\*\(\)\-_\{\}\|:;\/\.,])+$/u', $value) == false) {
			if($addError){ 
				$this->addFieldError($field, $message);
			}
			return false;
		}
		return true;
	}
    
	/**
     * Validate if every character is alphabetic or a digit
     *
     * @param mixed $value
     */
    public function validateAlnum($field,$data,$message=null) {
        if (!ctype_alnum($data[$field])) {
			$this->addFieldError($field, $message);
			return false;
        }
		return true;
    }
    
	public function addFieldError($field, $message=null) {
		$this->_controller->addFieldError($field, $message);
	}
	
	public function addError($message) {
		$this->_controller->addError($message);
	}
}
?>
