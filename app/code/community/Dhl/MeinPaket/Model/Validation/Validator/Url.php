<?php

/**
 * Validator for URLs.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Validation_Validator
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Validation_Validator_Url implements Dhl_MeinPaket_Model_Validation_ValidatorInterface {
	/**
	 * Validates an URL.
	 *
	 * @param mixed $value        	
	 * @return boolean
	 */
	public function isValid($value) {
		return (substr ( $value, 0, 4 ) === 'http');
	}
}
