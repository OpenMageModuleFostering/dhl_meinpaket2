<?php

/**
 * Abstract base class of all partials.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Xml
 * @version		$Id$
 * @author		Daniel Pötzinger <daniel.poetzinger@aoemedia.de>
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
abstract class Dhl_MeinPaket_Model_Xml_AbstractXmlPartial {
	/**
	 *
	 * @var DOMNode
	 */
	protected $node = null;
	
	/**
	 *
	 * @var DOMDocument
	 */
	protected $document = null;

	/**
	 * Default constructor.
	 *
	 * @param DOMDocument $document        	
	 */
	public function __construct(DOMDocument $document) {
		$this->document = $document;
		
		if ($this->document == null || ! ($this->document instanceof DOMDocument)) {
			throw new InvalidArgumentException ( "Invalid DOMDocument given" );
		}
	}
	
	/**
	 * Returns the DOM node of the element.
	 * The build() method has to be called before. Otherwise
	 * this method will return null.
	 *
	 * @return DOMNode
	 */
	public function getNode() {
		return $this->node;
	}
	
	/**
	 * Returns the associated document.
	 *
	 * @return DOMDocument
	 */
	protected function getDocument() {
		return $this->document;
	}
	/**
	 * Returns the associated root document.
	 *
	 * @return DOMNode
	 */
	protected function getDocumentElement() {
		if ($this->document != null) {
			return $this->document->documentElement;
		}
		return null;
	}
	
	/**
	 * Sets the DOM document.
	 *
	 * @param DOMDocument $document        	
	 * @return Dhl_MeinPaket_Model_Xml_AbstractXmlPartial
	 */
	public function setDocument(DOMDocument $document) {
		$this->document = $document;
		return $this;
	}
	
	/**
	 *
	 * @param integer $time        	
	 * @return string
	 */
	protected function getFormatedDate($time) {
		$date = date ( 'c', $time );
		$dateExploded = explode ( '+', $date );
		return $dateExploded [0];
	}
	
	/**
	 * Builds the DOM node.
	 *
	 * @return DOMNode
	 */
	abstract public function build();
	
	/**
	 * Creates a CDATA noe.
	 *
	 * @param string $nodeName
	 *        	name.
	 * @param string $content        	
	 * @return DOMElement
	 */
	protected function getCDATANode($nodeName, $content) {
		$node = $this->getDocument ()->createElement ( $nodeName );
		$node->appendChild ( $this->getDocument ()->createCDATASection ( $content ) );
		return $node;
	}
	
	/**
	 * Simple validation for element content.
	 *
	 * @param string $content        	
	 * @param string $validatorType        	
	 * @return boolean
	 */
	protected function isValid($content, $validatorType) {
		$valid = false;
		
		switch ($validatorType) {
			case 'nonEmptyString' :
				$valid = Mage::getModel ( 'meinpaket/Validation_Validator_NonEmptyString' )->isValid ( $content );
				break;
			case 'ean' :
				$valid = Mage::getModel ( 'meinpaket/Validation_Validator_Ean' )->isValid ( $content );
				break;
		}
		
		return $valid;
	}
	
	/**
	 * Returns an instance of Dhl_MeinPaket_Model_Validation_ValidatorFactory.
	 *
	 * @return Dhl_MeinPaket_Model_Validation_ValidatorFactory
	 */
	protected function getValidatorFactory() {
		return Mage::getSingleton ( 'meinpaket/Validation_ValidatorFactory' );
	}
	
	/**
	 * Creates an ISO date string without trailing timezone offset for the
	 * given timestamp.
	 *
	 * @param integer $timestamp        	
	 * @return string
	 */
	public function getIsoDateTime($timestamp = null) {
		$isoString = '';
		$format = 'Y-m-d\TH:i:s';
		
		if ($timestamp === null) {
			$isoString = date ( $format );
		} else {
			$isoString = date ( $format, $timestamp );
		}
		
		return $isoString;
	}
	/**
	 * Convert HTML entities to UTF- 8special chars while leaving the already existing special chars intact
	 * TODO: Move to helper?
	 * @param string $string        	
	 * @return string
	 */
	protected function escapeStringForMeinPaket($string) {
		$string = utf8_decode ( $string );
		$string = html_entity_decode ( $string );
		$string = utf8_encode ( $string );
		
		return $string;
	}
	
	/**
	 * Create a string from created document.
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->getDocument ()->saveXML ();
	}
}

