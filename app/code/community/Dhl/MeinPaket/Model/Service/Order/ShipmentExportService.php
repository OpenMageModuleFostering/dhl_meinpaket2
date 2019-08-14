<?php

/**
 * Service which exports an order's shipment to MeinPaket.de.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Service_ShipmentExport
 * @version		$Id$
 */
class Dhl_MeinPaket_Model_Service_Order_ShipmentExportService {
	/**
	 * Exports a shipment to MeinPaket.de.
	 *
	 * @param Mage_Sales_Model_Order_Shipment $shipment        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @throws Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException
	 * @throws Dhl_MeinPaket_Model_Client_HttpTimeoutException
	 * @throws Dhl_MeinPaket_Model_Xml_InvalidXmlException
	 * @return Dhl_MeinPaket_Model_Xml_Response_NotificationResponse
	 */
	public function exportShipment(Mage_Sales_Model_Order_Shipment $shipment) {
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Request_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/xml_request_notificationRequest' );
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$client = Mage::getModel ( 'meinpaket/client_xmlOverHttp' );
		
		$notificationRequest->addConsignment ( $shipment );
		
		/* @var $responseXml Dhl_MeinPaket_Model_Xml_Response_NotificationResponse */
		$responseXml = $client->send ( $notificationRequest );
		
		return $responseXml;
	}
	
	/**
	 * Exports the tracking number of a shipment to MeinPaket.de.
	 *
	 * @param Mage_Sales_Model_Order_Shipment $shipment
	 *        	The shipment which tracking numbers shall be exported.
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @throws Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException
	 * @throws Dhl_MeinPaket_Model_Client_HttpTimeoutException
	 * @throws Dhl_MeinPaket_Model_Xml_InvalidXmlException
	 * @return Dhl_MeinPaket_Model_Service_TrackingNumberExport_Result
	 */
	public function exportTrackingNumber(Mage_Sales_Model_Order_Shipment_Track $track) {
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Request_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/xml_request_notificationRequest' );
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$client = Mage::getModel ( 'meinpaket/client_xmlOverHttp' );
		
		/* @var $result Dhl_MeinPaket_Model_Service_Product_Export_Result */
		$result = Mage::getModel ( 'meinpaket/service_product_export_result' );
		
		$notificationRequest->addTrackingNumber ( $track );
		
		try {
			$responseXml = $client->send ( $notificationRequest );
			// TODO: parse
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			throw $e;
		}
		
		return null;
	}
}
