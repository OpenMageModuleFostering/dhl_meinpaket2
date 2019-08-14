<?php

/**
 * Common interface for all validator classes.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Validation
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
interface Dhl_MeinPaket_Model_Validation_ValidatorInterface {
	
	/**
	 * Validates the given value.
	 *
	 * @param mixed $value        	
	 * @return boolean
	 */
	public function isValid($value);
}
