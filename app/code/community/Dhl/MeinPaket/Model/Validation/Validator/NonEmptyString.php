<?php

/**
 * Validator for non empty strings.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Validation_Validator
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Validation_Validator_NonEmptyString implements Dhl_MeinPaket_Model_Validation_ValidatorInterface {
	/**
	 * Validates the given value.
	 * Checks if the given has at least one character.
	 *
	 * @param mixed $value        	
	 * @return boolean
	 */
	public function isValid($value) {
		return (is_string ( $value ) && strlen ( $value ) > 0);
	}
}
