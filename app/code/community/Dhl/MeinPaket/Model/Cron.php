<?php

/**
 * Cron handler for Dhl MeinPaket.
 *
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model
 */
class Dhl_MeinPaket_Model_Cron {
	const SYNC_CATALOG = 'meinpaket_sync_catalog';
	const SYNC_ORDERS = 'meinpaket_sync_orders';
	const SYNC_ASYNC = 'meinpaket_sync_async';
	public static $CRONJOBS = array (
			self::SYNC_CATALOG,
			self::SYNC_ORDERS,
			self::SYNC_ASYNC 
	);
	
	/**
	 * Called to synchronize catalog.
	 *
	 * @return NULL
	 */
	public function exportProducts() {
		try {
			return Mage::getSingleton ( 'meinpaket/service_product_export' )->exportProducts ();
		} catch ( Exception $e ) {
			Mage::logException ( $e );
		}
		return null;
	}
	
	/**
	 * Called to download orders.
	 *
	 * @return NULL
	 */
	public function importOrders() {
		try {
			return Mage::getSingleton ( 'meinpaket/service_order_importService' )->importOrders ();
		} catch ( Exception $e ) {
			Mage::logException ( $e );
		}
		return null;
	}
	
	/**
	 * Called to download responses for async requests.
	 *
	 * @return NULL
	 */
	public function processAsyncTasks() {
		try {
			return Mage::getSingleton ( 'meinpaket/service_async' )->process ();
		} catch ( Exception $e ) {
			Mage::logException ( $e );
		}
		return null;
	}
	
	/**
	 * Called to request best prices.
	 *
	 * @return NULL
	 */
	public function getBestPrice() {
		try {
			return Mage::getSingleton ( 'meinpaket/service_productData_requestService' )->requestBestPrices ();
		} catch ( Exception $e ) {
			Mage::logException ( $e );
		}
		return null;
	}
	
	/**
	 * Called to request best prices.
	 *
	 * @return NULL
	 */
	public function cleanup() {
		try {
			// Delete all log entries older than 40 days
			$date = Zend_Date::now ();
			$date->subDay ( 40 );
			
			$logCollection = Mage::getModel ( 'meinpaket/log' )->getCollection ();
			$logCollection->addFieldToFilter ( 'createdAt', array (
					'to' => $date->toString ( 'YYYY-MM-dd' ) 
			) );
			foreach ( $logCollection as $log ) {
				$log->delete ();
			}
		} catch ( Exception $e ) {
			Mage::logException ( $e );
		}
		return null;
	}
}