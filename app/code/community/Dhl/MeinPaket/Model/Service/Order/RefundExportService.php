<?php

/**
 * Service class which sends a refund to MeinPaket.de.
 * 
 * @category    Dhl
 * @package     Dhl_MeinPaket
 * @subpackage  Model_Service_RefundExport
 * @version     $Id$
 */
class Dhl_MeinPaket_Model_Service_Order_RefundExportService {
	/**
	 * Exports the given refunded creditmemo to MeinPaket.de.
	 *
	 * @param Mage_Sales_Model_Order_Creditmemo $creditmemo        	
	 * @throws Dhl_MeinPaket_Model_Xml_XmlBuildException
	 * @throws Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException
	 * @throws Dhl_MeinPaket_Model_Client_HttpTimeoutException
	 * @throws Dhl_MeinPaket_Model_Xml_InvalidXmlException
	 * @return Dhl_MeinPaket_Model_Sevice_RefundExport_Result
	 */
	public function exportRefund(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
		/* @var $httpClient Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$httpClient = Mage::getModel ( 'meinpaket/client_xmlOverHttp' );
		
		/* @var $notificationRequest Dhl_MeinPaket_Model_Xml_Request_NotificationRequest */
		$notificationRequest = Mage::getModel ( 'meinpaket/xml_request_notificationRequest' );
		
		$order = $creditmemo->getOrder ();
		if ($order->hasShipments ()) {
			foreach ( $order->getShipmentsCollection () as $shipment ) {
				$notificationRequest->addCreditMemo ( $creditmemo, $shipment );
			}
		} else {
			$notificationRequest->addCancellation ( $creditmemo->getOrder () );
		}
		
		try {
			if ($notificationRequest->isHasData()) {
				return $httpClient->send ( $notificationRequest );
			}
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			throw $e;
		}
		
		return null;
	}
}

