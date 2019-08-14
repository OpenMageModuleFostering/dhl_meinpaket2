<?php

/**
 * Order model related helper methods.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Util
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Helper_Order extends Mage_Core_Helper_Abstract {
	/**
	 * Tells if the given order is an order that was imported from MeinPaket.de.
	 *
	 * @param Mage_Sales_Model_Order $order        	
	 * @return boolean
	 */
	public function isMeinPaketOrder(Mage_Sales_Model_Order $order) {
		return ($order->hasData ( 'dhl_mein_paket_order_id' ) && $order->getData ( 'dhl_mein_paket_order_id' ));
	}
}
