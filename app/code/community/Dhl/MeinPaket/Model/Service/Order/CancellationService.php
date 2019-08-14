<?php

/**
 * Service class which cancels an order.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_OrderCancellation
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Service_Order_CancellationService extends Varien_Object {
	/**
	 * Cancels the given MeinPaket order.
	 *
	 * @param Mage_Sales_Model_Order $order        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @throws Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException
	 * @throws Dhl_MeinPaket_Model_Client_HttpTimeoutException
	 * @throws Dhl_MeinPaket_Model_Xml_InvalidXmlException
	 * @return Dhl_MeinPaket_Model_OrderCancellation_Result
	 */
	public function cancelOrder(Mage_Sales_Model_Order $order) {
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Request_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/xml_request_notificationRequest' );
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$httpClient = Mage::getModel ( 'meinpaket/client_xmlOverHttp' );
		
		/* @var $result Dhl_MeinPaket_Model_Service_Order_CancellationService_Result */
		$result = Mage::getModel ( 'meinpaket/service_order_cancellationService_result' );
		
		try {
			$notificationRequest->addCancellation ( $order );
			$responseXml = $httpClient->send ( $notificationRequest );
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			throw $e;
		}
		
		return $result;
	}
	
	/**
	 * Cancels a set of items for MeinPaket order.
	 *
	 * @param Mage_Sales_Model_Order $order        	
	 * @param array $items
	 *        	of the array have to be associative arrays
	 *        	containing the keys "productId" and "qty" (Quantity to reduce).
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @throws Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException
	 * @throws Dhl_MeinPaket_Model_Client_HttpTimeoutException
	 * @throws Dhl_MeinPaket_Model_Xml_InvalidXmlException
	 * @return Dhl_MeinPaket_Model_OrderCancellation_Result
	 */
	public function cancelOrderItems(Mage_Sales_Model_Order $order, array $items) {
		/* @var $xmlRequestFactory Dhl_MeinPaket_Model_Xml_XmlRequestFactory */
		$xmlRequestFactory = Mage::getModel ( 'meinpaket/Xml_XmlRequestFactory' );
		
		/* @var $xmlResponseParser Dhl_MeinPaket_Model_Xml_XmlResponseParser */
		$xmlResponseParser = Mage::getModel ( 'meinpaket/Xml_XmlResponseParser' );
		
		/* @var $httpClient Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$httpClient = Mage::getModel ( 'meinpaket/Client_XmlOverHttp' );
		
		/* @var $result Dhl_MeinPaket_Model_Service_Order_CancellationService_Result */
		$result = Mage::getModel ( 'meinpaket/service_order_cancellationService_result' );
		
		$requestXml = '';
		$responseXml = '';
		
		try {
			// TODO: see Observer line 220
			$response = $httpClient->send ( $xmlRequestFactory->createPartialOrderCancellationRequest ( $order, $items ) );
			$xmlResponseParser->parseOrderCancellationResultFromXml ( $responseXml, $result );
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			throw $e;
		}
		
		return $result;
	}
}
