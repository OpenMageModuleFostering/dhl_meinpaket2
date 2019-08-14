<?php

/**
 * XML Request builder class which generates the requests to communicate with 
 * the MeinPaket.de webservice.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Xml
 * @version		$Id$
 * @author		Daniel Pötzinger <daniel.poetzinger@aoemedia.de>
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Xml_XmlRequestFactory extends Varien_Object {
	
	/**
	 * The XML-version of the generated XML.
	 *
	 * @var string
	 */
	const XML_VERSION = '1.0';
	
	/**
	 * The charset which is set in the XML prologue of the generated XML.
	 *
	 * @var string
	 */
	const XML_CHARSET = 'UTF-8';
	
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}
	
	/**
	 * NEW
	 * Creates the XML for upload request.
	 *
	 * @param Dhl_MeinPaket_Model_Service_Product_Export_Result $result        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return Dhl_MeinPaket_Model_Xml_Partial_UploadRequest
	 */
	public function createUploadRequest() {
		return Mage::getModel ( 'meinpaket/xml_requets_uploadRequest' );
	}
	
	/**
	 * Crates XML for a variant group upload.
	 *
	 * @param Dhl_MeinPaket_Model_Service_Product_Export_Variant_Group_Collection $variantGroups        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createVariantGroupUploadRequest(Dhl_MeinPaket_Model_Service_Product_Export_Variant_Group_Collection $variantGroups) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		if ($variantGroups->count () < 1) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No variant groups.' );
		}
		
		$xml = '';
		$document = $this->createDocument ();
		
		/* @var $uploadRequest Dhl_MeinPaket_Model_Xml_Partial_UploadRequest */
		$uploadRequest = Mage::getModel ( 'meinpaket/xml_partial_uploadRequest' );
		
		/* @var $variantGroupsPartial Dhl_MeinPaket_Model_Xml_Partial_VariantGroups */
		$variantGroupsPartial = Mage::getModel ( 'meinpaket/xml_partial_variantGroups' );
		
		$uploadRequest->setDocument ( $document )->build ();
		$variantGroupsPartial->setDocument ( $document )->build ();
		
		foreach ( $variantGroups as $variantGroup ) {
			/* @var $variantGroupPartial Dhl_MeinPaket_Model_Xml_Partial_VariantGroup */
			$variantGroupPartial = Mage::getModel ( 'meinpaket/xml_partial_variantGroup' );
			
			$variantGroupPartial->setDocument ( $document )->setVariantGroup ( $variantGroup )->build ();
			
			$variantGroupsPartial->getNode ()->appendChild ( $variantGroupPartial->getNode () );
		}
		
		$uploadRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$uploadRequest->getNode ()->appendChild ( $variantGroupsPartial->getNode () );
		
		$document->appendChild ( $uploadRequest->getNode () );
		
		$xml = $document->saveXML ();
		
		return $xml;
	}
	
	/**
	 * Creates the XML for a product upload request.
	 *
	 * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection        	
	 * @param Dhl_MeinPaket_Model_Service_Product_Export_Result $result        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createProductsUploadRequestXml(Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection, Dhl_MeinPaket_Model_Service_Product_Export_Result $result) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		$uploadRequest = Mage::getModel ( 'meinpaket/xml_partial_uploadRequest' );
		$descriptions = Mage::getModel ( 'meinpaket/xml_partial_descriptions' );
		$offers = Mage::getModel ( 'meinpaket/xml_partial_offers' );
		$description = null;
		$offer = null;
		$xml = '';
		
		$uploadRequest->setDocument ( $document )->build ();
		$descriptions->setDocument ( $document )->build ();
		$offers->setDocument ( $document )->build ();
		
		if ($productCollection->count () > 0) {
			
			/* @var $attributeHelper Dhl_MeinPaket_Helper_Attribute */
			$attributeHelper = Mage::helper ( 'meinpaket/attribute' );
			
			foreach ( $productCollection as $product ) {
				// $product->load($product->getId());
				// $setAttributes = $product->getTypeInstance(true)->getSetAttributes($product);
				// foreach($setAttributes as $attributeIndex => $setAttribute) {
				// if($attributeHelper->isExportableAttribute($setAttribute)) {
				// Mage::log("Attribute is exportable: ".$attributeIndex);
				// }
				// }
				// Mage::log('Export: '.$product->getId().' , '.$product->getTypeId());
				// Mage::log("Custom Options: ".print_r($attrs,true));
				// Mage::log("Default Attr Set Id: ".$product->getDefaultAttributeSetId());
				// Mage::log("Ver3: ".print_r(Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId()),true));
				// Mage::log("ks: ".print_r(get_class($x),true));
				// Mage::log("Full: ".print_r(gettype($x[0]),true));
				// Mage::log(print_r($product->loadParentProductIds()->getData('parent_product_ids'),true));
				// $configurable_product = Mage::getModel('catalog/product_type_configurable');
				// $parentIdArray = $configurable_product->getParentIdsByChild($product->getId());
				// Mage::log("solution B: ".print_r($parentIdArray,true));
				
				try {
					$description = Mage::getModel ( 'meinpaket/xml_partial_productDescription' );
					$description->setDocument ( $document )->setProduct ( $product )->build ();
					$descriptions->getNode ()->appendChild ( $description->getNode () );
				} catch ( Dhl_MeinPaket_Model_Exception_InvalidDataException $e ) {
					Mage::logException ( $e );
					
					$result->addErrorForProductDescription ( $e->getEntityId (), $e->getFieldName (), $e->getErrorType () );
					
					// not all required data set for a description - we skip the description therefore
					// but we need at least a valid ean (sku) to still send an offer
					if (! $this->hasValidSku ( $product )) {
						$result->addErrorForProductDescription ( $e->getEntityId (), 'sku', Dhl_MeinPaket_Model_Validation_ValidationInterface::ERROR_FIELD_IS_INVALID );
						continue; // skip current
					}
				}
				// if description is not set - we need at least an sku - otherwise skip this item!
				$offer = Mage::getModel ( 'meinpaket/xml_partial_productOffer' );
				$offer->setDocument ( $document )->setProduct ( $product )->build ();
				$offers->getNode ()->appendChild ( $offer->getNode () );
				$result->incrementTotalRequestCount ();
			}
		}
		
		$uploadRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$uploadRequest->getNode ()->appendChild ( $descriptions->getNode () );
		$uploadRequest->getNode ()->appendChild ( $offers->getNode () );
		
		$document->appendChild ( $uploadRequest->getNode () );
		
		$xml = $document->saveXML ();
		
		$result->setRequestXml ( $xml );
		
		return $xml;
	}
	
	/**
	 * Creates the XML for an order cancellation request.
	 *
	 * @param Mage_Sales_Model_Order $order        	
	 * @param Mage_Sales_Model_Quote $quote        	
	 * @param Dhl_MeinPaket_Model_OrderCancellation_Result $result        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createOrderCancellationRequest(Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote $quote, Dhl_MeinPaket_Model_OrderCancellation_Result $result) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Partial_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/Xml_Partial_NotificationRequest' );
		
		/* @var $cancellations Dhl_MeinPaket_Model_Xml_Partial_Cancellations */
		$cancellations = Mage::getModel ( 'meinpaket/Xml_Partial_Cancellations' );
		
		/* @var $cancellation Dhl_MeinPaket_Model_Xml_Partial_Cancellation */
		$cancellation = Mage::getModel ( 'meinpaket/Xml_Partial_Cancellation' );
		
		/* @var $cancellationEntry Dhl_MeinPaket_Model_Xml_Partial_CancellationEntry */
		$cancellationEntry = null;
		
		$notificationRequest->setDocument ( $document )->build ();
		
		$cancellations->setDocument ( $document )->build ();
		
		$cancellation->setDocument ( $document )->setOrderId ( $order->getDhlMeinPaketOrderId () )->setConsignmentId ( $order->getId () )->build ();
		
		foreach ( $quote->getItemsCollection () as $item ) {
			$cancellationEntry = Mage::getModel ( 'meinpaket/Xml_Partial_CancellationEntry' );
			$cancellationEntry->setDocument ( $document )->setProductId ( $item->getProductId () )->setQuantity ( $item->getQty () )->setReason ( 'OutOfStock' )->build ();
			$cancellation->getNode ()->appendChild ( $cancellationEntry->getNode () );
		}
		
		$cancellations->getNode ()->appendChild ( $cancellation->getNode () );
		$notificationRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$notificationRequest->getNode ()->appendChild ( $cancellations->getNode () );
		$document->appendChild ( $notificationRequest->getNode () );
		
		return $document->saveXML ();
	}
	
	/**
	 * Creates the XML for a request that cancels single items of an order.
	 *
	 * @param Mage_Sales_Model_Order $order        	
	 * @param array $items        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createPartialOrderCancellationRequest(Mage_Sales_Model_Order $order, array $items) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Partial_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/Xml_Partial_NotificationRequest' );
		
		/* @var $cancellations Dhl_MeinPaket_Model_Xml_Partial_Cancellations */
		$cancellations = Mage::getModel ( 'meinpaket/Xml_Partial_Cancellations' );
		
		/* @var $cancellation Dhl_MeinPaket_Model_Xml_Partial_Cancellation */
		$cancellation = Mage::getModel ( 'meinpaket/Xml_Partial_Cancellation' );
		
		/* @var $cancellationEntry Dhl_MeinPaket_Model_Xml_Partial_CancellationEntry */
		$cancellationEntry = null;
		
		$notificationRequest->setDocument ( $document )->build ();
		
		$cancellations->setDocument ( $document )->build ();
		
		$cancellation->setDocument ( $document )->setOrderId ( $order->getDhlMeinPaketOrderId () )->setConsignmentId ( ( string ) $order->getId () )->build ();
		
		foreach ( $items as $item ) {
			$cancellationEntry = Mage::getModel ( 'meinpaket/Xml_Partial_CancellationEntry' );
			$cancellationEntry->setDocument ( $document )->setProductId ( $item ['productId'] )->setQuantity ( $item ['qty'] )->setReason ( 'CustomerRequest' )->build ();
			$cancellation->getNode ()->appendChild ( $cancellationEntry->getNode () );
		}
		
		$cancellations->getNode ()->appendChild ( $cancellation->getNode () );
		$notificationRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$notificationRequest->getNode ()->appendChild ( $cancellations->getNode () );
		$document->appendChild ( $notificationRequest->getNode () );
		
		return $document->saveXML ();
	}
	
	/**
	 * Creates XML for an order download request.
	 *
	 * @param integer $dateFrom        	
	 * @param integer $dateTo        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createOrderRequestXml($dateFrom, $dateTo) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		$orderRequest = Mage::getModel ( 'meinpaket/xml_partial_orderRequest' );
		$orders = Mage::getModel ( 'meinpaket/xml_partial_orders' );
		
		$orderRequest->setDocument ( $document )->build ();
		$orders->setDateFrom ( $dateFrom )->setDateTo ( $dateTo )->setDocument ( $document )->build ();
		
		$orderRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$orderRequest->getNode ()->appendChild ( $orders->getNode () );
		
		$document->appendChild ( $orderRequest->getNode () );
		
		return $document->saveXML ();
	}
	
	/**
	 * Creates XML for a shipment export request.
	 *
	 * @param Mage_Sales_Model_Order_Shipment $shipment        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createShipmentRequest(Mage_Sales_Model_Order_Shipment $shipment) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Partial_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/Xml_Partial_NotificationRequest' );
		
		/* @var $consignments Dhl_MeinPaket_Model_Xml_Partial_Consignments */
		$consignments = Mage::getModel ( 'meinpaket/Xml_Partial_Consignments' );
		
		/* @var $consignment Dhl_MeinPaket_Model_Xml_Partial_Consignment */
		$consignment = null;
		
		/* @var $consignmentEntry Dhl_MeinPaket_Model_Xml_Partial_ConsignmentEntry */
		$consignmentEntry = null;
		
		/* @var $shipmentUtil Dhl_MeinPaket_Model_Util_Shipment */
		$shipmentUtil = Mage::getSingleton ( 'meinpaket/Util_Shipment' );
		
		$consignmentId = $shipmentUtil->getConsignmentIdForShipment ( $shipment );
		
		$notificationRequest->setDocument ( $document )->build ();
		
		$consignments->setDocument ( $document )->build ();
		
		$itemsCollection = Mage::getModel ( 'sales/order_shipment_item' )->getCollection ();
		$itemsCollection->addAttributeToSelect ( 'qty' )->addAttributeToSelect ( 'product_id' )->addAttributeToFilter ( 'parent_id', $shipment->getId () )->load ();
		
		foreach ( $itemsCollection as $item ) {
			
			$consignment = Mage::getModel ( 'meinpaket/Xml_Partial_Consignment' );
			$consignment->setDocument ( $document )->setOrderId ( $shipment->getOrder ()->getDhlMeinPaketOrderId () )->setConsignmentId ( $consignmentId )->build ();
			
			$consignmentEntry = Mage::getModel ( 'meinpaket/Xml_Partial_ConsignmentEntry' );
			$consignmentEntry->setDocument ( $document )->setProductId ( $item->getProductId () )->setQuantity ( $item->getQty () )->build ();
			
			$consignment->getNode ()->appendChild ( $consignmentEntry->getNode () );
			$consignments->getNode ()->appendChild ( $consignment->getNode () );
		}
		
		$notificationRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$notificationRequest->getNode ()->appendChild ( $consignments->getNode () );
		$document->appendChild ( $notificationRequest->getNode () );
		
		return $document->saveXML ();
	}
	
	/**
	 * Creates XML for a request which notifies MeinPaket.de about returned shipments.
	 *
	 * @param Mage_Sales_Model_Order_Creditmemo $creditMemo        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createReturnRequest(Mage_Sales_Model_Order_Creditmemo $creditMemo) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Partial_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/Xml_Partial_NotificationRequest' );
		
		/* @var $returns Dhl_MeinPaket_Model_Xml_Partial_Returns */
		$returns = Mage::getModel ( 'meinpaket/Xml_Partial_Returns' );
		
		/* @var $return Dhl_MeinPaket_Model_Xml_Partial_Return */
		$return = Mage::getModel ( 'meinpaket/Xml_Partial_Return' );
		
		/* @var $returnEntry Dhl_MeinPaket_Model_Xml_Partial_ReturnEntry */
		$returnEntry = null;
		
		$comment = '';
		
		$notificationRequest->setDocument ( $document )->build ();
		
		$returns->setDocument ( $document )->build ();
		
		if ($creditMemo->getCommentsCollection ()->count () > 0) {
			$comment = $creditMemo->getCommentsCollection ()->getFirstItem ()->getComment ();
		}
		
		$return->setDocument ( $document )->setOrderId ( $creditMemo->getOrder ()->getDhlMeinPaketOrderId () )->setReturnId ( $creditMemo->getId () )->setComment ( $comment )->setReimbursedDeliveryCosts ( $creditMemo->getShippingAmount () )->setReduction ( $creditMemo->getAdjustment () * (- 1) )->build ();
		
		foreach ( $creditMemo->getItemsCollection () as $item ) {
			$returnEntry = Mage::getModel ( 'meinpaket/Xml_Partial_ReturnEntry' );
			$returnEntry->setDocument ( $document )->setProductId ( $item->getProductId () )->setQuantity ( $item->getQty () )->build ();
			$return->getNode ()->appendChild ( $returnEntry->getNode () );
		}
		
		$returns->getNode ()->appendChild ( $return->getNode () );
		$notificationRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$notificationRequest->getNode ()->appendChild ( $returns->getNode () );
		
		$document->appendChild ( $notificationRequest->getNode () );
		
		return $document->saveXML ();
	}
	
	/**
	 * Creates the XML request which contains the tracking code for particular shipment.
	 *
	 * @param Mage_Sales_Model_Order_Shipment_Track $track        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createShipmentTrackRequestXml(Mage_Sales_Model_Order_Shipment_Track $track) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Partial_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/Xml_Partial_NotificationRequest' );
		
		/* @var $trackingNumbers Dhl_MeinPaket_Model_Xml_Partial_TrackingNumbers */
		$trackingNumbers = Mage::getModel ( 'meinpaket/Xml_Partial_TrackingNumbers' );
		
		/* @var $trackingNumber Dhl_MeinPaket_Model_Xml_Partial_TrackingNumber */
		$trackingNumber = Mage::getModel ( 'meinpaket/Xml_Partial_TrackingNumber' );
		
		/* @var $shipmentUtil Dhl_MeinPaket_Model_Util_Shipment */
		$shipmentUtil = Mage::getSingleton ( 'meinpaket/Util_Shipment' );
		
		$consignmentId = $shipmentUtil->getConsignmentIdForShipment ( $track->getShipment () );
		
		$notificationRequest->setDocument ( $document )->build ();
		
		$trackingNumbers->setDocument ( $document )->build ();
		
		$trackingNumber->setConsignmentId ( $consignmentId )->setTrackingId ( $track->getNumber () )->setDocument ( $document )->build ();
		
		$trackingNumbers->getNode ()->appendChild ( $trackingNumber->getNode () );
		$notificationRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$notificationRequest->getNode ()->appendChild ( $trackingNumbers->getNode () );
		
		$document->appendChild ( $notificationRequest->getNode () );
		
		return $document->saveXML ();
	}
	public function createProductDeleteRequest($product) {
	}
	
	/**
	 * Creates the XML for an external checkout.
	 *
	 * @param Dhl_MeinPaket_Model_Service_Cart_Export_Checkout $checkout        	
	 * @return string
	 */
	public function createSubmitCartRequest(Dhl_MeinPaket_Model_Service_Cart_Export_Checkout $checkout) {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		
		/* @var $submitCartRequest Dhl_MeinPaket_Model_Xml_Partial_SubmitCartRequest */
		$submitCartRequest = Mage::getModel ( 'meinpaket/Xml_Partial_SubmitCartRequest' );
		
		/* @var $shoppingCart Dhl_MeinPaket_Model_Xml_Partial_ShoppingCart */
		$shoppingCart = Mage::getModel ( 'meinpaket/Xml_Partial_ShoppingCart' );
		
		/* @var $shoppingCartItem Dhl_MeinPaket_Model_Xml_Partial_ShoppingCartItem */
		$shoppingCartItem = null;
		
		$submitCartRequest->setDocument ( $document )->build ();
		
		foreach ( $checkout->getCart ()->getItems () as $item ) {
			$shoppingCartItem = Mage::getModel ( 'meinpaket/xml_partial_shoppingCartItem' );
			$shoppingCartItem->setDocument ( $document )->setItem ( $item )->build ();
			$shoppingCart->addItem ( $shoppingCartItem );
		}
		
		$shoppingCart->setDocument ( $document )->setCheckout ( $checkout );
		
		$shoppingCart->build ();
		
		$submitCartRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$submitCartRequest->getNode ()->appendChild ( $shoppingCart->getNode () );
		
		$document->appendChild ( $submitCartRequest->getNode () );
		
		return $document->saveXML ();
	}
	
	/**
	 * Creates the XML request for a variant configurations download.
	 *
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @return string
	 */
	public function createVariantConfigurationDownloadRequest() {
		if (! $this->areAuthenticationParamtersSet ()) {
			throw new Dhl_MeinPaket_Model_Xml_XmlBuildException ( 'No authentication parameters set.' );
		}
		
		$document = $this->createDocument ();
		$downloadRequest = Mage::getModel ( 'meinpaket/xml_partial_downloadRequest' );
		$variantConfigurations = Mage::getModel ( 'meinpaket/xml_partial_variantConfigurations' );
		
		$downloadRequest->setDocument ( $document )->build ();
		$variantConfigurations->setDocument ( $document )->build ();
		
		$downloadRequest->getNode ()->appendChild ( $this->createHeaderNode ( $document ) );
		$downloadRequest->getNode ()->appendChild ( $variantConfigurations->getNode () );
		
		$document->appendChild ( $downloadRequest->getNode () );
		
		return $document->saveXML ();
	}
	
	/**
	 * Checks if the necessary authentication have been set.
	 *
	 * @return boolean
	 */
	public function areAuthenticationParamtersSet() {
		return (is_string ( $this->username ) && is_string ( $this->password ) && (strlen ( $this->username ) > 0) && (strlen ( $this->password ) > 0));
	}
	
	/**
	 * Creates XML for the header element which encapsulates the user credentials.
	 *
	 * @param DOMDocument $document        	
	 * @return DOMNode
	 */
	public function createHeaderNode(DOMDocument $document) {
		return Mage::getModel ( 'meinpaket/xml_partial_header' )->setDocument ( $document )->setUsername ( $this->username )->setPassword ( $this->password )->build ()->getNode ();
	}
}

