<?php

/**
 * Observer for all events the DHL MeinPaket extension has to catch.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Observer {
	/**
	 * Stock attributes.
	 *
	 * @var array
	 */
	private $STOCK_ATTRIBUTES = array (
			'qty',
			'manage_stock',
			'is_in_stock',
			'min_qty',
			'min_sale_qty',
			'max_sale_qty' 
	);
	
	/**
	 * Holds the id of the order whose save_after event is actually triggered.
	 *
	 * @var integer
	 */
	protected static $currentlySavedOrderId = - 1;
	
	/**
	 * Tells if the save_after has been triggered twice or more on
	 * the same order during script lifetime.
	 *
	 * @var boolean
	 */
	protected static $isSameSavedOrder = false;
	
	/**
	 * Sets the id of the order whose save_after event has been triggered.
	 *
	 * @param integer $orderId        	
	 * @return void
	 */
	protected static function setCurrentOrderId($orderId) {
		if (self::$currentlySavedOrderId === $orderId) {
			self::$isSameSavedOrder = true;
		} else {
			self::$isSameSavedOrder = false;
		}
		self::$currentlySavedOrderId = $orderId;
	}
	
	/**
	 * Tells if the save_after event of the current order has been triggered
	 * at least once during the lifecycle of the current script.
	 *
	 * @return boolean
	 */
	protected static function saveEventHasBeenTriggeredOnSameOrderBefore() {
		return self::$isSameSavedOrder;
	}
	
	/**
	 * Triggered when product is duplicated.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function productDuplicate(Varien_Event_Observer $observer) {
		$newProduct = $observer->getEvent ()->getNewProduct ();
		$newProduct->setData ( 'meinpaket_id', '' );
		$newProduct->setData ( 'meinpaket_export', 0 );
		return $this;
	}
	
	/**
	 * Is triggered when an order is saved.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function salesOrderSaveAfter(Varien_Event_Observer $observer) {
		$order = $observer->getData ( 'order' );
		
		self::setCurrentOrderId ( $order->getId () );
		
		$this->_updateMeinPaketOrderId ( $order );
		$this->_handleOrderItemsChange ( $order );
		$this->_cancelMeinPaketOrder ( $order );
		
		return $this;
	}
	
	/**
	 * Sets the dhl_mein_paket_order_id for the given order.
	 * This is necessary if an order is edit and so created a new one.
	 * The new one will not have it's dhl_mein_paket_order_id attribute
	 * automatically.
	 *
	 * @param Varien_Object $order        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	protected function _updateMeinPaketOrderId($order) {
		// return if the order doesn't have a parent order
		// or if it already has a MeinPaket.de order id...
		if (! $order->hasData ( 'relation_parent_id' ) || $order->hasData ( 'dhl_mein_paket_order_id' )) {
			return;
		}
		
		/* @var $parentOrder Mage_Sales_Model_Order */
		$parentOrder = Mage::getModel ( 'sales/order' )->load ( $order->getData ( 'relation_parent_id' ) );
		
		if (! $parentOrder->hasData ( 'relation_parent_id' ) || $parentOrder->hasData ( 'dhl_mein_paket_order_id' )) {
			$order->setData ( 'dhl_mein_paket_order_id', $parentOrder->getDhlMeinPaketOrderId () );
			$parentOrder->setData ( 'dhl_mein_paket_order_id', '' )->getResource ()->saveAttribute ( $parentOrder, 'dhl_mein_paket_order_id' );
		}
		
		return $this;
	}
	
	/**
	 * Cancels single order items.
	 *
	 * @param Mage_Sales_Model_Order $order        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	protected function _handleOrderItemsChange(Mage_Sales_Model_Order $order) {
		if (self::saveEventHasBeenTriggeredOnSameOrderBefore ()) {
			return;
		}
		
		if ($order->getData ( 'state' ) === Mage_Sales_Model_Order::STATE_CANCELED) {
			return $this;
		}
		
		if (! $order->hasData ( 'relation_parent_id' )) {
			return $this;
		}
		
		/* @var $orderHelper Dhl_MeinPaket_Helper_Order */
		$orderHelper = Mage::helper ( 'meinpaket/order' );
		
		/* @var $parentOrder Mage_Sales_Model_Order */
		$parentOrder = Mage::getModel ( 'sales/order' )->load ( $order->getData ( 'relation_parent_id' ) );
		
		if ($orderHelper->isMeinPaketOrder ( $order )) {
			
			$orderItemCollection = $order->getItemsCollection ();
			$parentItemCollection = $parentOrder->getItemsCollection ();
			$items = array ();
			$parentItems = array ();
			$reduceList = array ();
			$submitChanges = false;
			
			foreach ( $orderItemCollection as $item ) {
				$options = $item->getProductOptions ();
				if (isset ( $options ['info_buyRequest'] ) && isset ( $options ['info_buyRequest'] ['dhl_mein_paket_item_id'] )) {
					$items [$options ['info_buyRequest'] ['dhl_mein_paket_item_id']] = array (
							'qty' => ( integer ) $item->getQtyOrdered (),
							'productId' => $item->getProductId () 
					);
				}
			}
			foreach ( $parentItemCollection as $item ) {
				$options = $item->getProductOptions ();
				if ($item->getQtyCanceled () != $item->getQtyOrdered ()) {
					$submitChanges = true;
				}
				if (isset ( $options ['info_buyRequest'] ) && isset ( $options ['info_buyRequest'] ['dhl_mein_paket_item_id'] )) {
					$parentItems [$options ['info_buyRequest'] ['dhl_mein_paket_item_id']] = array (
							'qty' => ( integer ) $item->getQtyOrdered (),
							'productId' => $item->getProductId () 
					);
				}
			}
			
			if ($submitChanges === false) {
				return $this;
			}
			
			// find items with lowered quantity
			if (sizeof ( $items ) < sizeof ( $parentItems )) {
				foreach ( $parentItems as $dhlItemId => $item ) {
					if (! array_key_exists ( $dhlItemId, $items )) {
						$reduceList [$dhlItemId] = array (
								'qty' => $item ['qty'],
								'productId' => $item ['productId'] 
						);
					} elseif ($items [$dhlItemId] ['qty'] < $item ['qty']) {
						$reduceList [$dhlItemId] = array (
								'qty' => $item ['qty'] - $items [$dhlItemId] ['qty'],
								'productId' => $item ['productId'] 
						);
					}
				}
			} else {
				foreach ( $items as $dhlItemId => $item ) {
					if (array_key_exists ( $dhlItemId, $parentItems ) && $item ['qty'] < $parentItems [$dhlItemId] ['qty']) {
						$reduceList [$dhlItemId] = array (
								'qty' => $parentItems [$dhlItemId] ['qty'] - $item ['qty'],
								'productId' => $item ['productId'] 
						);
					}
				}
			}
			
			if (sizeof ( $reduceList ) > 0) {
				// make request
				/* @var $cancellationService Dhl_MeinPaket_Model_Service_Order_CancellationService */
				$cancellationService = Mage::getModel ( 'meinpaket/service_order_cancellationService' );
				$result = null;
				$helper = Mage::helper ( 'meinpaket/data' );
				$errMsg = '';
				
				try {
					$result = $cancellationService->cancelOrderItems ( $order, $reduceList );
				} catch ( Dhl_MeinPaket_Model_Xml_XmlBuildException $e ) {
					Mage::logException ( $e );
					$errMsg .= $helper->__ ( 'Request could not be built.' );
				} catch ( Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException $e ) {
					Mage::logException ( $e );
					$errMsg .= sprintf ( $helper->__ ( 'MeinPaket.de server returned HTPP code %s.' ), $e->getHttpReturnCode () );
				} catch ( Dhl_MeinPaket_Model_Client_HttpTimeoutException $e ) {
					Mage::logException ( $e );
					$errMsg .= $helper->__ ( 'Connection to MeinPaket.de server timed out.' );
				} catch ( Dhl_MeinPaket_Model_Xml_InvalidXmlException $e ) {
					Mage::logException ( $e );
					$errMsg .= $helper->__ ( 'Invalid response from MeinPaket.de server.' );
				} catch ( Exception $e ) {
					Mage::logException ( $e );
					$errMsg .= $helper->__ ( 'Unknown error.' );
				}
				if (is_object ( $result ) && $result->hasError ()) {
					$errMsg .= sprintf ( $helper->__ ( 'MeinPaket.de server returned error code %s.' ), $result->getError () );
				}
				
				if (strlen ( $errMsg ) > 0) {
					Mage::getSingleton ( 'adminhtml/session' )->addError ( $helper->__ ( 'Error on tranfering cancelled items to MeinPaket.de.' ) . ' (' . $errMsg . ')' );
					throw new Exception ( 'Failed transfering cancelled items to MeinPaket.de server.' );
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Cancels the given order in MeinPaket.de if the given order is a MeinPaket.de order
	 * and its state is Mage_Sales_Model_Order::STATE_CANCELED.
	 *
	 * @param Mage_Sales_Model_Order $order        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	protected function _cancelMeinPaketOrder(Mage_Sales_Model_Order $order) {
		$meinPaketOrderId = $order->getData ( 'dhl_mein_paket_order_id' );
		
		if (strlen ( $meinPaketOrderId ) > 1 && $order->getData ( 'state' ) === Mage_Sales_Model_Order::STATE_CANCELED) {
			
			$col = Mage::getModel ( 'sales/order' )->getCollection ()->addAttributeToFilter ( 'relation_parent_id', $order->getId () )->load ();
			
			if ($col->count () > 0) {
				return $this;
			}
			
			// TODO: Class missing
			
			/* @var $orderCancellationService Dhl_MeinPaket_Model_Service_Order_CancellationService */
			$orderCancellationService = Mage::getModel ( 'meinpaket/service_order_cancellationService' );
			
			$result = null;
			$helper = Mage::helper ( 'meinpaket/data' );
			$errMsg = '';
			
			try {
				$result = $orderCancellationService->cancelOrder ( $order );
			} catch ( Dhl_MeinPaket_Model_Xml_XmlBuildException $e ) {
				Mage::logException ( $e );
				$errMsg .= $helper->__ ( 'Request could not be built.' );
			} catch ( Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException $e ) {
				Mage::logException ( $e );
				$errMsg .= sprintf ( $helper->__ ( 'MeinPaket.de server returned HTPP code %s.' ), $e->getHttpReturnCode () );
			} catch ( Dhl_MeinPaket_Model_Client_HttpTimeoutException $e ) {
				Mage::logException ( $e );
				$errMsg .= $helper->__ ( 'Connection to MeinPaket.de server timed out.' );
			} catch ( Dhl_MeinPaket_Model_Xml_InvalidXmlException $e ) {
				Mage::logException ( $e );
				$errMsg .= $helper->__ ( 'Invalid response from MeinPaket.de server.' );
			} catch ( Exception $e ) {
				Mage::logException ( $e );
				$errMsg .= $helper->__ ( 'Unknown error.' );
			}
			if (is_object ( $result ) && $result->hasError ()) {
				$errMsg .= sprintf ( $helper->__ ( 'MeinPaket.de server returned error code %s.' ), $result->getError () );
			}
			
			if (strlen ( $errMsg ) > 0) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $helper->__ ( 'Error on tranfering cancelled items to MeinPaket.de.' ) . ' (' . $errMsg . ')' );
				throw new Exception ( 'Failed transfering cancelled items to MeinPaket.de server.' );
			}
		}
		
		return $this;
	}
	
	/**
	 * Is triggered when a shipment is saved.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer) {
		/* @var $order Dhl_MeinPaket_Helper_Shipment */
		$shipmentHelper = Mage::helper ( 'meinpaket/shipment' );
		
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment = Mage::getModel ( 'sales/order_shipment' )->load ( $observer->getData ( 'shipment' )->getId () );
		
		if ($shipmentHelper->isExportedToDhlMeinPaket ( $shipment )) {
			return $this;
		}
		
		if (! $shipmentHelper->isMeinPaketShipment ( $shipment )) {
			return $this;
		}
		
		// send shipment request to MeinPaket.
		$this->_exportShipmentToMeinPaket ( $shipment );
		
		return $this;
	}
	
	/**
	 * Is called after a shipment save is committed.
	 * Sets the shipment's "shipment_was_exported_for_dhl_mein_paket" attribute
	 * value to "1" if it isn't set yet.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function salesOrderShipmentSaveCommitAfter(Varien_Event_Observer $observer) {
		/* @var $order Dhl_MeinPaket_Helper_Shipment */
		$shipmentHelper = Mage::helper ( 'meinpaket/shipment' );
		
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment = $observer->getShipment ();
		
		if ($shipmentHelper->isExportedToDhlMeinPaket ( $shipment )) {
			return $this;
		}
		
		if (! $shipmentHelper->isMeinPaketShipment ( $shipment )) {
			return $this;
		}
		
		if ($shipmentHelper->isMeinPaketShipment ( $shipment ) && ! $shipment->getData ( 'shipment_was_exported_for_dhl_mein_paket' )) {
			$shipment->setData ( 'shipment_was_exported_for_dhl_mein_paket', 1 )->getResource ()->saveAttribute ( $shipment, 'shipment_was_exported_for_dhl_mein_paket' );
		}
	}
	
	/**
	 * Exports an saved shipment to MeinPaket.de.
	 *
	 * @param Mage_Sales_Model_Order_Shipment $shipment        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	protected function _exportShipmentToMeinPaket(Mage_Sales_Model_Order_Shipment $shipment) {
		/* @var $shipmentExportService Dhl_MeinPaket_Model_Service_Order_ShipmentExportService */
		$shipmentExportService = Mage::getModel ( 'meinpaket/service_order_shipmentExportService' );
		
		$result = null;
		$errMsg = '';
		$helper = Mage::helper ( 'meinpaket/data' );
		
		try {
			$result = $shipmentExportService->exportShipment ( $shipment );
		} catch ( Dhl_MeinPaket_Model_Xml_XmlBuildException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Request could not be built.' );
		} catch ( Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException $e ) {
			Mage::logException ( $e );
			$errMsg .= sprintf ( $helper->__ ( 'MeinPaket.de server returned HTPP code %s.' ), $e->getHttpReturnCode () );
		} catch ( Dhl_MeinPaket_Model_Client_HttpTimeoutException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Connection to MeinPaket.de server timed out.' );
		} catch ( Dhl_MeinPaket_Model_Xml_InvalidXmlException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Invalid response from MeinPaket.de server.' );
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Unknown error.' );
		}
		
		if ($result == null || $result->hasErrors ()) {
			$errMsg .= $helper->__ ( 'Shipment has not been accepted by MeinPaket.de.' );
		}
		
		if (strlen ( $errMsg ) > 0) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $helper->__ ( 'Error on transfering shipment to MeinPaket.de.' ) . ' (' . $errMsg . ')' );
			throw new Exception ( 'Failed exporting shipment to MeinPaket.de server.' );
		}
		
		return $this;
	}
	
	/**
	 * Is called when a creditmemo is saved.
	 * If the creditmemo is refunded, a notification will be send to MeinPaket.de.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function salesOrderCreditmemoSaveAfter(Varien_Event_Observer $observer) {
		/* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
		$creditmemo = $observer->getData ( 'creditmemo' );

		// check if creditmemo is refunded
		if ($creditmemo->getState () != Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED) {
			return $this;
		}

		// check if the order is a MeinPaket.de order
		if (! Mage::helper('meinpaket/order')->isMeinPaketOrder ( $creditmemo->getOrder () )) {
			return $this;
		}
		
		/* @var $result Dhl_MeinPaket_Model_Sevice_RefundExport_Result */
		$result = null;
		
		/* @var $exportService Dhl_MeinPaket_Model_Sevice_RefundExportService */
		$exportService = Mage::getModel ( 'meinpaket/service_order_refundExportService' );
		
		$helper = Mage::helper ( 'meinpaket/data' );
		$errMsg = '';
		
		try {
			$result = $exportService->exportRefund ( $creditmemo );
		} catch ( Dhl_MeinPaket_Model_Xml_XmlBuildException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Request could not be built.' );
		} catch ( Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException $e ) {
			Mage::logException ( $e );
			$errMsg .= sprintf ( $helper->__ ( 'MeinPaket.de server returned HTPP code %s.' ), $e->getHttpReturnCode () );
		} catch ( Dhl_MeinPaket_Model_Client_HttpTimeoutException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Connection to MeinPaket.de server timed out.' );
		} catch ( Dhl_MeinPaket_Model_Xml_InvalidXmlException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Invalid response from MeinPaket.de server.' );
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Unknown error.' );
		}
		
		if ($result == null) {
			$errMsg .= $helper->__ ( 'Refund has not been accepted by MeinPaket.de.' );
		}
		
		if (strlen ( $errMsg ) > 0) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $helper->__ ( 'Error on transfering refund to MeinPaket.de.' ) . ' (' . $errMsg . ')' );
			throw new Exception ( 'Failed exporting refund to MeinPaket.de server.' );
		}
		
		return $this;
	}
	
	/**
	 * Is called when a shipment track is saved.
	 * The tracking code will be sent to MeinPaket.de if it's the first track
	 * for the shipment.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function salesOrderShipmentTrackSaveAfter(Varien_Event_Observer $observer) {
		/* @var $track Mage_Sales_Model_Order_Shipment_Track */
		$track = $observer->getData ( 'track' );
		
		if ($track == null) {
			return this;
		}
		
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment = $track->getShipment ();
		
		/* @var $order Mage_Sales_Model_Order */
		$order = $shipment->getOrder ();
		
		// check if order is a MeinPaket.de order...
		if (! Mage::helper ( 'meinpaket/order' )->isMeinPaketOrder ( $order )) {
			return $this;
		}
		
		$helper = Mage::helper ( 'meinpaket' );
		$result = null;
		$errMsg = '';
		
		/* @var $service Dhl_MeinPaket_Model_Service_Order_ShipmentExportService */
		$service = Mage::getModel ( 'meinpaket/service_order_shipmentExportService' );
		
		try {
			$result = $service->exportTrackingNumber ( $track );
		} catch ( Dhl_MeinPaket_Model_Xml_XmlBuildException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Request could not be built.' );
		} catch ( Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException $e ) {
			Mage::logException ( $e );
			$errMsg .= sprintf ( $helper->__ ( 'MeinPaket.de server returned HTPP code %s.' ), $e->getHttpReturnCode () );
		} catch ( Dhl_MeinPaket_Model_Client_HttpTimeoutException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Connection to MeinPaket.de server timed out.' );
		} catch ( Dhl_MeinPaket_Model_Xml_InvalidXmlException $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Invalid response from MeinPaket.de server.' );
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			$errMsg .= $helper->__ ( 'Unknown error.' );
		}
		
		if ($result !== null && ! $result->hasBeenAccepted ()) {
			$errMsg .= $helper->__ ( 'Tracking code has not been accepted by MeinPaket.de.' );
			if ($result->hasError ()) {
				$errMsg .= ' (' . sprintf ( $helper->__ ( 'MeinPaket.de returned error code %s.' ), $result->getError () ) . ')';
			}
		}
		
		if (strlen ( $errMsg ) > 0) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $helper->__ ( 'Error on transfering tracking code to MeinPaket.de.' ) . ' (' . $errMsg . ')' );
			throw new Exception ( 'Failed exporting tracking code to MeinPaket.de server.' );
		}
		
		return $this;
	}
	
	/**
	 * Is triggered after a product has been deleted.
	 * If the product has been exported to MeinPake.de once before, the
	 * product's status will be set to disabled, and then be saved to
	 * trigger the catalog_product_save_after event again.
	 *
	 * @see Dhl_MeinPaket_Model_Observer::catalogProductSaveAfter()
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function catalogProductDeleteBefore(Varien_Event_Observer $observer) {
		try {
			$product = Mage::getModel ( 'catalog/product' )->load ( $observer->getData ( 'product' )->getId () );
			if ($product->hasData ( 'was_exported_for_dhl_mein_paket' ) && (( boolean ) $product->getData ( 'product_was_exported_for_dhl' )) === true) {
				$product->setStatus ( Mage_Catalog_Model_Product_Status::STATUS_DISABLED )->save ();
			}
			
			$catalogService = Mage::getSingleton ( 'meinpaket/service_catalog' ); // TODO:
			$catalogService->deleteProduct ( $product );
			$catalogService->deleteProductAsVariation ( $product );
		} catch ( Exception $ex ) {
			Mage::logException ( $ex );
		}
		
		return $this;
	}
	
	/**
	 * Triggered before product is saved.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function catalogProductSaveBefore(Varien_Event_Observer $observer) {
		/**
		 *
		 * @var $product Mage_Catalog_Model_Product
		 */
		$product = $observer->getEvent ()->getProduct ();
		
		if (! $product->getId ()) {
			/*
			 * Delete meinpaket data for new and as such not synchronized objects. It's assumed that meinpaket_export is either set by the user or productDuplicate in this class. This is for example needed for quick created variants.
			 */
			$product->setData ( 'meinpaket_id', '' );
		}
		
		if ($product->hasDataChanges ()) {
			try {
				$changes = array ();
				foreach ( $product->getData () as $attribute => $newValue ) {
					// Find changed products
					$oldValue = $product->getOrigData ( $attribute );
					
					if (is_array ( $newValue ) && is_array ( $oldValue )) {
						// Ignored
					} else if ($newValue != $oldValue) {
						$changes [] = $attribute;
					}
				}
				
				if (count ( $changes )) {
					// Flag schedule in backlog.
					$product->setData ( 'meinpaket_backlog_changes', $changes );
				}
			} catch ( Exception $e ) {
				Mage::logException ( $e );
			}
		}
		return $this;
	}
	
	/**
	 * Triggered after product is saved.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function catalogProductSaveAfter(Varien_Event_Observer $observer) {
		try {
			$product = $observer->getEvent ()->getProduct ();
			
			if (is_array ( $product->getData ( 'meinpaket_backlog_changes' ) )) {
				// Schedule the product for later
				$changes = $product->getData ( 'meinpaket_backlog_changes' );
				if (count ( $changes ) && $product->getId ()) {
					$typeInstance = $product->getTypeInstance ();
					if ($typeInstance instanceof Mage_Catalog_Model_Product_Type_Configurable) {
						Mage::helper ( 'meinpaket/backlog' )->createChildrenBacklog ( $product->getId () );
					} else {
						Mage::helper ( 'meinpaket/backlog' )->createBacklog ( $product->getId (), implode ( ',', $changes ) );
					}
				}
			}
		} catch ( Exception $ex ) {
			Mage::logException ( $ex );
		}
		
		return $this;
	}
	
	/**
	 * Triggered before product massaction.
	 *
	 * @param Varien_Event_Observer $observer        	
	 * @return Dhl_MeinPaket_Model_Observer
	 */
	public function catalogProductAttributeUpdateBefore(Varien_Event_Observer $observer) {
		$attributesData = $observer->getEvent ()->getAttributesData ();
		$productIds = $observer->getEvent ()->getProductIds ();
		
		$changes = implode ( ',', array_keys ( $attributesData ) );
		
		foreach ( $productIds as $id ) {
			$count = Mage::helper ( 'meinpaket/backlog' )->createChildrenBacklog ( $id );
			if ($count <= 0) {
				Mage::helper ( 'meinpaket/backlog' )->createBacklog ( $id, $changes );
			}
		}
		return $this;
	}
	public function salesOrderGridCollectionLoadBefore($observer) {
		$collection = $observer->getOrderGridCollection ();
		// Don't enable this if select contains *. If an attribute is added all others are removed.
		// $collection->addAttributeToSelect ( 'dhl_mein_paket_order_id' );
		return $this;
	}
	public function gridAddAttributes(Varien_Event_Observer $observer) {
		$block = $observer->getBlock ();
		if (! isset ( $block )) {
			return $this;
		}
		
		if ($block->getType () == 'adminhtml/sales_order_grid') {
			/* @var $block Mage_Adminhtml_Block_Sales_Order_Grid */
			
			$block->addColumnAfter ( 'dhl_mein_paket_order_id', array (
					'header' => __ ( 'MeinPaket.de Order No.' ),
					'index' => 'dhl_mein_paket_order_id' 
			), 'real_order_id' );
		}
		return $this;
	}
	public function addMeinPaketAttributes($observer) {
		$fieldset = $observer->getForm ()->getElement ( 'base_fieldset' );
		$attribute = $observer->getAttribute ();
		$fieldset->addField ( 'meinpaket_attribute', 'select', array (
				'name' => 'meinpaket_attribute',
				'label' => Mage::helper ( 'meinpaket' )->__ ( 'MeinPaket Attribute' ),
				'title' => Mage::helper ( 'meinpaket' )->__ ( 'MeinPaket Attribute' ),
				'values' => Mage::getModel ( 'meinpaket/system_config_source_attributes' )->toOptionArray () 
		) );
	}
}