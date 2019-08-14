<?php
class Dhl_MeinPaket_Model_Xml_Response_QueryResponse extends Dhl_MeinPaket_Model_Xml_Response_Abstract {
	/**
	 *
	 * @var $orders array
	 */
	private $orders = array ();
	
	/**
	 * Constructor
	 *
	 * @param unknown $domElement        	
	 */
	public function __construct(DOMElement $domElement) {
		parent::__construct ( $domElement );
		assert ( $domElement->localName == 'queryResponse' );
		
		foreach ( $domElement->childNodes as $queryResponseEntries ) {
			switch ($queryResponseEntries->localName) {
				case 'order' :
					$this->orders [] = new Dhl_MeinPaket_Model_Xml_Response_Partial_Order ( $queryResponseEntries );
					break;
			}
		}
	}
	
	/**
	 * 
	 * @return $orders
	 */
	public function getOrders() {
		return $this->orders;
	}
}
