<?php
class Dhl_MeinPaket_Model_Async extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'meinpaket/async' );
	}
}