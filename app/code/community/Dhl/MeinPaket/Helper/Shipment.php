<?php

/**
 * Shipment model related helper methods.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Util
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Helper_Shipment extends Mage_Core_Helper_Abstract {
	/**
	 * Tells if the given order is an order that was imported from MeinPaket.de.
	 *
	 * @param Mage_Sales_Model_Order_Shipment $shipment
	 *        	to check
	 * @return boolean
	 */
	public function isMeinPaketShipment(Mage_Sales_Model_Order_Shipment $shipment) {
		return Mage::helper ( 'meinpaket/order' )->isMeinPaketOrder ( $shipment->getOrder () );
	}
	
	/**
	 * Tells if the given $shipment is exported to MeinPaket
	 *
	 * @param Mage_Sales_Model_Order_Shipment $shipment
	 *        	to be shipped
	 * @return boolean
	 */
	public function isExportedToDhlMeinPaket(Mage_Sales_Model_Order_Shipment $shipment) {
		return $shipment->hasData ( 'shipment_was_exported_for_dhl_mein_paket' ) && $shipment->getData ( 'shipment_was_exported_for_dhl_mein_paket' );
	}
}
