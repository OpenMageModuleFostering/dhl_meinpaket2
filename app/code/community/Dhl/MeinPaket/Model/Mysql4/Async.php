<?php
class Dhl_MeinPaket_Model_Mysql4_Async extends Mage_Core_Model_Mysql4_Abstract {
	/**
	 * Initialize domain model and set primary key
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init ( 'meinpaket/async', 'async_id' );
	}
}