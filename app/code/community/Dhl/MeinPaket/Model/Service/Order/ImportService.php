<?php

/**
 * Service class which imports orders from MeinPaket.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Order
 * @version		$Id$
 * @author		Andreas Demmer <andreas.demmer@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Service_Order_ImportService extends Varien_Object {
	/**
	 * counts of imported and duplicate orders
	 *
	 * @var $_orders array
	 */
	var $_orderCount;
	
	/**
	 * DHL IDs from orders which are out of stock
	 *
	 * @var $_outOfStockOrders array
	 */
	var $_outOfStockOrders;
	
	/**
	 * Dhl order ids which include disabled products.
	 *
	 * @var array
	 */
	protected $_disabledProductOrders;
	
	/**
	 *
	 * @var string
	 */
	const IMPORT_SHIPPING_METHOD = 'meinpaket_method1';
	
	/**
	 *
	 * @var string
	 */
	const IMPORT_PAYMENT_METHOD = 'meinpaket';
	
	/**
	 *
	 * @var duplicate orders, returncode
	 */
	const DUPLICATE_ORDER_STATUS = 2;
	
	/**
	 *
	 * @var imported orders, returncode
	 */
	const IMPORTED_ORDER_STATUS = 1;
	
	/**
	 *
	 * @var out-of-stock orders, returncode
	 */
	const OUT_OF_STOCK_ORDER_STATUS = 3;
	
	/**
	 * Status for a disabled product.
	 *
	 * @var integer
	 */
	const DISABLED_ORDER_STATUS = 4;
	const USE_GUEST_ACCOUNT_CONFIG = 'meinpaket/order/use_guest_account';
	const CUSTOMER_GROUP_CONFIG = 'meinpaket/order/customer_group';
	
	/**
	 *
	 * @var boolean
	 */
	protected $serviceResponseWasMalformed = false;
	
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->_orderCount = array (
				'imported' => 0,
				'duplicates' => 0,
				'outOfStock' => 0,
				'disabled' => 0 
		);
		
		$_outOfStockOrders = array ();
		$this->_disabledProductOrders = array ();
		
		parent::__construct ();
	}
	
	/**
	 * Tells if the MeinPaket response for the impoer orders request was
	 * malformed and could not be parsed.
	 *
	 * @return boolean
	 */
	public function wasServiceResponseMalformed() {
		return $this->serviceResponseWasMalformed;
	}
	
	/**
	 * returns count of imported and duplicate orders
	 *
	 * @return array
	 */
	public function getOrderCount() {
		return $this->_orderCount;
	}
	
	/**
	 * returns DHL IDs of orders which are out of stock
	 *
	 * @return array
	 */
	public function getOutOfStockOrders() {
		return $this->_outOfStockOrders;
	}
	
	/**
	 * Returns the order ids which could not be imported, because they contained
	 * disabled products.
	 *
	 * @return array
	 */
	public function getDisabledProductOrders() {
		return $this->_disabledProductOrders;
	}
	
	/**
	 * Imports Order from meinPaket
	 * //TODO make a simple result object containing: countnew / countexisting / warnings (e.g.
	 * if price missmatch)
	 *
	 * @param integer $start        	
	 * @param integer $stop        	
	 * @return void
	 */
	public function importOrders($start = null, $stop = null) {
		/* @var $xmlRequestFactory Dhl_MeinPaket_Model_Xml_XmlRequestFactory */
		$xmlRequestFactory = Mage::getSingleton ( 'meinpaket/xml_xmlRequestFactory' );
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$client = Mage::getModel ( 'meinpaket/client_xmlOverHttp' );
		
		/* @var $queryRequest Dhl_MeinPaket_Model_Xml_Request_QueryRequest */
		$queryRequest = Mage::getModel ( 'meinpaket/xml_request_queryRequest' );
		// $queryRequest->addOrders ( $start, $stop );
		$queryRequest->addOrders ( $start, $stop, 'Open' );
		
		/* @var $queryResult Dhl_MeinPaket_Model_Xml_Response_QueryResponse */
		$queryResult = $client->send ( $queryRequest );
		
		foreach ( $queryResult->getOrders () as $order ) {
			/* @var $order Dhl_MeinPaket_Model_Xml_Response_Partial_Order */
			$successCode = $this->_importOrder ( $order );
			switch ($successCode) {
				case self::IMPORTED_ORDER_STATUS :
					$this->_orderCount ['imported'] ++;
					break;
				case self::DUPLICATE_ORDER_STATUS :
					$this->_orderCount ['duplicates'] ++;
					break;
				case self::OUT_OF_STOCK_ORDER_STATUS :
					$this->_orderCount ['outOfStock'] ++;
					break;
				case self::DISABLED_ORDER_STATUS :
					$this->_orderCount ['disabled'] ++;
					break;
			}
		}
		
		return $this->_orderCount;
	}
	
	/**
	 * imports orders from XML, returns success code
	 *
	 * @param SimpleXMLElement $order        	
	 * @return int
	 */
	protected function _importOrder(Dhl_MeinPaket_Model_Xml_Response_Partial_Order $order) {
		$storeId = Mage::helper ( 'meinpaket/data' )->getMeinPaketStoreId ();
		$store = Mage::helper ( 'meinpaket/data' )->getMeinPaketStore ();
		
		/* @var $orderObj Mage_Sales_Model_Order */
		$orderObj = Mage::getModel ( 'sales/order' )->load ( $order->getOrderId (), 'dhl_mein_paket_order_id' );
		
		// do not import order already existing orders
		if ($orderObj->getId ()) {
			return self::DUPLICATE_ORDER_STATUS;
		}
		
		$customer = $this->getOrCreateCustomer ( $order );
		
		if (! Mage::getStoreConfig ( self::USE_GUEST_ACCOUNT_CONFIG )) {
			$customer->save ();
		}
		
		// Set email for guest order
		/* @var $quoteObj Mage_Sales_Model_Quote */
		$quoteObj = Mage::getModel ( 'sales/quote' );
		$quoteObj->setStoreId ( $storeId );
		$quoteObj->setCustomerNote ( __ ( 'Imported from DHL MeinPaket.de' ) . ' (' . __ ( 'Delivery Method' ) . ':' . (( string ) $order->getDeliveryMethod ()) . ')' );
		$quoteObj->setCustomerFirstname ( $customer->getFirstname () );
		$quoteObj->setCustomerLastname ( $customer->getLastname () );
		$quoteObj->setCustomerIsGuest ( Mage::getStoreConfig ( self::USE_GUEST_ACCOUNT_CONFIG ) );
		$quoteObj->setCustomerEmail ( $customer->getEmail () );
		$quoteObj->setCustomer ( $customer );
		
		$hasNoConfigurables = true;
		$quoteItems = array ();
		
		foreach ( $order->getEntries () as $orderEntry ) {
			/* var $orderEntry Dhl_MeinPaket_Model_Xml_Response_Partial_Order_Entry */
			$productId = ( string ) $orderEntry->getProductId ();
			
			if ($hasNoConfigurables) {
				
				try {
					/* @var $productObj Mage_Catalog_Model_Product */
					$productObj = Mage::getModel ( 'catalog/product' )->setStoreId ( $storeId )->load ( $productId );
					
					if ($productObj->isConfigurable ()) {
						$hasNoConfigurables = false;
						break;
					}
					
					// check wether product is in stock
					$stockItem = $productObj->getStockItem ();
					
					$magentoPrice = number_format ( $productObj->getPrice (), 2 ) . " " . $store->getCurrentCurrencyCode ();
					$meinPaketPrice = number_format ( ( string ) $orderEntry->getBasePrice (), 2 ) . " " . $store->getCurrentCurrencyCode ();
					
					if ($productObj->getStatus () == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
						$this->_disabledProductOrders [] = $order->getOrderId ();
						return self::DISABLED_ORDER_STATUS;
					} elseif (( bool ) $stockItem->getData ( 'is_in_stock' )) {
						if ($magentoPrice != $meinPaketPrice) {
							$message = sprintf ( __ ( '%s was ordered with a different price (%s instead of current price %s). Was the price changed after exporting to MeinPaket.de?' ), $productObj->getName (), $meinPaketPrice, $magentoPrice );
							Mage::getSingleton ( 'adminhtml/session' )->addNotice ( $message );
						}
						
						$item = $quoteObj->addProduct ( $productObj, new Varien_Object ( $request = array (
								'qty' => ( string ) $orderEntry->getQuantity (),
								'dhl_mein_paket_item_id' => ( string ) $orderEntry->getMeinPaketId () 
						) ) );
						
						$item->setRowTotal ( floatval ( $item->getRowTotal () ) + $orderEntry->getBasePrice () );
						$item->setCustomPrice ( $orderEntry->getBasePrice () );
						$item->setOriginalCustomPrice ( $orderEntry->getBasePrice () );
						$item->setRowTotal ( $orderEntry->getTotalPrice () );
						
						$item->getProduct ()->setIsSuperMode ( true );
					} else {
						$this->_outOfStockOrders [] = $order->getOrderId ();
						return self::OUT_OF_STOCK_ORDER_STATUS;
					}
				} catch ( Exception $e ) {
					Mage::logException ( $e );
					throw new Exception ( 'Could not add product ' . $productId . ' - ' . $e->getMessage () );
				}
			}
		}
		
		if (! $hasNoConfigurables) {
			$quoteObj->delete ();
			return 0;
		}
		
		if (sizeof ( $quoteItems ) > 0) {
			foreach ( $quoteItems as $quoteItem ) {
			}
		}
		
		$billingAddress = $quoteObj->getBillingAddress ();
		$billingAddress->addData ( $this->_getAddressData ( $order->getBillingAddress () ) );
		$shippingAddress = $quoteObj->getShippingAddress ();
		if ($order->getDeliveryAddress () != null) {
			$shippingAddress->addData ( $this->_getAddressData ( $order->getDeliveryAddress () ) );
		} else {
			$shippingAddress->addData ( $this->_getAddressData ( $order->getBillingAddress () ) );
		}
		
		$paymentMethod = self::IMPORT_PAYMENT_METHOD;
		$shippingAddress->setPaymentMethod ( $paymentMethod );
		$quoteObj->getPayment ()->importData ( array (
				'method' => $paymentMethod 
		) );
		
		Dhl_MeinPaket_Model_Carrier_Meinpaket::unlock ();
		Dhl_MeinPaket_Model_Carrier_Meinpaket::setDeliveryCosts ( $order->getTotalDeliveryCosts () );
		
		$shippingAddress->setShippingMethod ( self::IMPORT_SHIPPING_METHOD )->unsGrandTotal ()->unsBaseGrandTotal ()->setCollectShippingRates ( true )->save ();
		$shippingAddress->collectTotals ();
		
		$quoteObj->collectTotals ();
		$quoteObj->save ();
		
		/* @var $quoteObj Mage_Sales_Model_Service_Quote */
		$serviceQuote = Mage::getModel ( 'sales/service_quote', $quoteObj );
		$serviceQuote->submitAll ();
		
		/* @var $orderModel Mage_Sales_Model_Order */
		$orderModel = $serviceQuote->getOrder ();
		/**
		 * triggert aufruf von authorizes() auf dem payment model (ggf auch capture() )
		 */
		
		$contactData = $order->getContactData ();
		
		$orderModel->place ();
		$orderModel->setState ( Mage_Sales_Model_Order::STATE_PROCESSING, true );
		$orderModel->setData ( 'created_at', $this->_getFormattedDateString ( $order->getOrderDate () ) );
		$orderModel->setData ( 'customer_email', strlen ( $contactData->getEmail () ) ? $contactData->getEmail () : Mage::getStoreConfig ( 'meinpaket/customer/default_email' ) );
		$orderModel->setData ( 'ext_customer_id', $contactData->getCustomerId () );
		$orderModel->setData ( 'dhl_mein_paket_order_id', $order->getOrderId () );
		// Zend_Debug::dump($orderModel->getData());die;
		$orderModel->save ();
		
		Dhl_MeinPaket_Model_Carrier_Meinpaket::unlock ();
		
		// dispatch event
		Mage::dispatchEvent ( 'dhl_meinpaket_orderimport', array (
				'orderId' => $orderModel->getId () 
		) );

		$this->createInvoice($orderModel);
		
		return self::IMPORTED_ORDER_STATUS;
	}
	
	/**
	 * Extracts address data from the given element.
	 *
	 * @param SimpleXMLElement $addressElement        	
	 * @return array
	 */
	protected function _getAddressData(Dhl_MeinPaket_Model_Xml_Response_Partial_Address $address) {
		$addressArray = array (
				'salutation' => ( string ) $address->getSalutation (),
				'firstname' => ( string ) $address->getFirstName (),
				'lastname' => ( string ) $address->getLastName (),
				// 'street' => ( string ) $address->getStreet (),
				'postcode' => ( string ) $address->getZipCode (),
				'city' => ( string ) $address->getCity (),
				'region_id' => '91', // TODO: make this optional - and add in documentation
				'country_id' => ( string ) $address->getCountry (),
				'telephone' => '0000' 
		);
		
		if (strlen ( $address->getCustomerId () )) {
			$addressArray ['street'] = ( string ) $address->getCustomerId () . "\n" . ( string ) $address->getStreet () . " " . ( string ) $address->getHouseNumber () . "\n" . ( string ) $address->getAddressAddition ();
		} else {
			$addressArray ['street'] = ( string ) $address->getStreet () . " " . ( string ) $address->getHouseNumber () . "\n" . ( string ) $address->getAddressAddition ();
		}
		
		if (strlen ( $address->getCompany () )) {
			$addressArray ['company'] = ( string ) $address->getCompany ();
		}
		
		return $addressArray;
	}
	
	/**
	 * Converts the given ISO date string (i.e.
	 * "2010-10-27T15:59:59.012+02:00")
	 * into the mysql datetime format (i.e. "2010-10-27 15:59:59").
	 *
	 * @param string $isoDateString
	 *        	@wins Code of the year contest
	 * @return string
	 */
	protected function _getFormattedDateString($isoDateString) {
		$date = new Zend_Date ( $isoDateString, Zend_Date::ISO_8601 );
		$mysqlDateString = $date->toString ( 'YYYY-MM-dd HH:mm:ss' );
		return $mysqlDateString;
	}
	
	/**
	 * Get or create customer.
	 *
	 * @param array $viaOrder
	 *        	to check
	 * @return Mage_Customer_Model_Customer
	 */
	protected function getOrCreateCustomer(Dhl_MeinPaket_Model_Xml_Response_Partial_Order $order) {
		/* @var $customer Mage_Customer_Model_Customer */
		$customer = Mage::getModel ( 'customer/customer' )->getCollection ()->addAttributeToSelect ( 'meinpaket_buyer_id' )->addAttributeToFilter ( 'meinpaket_buyer_id', $order->getContactData ()->getCustomerId () )->load ()->getFirstItem ();
		
		if ($customer->getId () != null) {
			return $customer;
		}
		
		// Set store and website for loadByEmail.
		$customer->setStore ( Mage::helper ( 'meinpaket/data' )->getMeinPaketStore () );
		// Could not find customer by meinpaket_buyer_id. As there can only be one customer for a given
		// email try to load one.
		$customer->loadByEmail ( $order->getContactData ()->getEmail () );
		
		if ($customer->getId () != null) {
			return $customer;
		}
		
		// New customer
		// Set store and website again after loadByEmail reset it.
		$customer->setStore ( Mage::helper ( 'meinpaket/data' )->getMeinPaketStore () );
		$customer->setFirstname ( $order->getBillingAddress ()->getFirstName () );
		$customer->setLastname ( $order->getBillingAddress ()->getLastName () );
		$customer->setEmail ( $order->getContactData ()->getEmail () );
		
		$groupId = Mage::getStoreConfig ( self::CUSTOMER_GROUP_CONFIG );
		$customer->setData ( 'group_id', $groupId );
		
		$customer->setData ( 'meinpaket_buyer_id', $order->getContactData ()->getCustomerId () );
		
		if (! $customer->getId ()) {
			// recurring customer
			$customer->setPasswordHash ( $customer->generatePassword () );
		}
		
		return $customer;
	}
	protected function createInvoice(Mage_Sales_Model_Order $order) {
		/* @var $invoiceModel Sales_Model_Order_Invoice */
		$invoice = $order->prepareInvoice ();
		
		$invoice->setRequestedCaptureCase ( Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE );
		$invoice->register ();
		$invoice->setIsInProcess ( true );
		
		$transactionSave = Mage::getModel ( 'core/resource_transaction' )->addObject ( $invoice )->addObject ( $invoice->getOrder () )->save ();
	}
}
