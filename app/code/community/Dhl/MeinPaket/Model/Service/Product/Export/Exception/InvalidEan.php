<?php

/**
 * Exception which is thrown when a product has an invalid EAN.
 *
 * @category   Mage
 * @package    Dhl_MeinPaket
 * @subpackage Model_Service_Product_Export_Exception_InvalidEan
 * @author     Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Service_Product_Export_Exception_InvalidEan extends Dhl_MeinPaket_Model_Exception_InvalidDataException {
	/**
	 * Constructor.
	 *
	 * @param integer $entityId        	
	 * @return void
	 */
	public function __construct($entityId) {
		parent::__construct ( $entityId, 'sku' );
	}
}
