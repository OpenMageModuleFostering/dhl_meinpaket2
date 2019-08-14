<?php

/**
 * Default helper for the Dhl_Postpay package.
 * 
 * @category    Dhl
 * @package     Dhl_Postpay
 * @subpackage  Helper
 */
class Dhl_Postpay_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getExtensionVersion() {
		return ( string ) Mage::getConfig ()->getModuleConfig ( 'Dhl_Postpay' )->version;
	}
}
