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
class Dhl_MeinPaket_Model_Service_Order_ShipmentExportService_Result extends Dhl_MeinPaket_Model_Service_Result_Abstract {
	/**
	 *
	 * @var string
	 */
	protected $consignmentId = '';
	
	/**
	 *
	 * @var string
	 */
	protected $responseConsignmentId = '_';
	
	/**
	 * Sets the consignment id of the shipment.
	 *
	 * @param string $consignmentId        	
	 * @return Dhl_MeinPaket_Model_Service_ShipmentExport_Result
	 */
	public function setConsignmentId($consignmentId) {
		$this->consignmentId = $consignmentId;
		return $this;
	}
	
	/**
	 * Sets the consignment id of the shipment which was returned from MeinPaket.de.
	 *
	 * @param string $consignmentId        	
	 * @return Dhl_MeinPaket_Model_Service_ShipmentExport_Result
	 */
	public function setResponseConsignmentId($responseConsignmentId) {
		$this->responseConsignmentId = $responseConsignmentId;
		return $this;
	}
	
	/**
	 * Tells if the send consignment id has been returned by MeinPaket.de.
	 * If this is true, the consignment has been accepted.
	 *
	 * @return boolean
	 */
	public function hasBeenAccepted() {
		return ($this->consignmentId === $this->responseConsignmentId);
	}
}

