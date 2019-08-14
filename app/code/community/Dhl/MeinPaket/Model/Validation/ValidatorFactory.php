<?php

/**
 * Factory for validators.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Validation
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Validation_ValidatorFactory extends Varien_Object {
	/**
	 *
	 * @var string
	 */
	protected $packagePrefix = 'meinpaket/Validation_Validator_';
	
	/**
	 *
	 * @return Dhl_MeinPaket_Model_Validation_ValidatorChain
	 */
	public function createValidatorChain() {
		return Mage::getModel ( 'meinpaket/Validation_ValidatorChain' );
	}
	
	/**
	 *
	 * @return Dhl_MeinPaket_Model_Validation_Validator_NonEmptyString
	 */
	public function createNonEmptyStringValidator() {
		return Mage::getModel ( $this->packagePrefix . 'NonEmptyString' );
	}
	
	/**
	 *
	 * @return Dhl_MeinPaket_Model_Validation_Validator_Ean
	 */
	public function createEanValidator() {
		return Mage::getModel ( $this->packagePrefix . 'Ean' );
	}
	
	/**
	 *
	 * @return Dhl_MeinPaket_Model_Validation_Validator_IntGreaterZero
	 */
	public function createIntGreaterZeroValidator() {
		return Mage::getModel ( $this->packagePrefix . 'IntGreaterZero' );
	}
	
	/**
	 *
	 * @return Dhl_MeinPaket_Model_Validation_Validator_CDATAContent
	 */
	public function createCDATAContentValidator() {
		return Mage::getModel ( $this->packagePrefix . 'CDATAContent' );
	}
	
	/**
	 *
	 * @return Dhl_MeinPaket_Model_Validation_Validator_Url
	 */
	public function createUrlValidator() {
		return Mage::getModel ( $this->packagePrefix . 'Url' );
	}
	
	/**
	 *
	 * @param integer $maxLength        	
	 * @return Dhl_MeinPaket_Model_Validation_Validator_StringMaxLength
	 */
	public function createStringMaxLengthValidator($maxLength = Dhl_MeinPaket_Model_Validation_Validator_StringMaxLength::LENGTH_INFINITE) {
		return Mage::getModel ( $this->packagePrefix . 'StringMaxLength' )->setMaxLength ( $maxLength );
	}
}

