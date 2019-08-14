<?php

/**
 *
 * @author stephan
 *        
 */
class Dhl_MeinPaket_Model_Xml_Response_Partial_NotificationResponse_Confirmation extends Dhl_MeinPaket_Model_Xml_Response_Abstract {
	private $cancellationConfirmation = array ();
	private $consignmentConfirmation = array ();
	private $trackingNumberConfirmation = array ();
	private $returnConfirmation = array ();
	private $creditMemoConfirmation = array ();
	
	/**
	 *
	 * @param DOMElement $domElement        	
	 */
	public function __construct(DOMElement $domElement) {
		parent::__construct ( $domElement );
		assert ( $domElement->localName == 'confirmation' );
		
		foreach ( $domElement->childNodes as $childNode ) {
			switch ($childNode->localName) {
				case 'cancellation' :
					$this->cancellationConfirmation [] = new Dhl_MeinPaket_Model_Xml_Response_Partial_NotificationResponse_Consignment ( $childNode );
					break;
				case 'consignment' :
					$this->consignmentConfirmation [] = new Dhl_MeinPaket_Model_Xml_Response_Partial_NotificationResponse_Consignment ( $childNode );
					break;
				case 'trackingNumber' :
					$this->trackingNumberConfirmation [] = new Dhl_MeinPaket_Model_Xml_Response_Partial_NotificationResponse_Consignment ( $childNode );
					break;
				case 'return' :
					$this->returnConfirmation [] = new Dhl_MeinPaket_Model_Xml_Response_Partial_NotificationResponse_Return ( $childNode );
					break;
				case 'creditMemo' :
					$this->creditMemoConfirmation [] = new Dhl_MeinPaket_Model_Xml_Response_Partial_NotificationResponse_Credit ( $childNode );
					break;
			}
		}
	}
	public function getCancellationConfirmations() {
		return $this->cancellationConfirmation;
	}
	public function getConsignmentConfirmations() {
		return $this->consignmentConfirmation;
	}
	public function getTrackingNumberConfirmations() {
		return $this->trackingNumberConfirmation;
	}
	public function getReturnConfirmations() {
		return $this->returnConfirmation;
	}
	public function getCreditMemoConfirmations() {
		return $this->creditMemoConfirmation;
	}
}