<?php

/**
 * MeinPaket payment method for imported orders.
 * 
 * @category	Mage
 * @package		Dhl_MeinPaket
 * @subpackage	Payment_Method
 */
class Dhl_MeinPaket_Model_Payment_Method_Meinpaket extends Mage_Payment_Model_Method_Abstract {
	/**
	 *
	 * @var string
	 */
	protected $_code = 'meinpaket';
	
	/**
	 *
	 * @var boolean
	 */
	protected $_canUseCheckout = false;
}
