<?php

/**
 * Result class which encapsulates information concerning the shipment export process.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Service_ShipmentExport
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Service_Async extends Dhl_MeinPaket_Model_Service_Abstract {
	/**
	 * Exports products.
	 *
	 * @param integer $selectionMode        	
	 * @return Dhl_MeinPaket_Model_Service_Product_Export_Result
	 */
	public function process() {
		$seenMagentoProducts = array ();
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$client = Mage::getModel ( 'meinpaket/client_xmlOverHttp' );
		
		/* var $logCollection Dhl_MeinPaket_Model_Mysql4_Log_Collection */
		$asyncCollection = Mage::getModel ( 'meinpaket/async' )->getCollection ();
		
		$count = 0;
		
		foreach ( $asyncCollection as $async ) {
			/* @var $uploadRequest Dhl_MeinPaket_Model_Xml_Request_AsynchronousStatusRequest */
			$statusRequest = Mage::getModel ( 'meinpaket/xml_request_asynchronousStatusRequest' );
			$statusRequest->addRequestStatus ( $async->getRequestId () );
			$response = $client->send ( $statusRequest );
			
			if ($response instanceof Dhl_MeinPaket_Model_Xml_Response_AsynchronousStatusResponse) {
				/* @var $response Dhl_MeinPaket_Model_Xml_Response_AsynchronousStatusResponse */
				$async->setRequestId ( $response->getRequestId () );
				$async->setStatus ( $response->getStatus () );
				$async->setUpdatedAt ( Varien_Date::now () );
				$async->save ();
			} else {
				$async->delete ();
			}
			
			$count ++;
		}
		
		return Mage::helper ( 'meinpaket/data' )->__ ( "Processed %d async jobs", $count );
	}
}

