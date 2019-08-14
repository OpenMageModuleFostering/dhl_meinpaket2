<?php

/**
 * Represents the XML structure of an <uploadRequest> element.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Xml_Partial
 * @subpackage	Dhl_MeinPaket_Model_Xml_Partial
 */
class Dhl_MeinPaket_Model_Xml_Request_UploadRequest extends Dhl_MeinPaket_Model_Xml_AbstractXmlRequest {
	/**
	 *
	 * @var DOMNode
	 */
	protected $productDescriptions = null;
	/**
	 *
	 * @var DOMNode
	 */
	protected $offers = null;
	/**
	 *
	 * @var DOMNode
	 */
	protected $variantGroups = null;
	/**
	 *
	 * @var DOMNode
	 */
	protected $trackingNumbers = null;
	/**
	 *
	 * @var DOMNode
	 */
	protected $categories = null;
	/**
	 *
	 * @var DOMNode
	 */
	protected $deletions = null;
	
	/**
	 *
	 * @var Dhl_MeinPaket_Helper_Product
	 */
	protected $productHelper = null;
	
	/**
	 * Default Constructor.
	 */
	public function __construct() {
		parent::__construct ();
		$this->productHelper = Mage::helper ( 'meinpaket/product' );
	}
	
	/**
	 * Create the root element for the document.
	 *
	 * @return DOMNode
	 */
	public function createDocumentElement() {
		$this->node = $this->getDocument ()->createElement ( 'uploadRequest' );
		$this->node->setAttribute ( 'xmlns', self::XMLNS_PRODUCTS );
		$this->node->setAttribute ( 'xmlns:common', self::XMLNS_COMMON );
		$this->node->setAttribute ( 'version', '1.0' );
		$this->getDocument ()->appendChild ( $this->node );
	}
	
	/**
	 *
	 * @param Mage_Catalog_Model_Product $product        	
	 * @return DOMNode|Ambigous <boolean, DOMElement>
	 */
	public function addProductDescription(Mage_Catalog_Model_Product $product) {
		switch ($product->getTypeId ()) {
			case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE :
				return $this->handleSimpleProduct ( $product );
			case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE :
				return $this->handleConfigurableProduct ( $product );
			default :
				Mage::log ( 'Unhandled typeId ' . $product->getTypeId () );
		}
	}
	
	/**
	 *
	 * @param Mage_Catalog_Model_Product $product        	
	 * @return DOMElement
	 */
	public function addOffer(Mage_Catalog_Model_Product $product) {
		$offer = $this->getDocument ()->createElement ( 'productOffer' );
		
		// product id
		$offer->appendChild ( $this->getDocument ()->createElement ( 'common:productId', $product->getId () ) );
		
		// ean
		$ean = Mage::helper ( 'meinpaket/product' )->getEan ( $product );
		if (! empty ( $ean )) {
			$offer->appendChild ( $this->getDocument ()->createElement ( 'common:ean', $ean ) );
		}
		
		// manufacturer
		$manufacturer = $product->getAttributeText ( 'manufacturer' );
		if (! empty ( $manufacturer )) {
			$offer->appendChild ( $this->getCDATANode ( 'common:manufacturerName', $manufacturer ) );
		}
		
		// price
		$offer->appendChild ( $this->getDocument ()->createElement ( 'price', $product->getPrice () ) );
		
		// tax group
		$taxGroup = $this->productHelper->getMeinPaketTaxGroup ( $product );
		$offer->appendChild ( $this->getDocument ()->createElement ( 'taxGroup', $taxGroup ) );
		
		// availability
		$availability = $this->productHelper->getMeinPaketStock ( $product );
		$offer->appendChild ( $this->getDocument ()->createElement ( 'availability', $availability ) );
		
		// abuse enddate for activation and deactivation
		// $offer->appendChild ( $this->getDocument ()->createElement ( 'endDate', $this->getIsoDateTime ( now () ) ) );
		
		// deliverytime
		$deliveryTime = $this->productHelper->getMeinPaketDeliveryTime ( $product );
		if ($deliveryTime !== false) {
			$offer->appendChild ( $this->getDocument ()->createElement ( 'deliverytime', $deliveryTime ) );
		}
		
		$this->getOffers ()->appendChild ( $offer );
		
		$this->setHasData ();
		
		return $offer;
	}
	public function removeProduct(Mage_Catalog_Model_Product $product) {
		if ($product->getData ( 'meinpaket_id' )) {
			/* @var $productDeletion DOMNode */
			$productDeletionNode = $this->getDocument ()->createElement ( 'productDeletion' );
			$this->getDeletions ()->appendChild ( $productDeletionNode );
			$productDeletionNode->appendChild ( $this->getDocument ()->createElement ( 'common:productId', $product->getId () ) );
		}
	}
	public function addCategory(Mage_Catalog_Model_Product $product) {
		$productDescriptions = Mage::getModel ( 'meinpaket/xml_partial_productDescriptions', $this->getDocument () );
		
		/* @var $offers Dhl_MeinPaket_Model_Xml_Partial_Offers */
		$offers = Mage::getModel ( 'meinpaket/xml_partial_offers' );
	}
	public function removeCategory(Mage_Catalog_Model_Product $product) {
	}
	protected function getProductDescriptions() {
		if ($this->productDescriptions == null) {
			$this->productDescriptions = $this->getDocument ()->createElement ( 'descriptions' );
			$this->getDocumentElement ()->appendChild ( $this->productDescriptions );
		}
		return $this->productDescriptions;
	}
	
	/**
	 *
	 * @return DOMElement
	 */
	protected function getOffers() {
		if ($this->offers == null) {
			$this->offers = $this->getDocument ()->createElement ( 'offers' );
			$this->getDocumentElement ()->appendChild ( $this->offers );
		}
		return $this->offers;
	}
	
	/**
	 *
	 * @return DOMElement
	 */
	protected function getVariantGroups() {
		if ($this->variantGroups == null) {
			$this->variantGroups = $this->getDocument ()->createElement ( 'variantGroups' );
			$this->getDocumentElement ()->appendChild ( $this->variantGroups );
		}
		return $this->variantGroups;
	}
	
	/**
	 *
	 * @return DOMElement
	 */
	protected function getTrackingNumbers() {
		if ($this->trackingNumbers == null) {
			$this->trackingNumbers = $this->getDocument ()->createElement ( 'trackingNumbers' );
			$this->getDocumentElement ()->appendChild ( $this->trackingNumbers );
		}
		return $this->trackingNumbers;
	}
	
	/**
	 *
	 * @return DOMElement
	 */
	protected function getCategories() {
		if ($this->categories == null) {
			$this->categories = $this->getDocument ()->createElement ( 'categories' );
			$this->getDocumentElement ()->appendChild ( $this->categories );
		}
		return $this->categories;
	}
	
	/**
	 *
	 * @return DOMElement
	 */
	protected function getDeletions() {
		if ($this->deletions == null) {
			$this->deletions = $this->getDocument ()->createElement ( 'deletions' );
			$this->getDocumentElement ()->appendChild ( $this->deletions );
		}
		return $this->deletions;
	}
	
	/**
	 * Export a single simple product.
	 *
	 * @param Mage_Catalog_Model_Product $product
	 *        	to be exported
	 * @param string $variantGroup
	 *        	the product belongs to if available
	 * @throws Dhl_MeinPaket_Model_Exception_InvalidDataException
	 * @throws Dhl_MeinPaket_Model_Exception_MissingDataException
	 * @return DOMNode
	 */
	protected function handleSimpleProduct(Mage_Catalog_Model_Product $product, Mage_Catalog_Model_Product $configurableProduct = null) {
		if ($configurableProduct == null) {
			$type = Mage::getModel ( 'catalog/product_type_configurable' );
			$parentIdArray = $type->getParentIdsByChild ( $product->getId () );
			if (isset ( $parentIdArray [0] )) {
				$configurableProduct = Mage::getModel ( 'catalog/product' )->load ( $parentIdArray [0] );
			}
		}
		
		if (! $product->getData ( 'sync_with_dhl_mein_paket' ) && $configurableProduct == null) {
			/* @var $productDeletion DOMNode */
			$productDeletion = $this->getDocument ()->createElement ( 'productDeletion' );
			$this->getDeletions ()->appendChild ( $productDeletion );
			$pId = $this->getDocument ()->createElement ( 'common:productId', $product->getId () );
			// TODO:
			// $productDeletion->appendChild ( $pId );
			return false;
		}
		
		/* @var $productDescription DOMNode */
		$productDescription = $this->getDocument ()->createElement ( 'productDescription' );
		$variantInfo = null;
		
		// product id
		$productId = $this->getDocument ()->createElement ( 'common:productId', $product->getId () );
		$productDescription->appendChild ( $productId );
		
		// ean (optional)
		$ean = Mage::helper ( 'meinpaket/product' )->getEan ( $product );
		if (! empty ( $ean )) {
			$productDescription->appendChild ( $this->getDocument ()->createElement ( 'common:ean', $ean ) );
		}
		
		// manufacturer
		$manufacturer = $product->getAttributeText ( 'manufacturer' );
		if (! empty ( $manufacturer )) {
			$productDescription->appendChild ( $this->getCDATANode ( 'common:manufacturerName', $manufacturer ) );
		}
		
		if ($configurableProduct != NULL) {
			$variantGroupInfoNode = $this->getDocument ()->createElement ( "variantGroupInfo" );
			$variantGroupInfoNode->setAttribute ( "code", $configurableProduct->getId () );
			$productDescription->appendChild ( $variantGroupInfoNode );
		}
		
		// name
		if (strlen ( $product->getName () ) > 0) {
			$productDescription->appendChild ( $this->getCDATANode ( 'name', $product->getName () ) );
		} else {
			throw new Dhl_MeinPaket_Model_Exception_InvalidDataException ( $product->getId (), 'name' );
		}
		
		// shortdescription
		if (strlen ( $product->getShortDescription () ) > 0) {
			$shortDescription = $this->escapeStringForMeinPaket ( $product->getShortDescription () );
			$productDescription->appendChild ( $this->getCDATANode ( 'shortDescription', $shortDescription ) );
		} else {
			throw new Dhl_MeinPaket_Model_Exception_MissingDataException ( $product->getId (), 'short_description' );
		}
		
		// long description (optional) && (strlen > 0)
		if (strlen ( $product->getDescription () ) > 0) {
			$description = $this->escapeStringForMeinPaket ( $product->getDescription () );
			$productDescription->appendChild ( $this->getCDATANode ( 'longDescription', $description ) );
		}
		
		// Export images
		$images = $product->getMediaGalleryImages ();
		if ($images != null) {
			foreach ( $images as $image ) {
				$smallOrImage = false;
				
				if ($image->getDisabled ()) {
					continue;
				}
				
				$imageNode = $this->getDocument ()->createElement ( "image" );
				$productDescription->appendChild ( $imageNode );
				$imageNode->appendChild ( $this->getDocument ()->createElement ( "url", $image->getUrl () ) );
				
				$label = $image->getLabel ();
				if (! empty ( $label )) {
					$imageNode->appendChild ( $this->getDocument ()->createElement ( "caption", $image->getLabel () ) );
				}
			}
		}
		
		// marketplace category
		$meinpaketCategory = $product->getData ( 'meinpaket_category' );
		if (strlen ( $meinpaketCategory ) > 0) {
			$mNode = $this->getDocument ()->createElement ( 'marketplaceCategory' );
			$mNode->setAttribute ( 'code', $meinpaketCategory );
			$productDescription->appendChild ( $mNode );
		} else {
			throw new Dhl_MeinPaket_Model_Exception_MissingDataException ( $product->getId (), 'dhl_marketplace_category_id' );
		}
		
		$this->exportAttributes ( $product, $productDescription, $configurableProduct );
		$this->getProductDescriptions ()->appendChild ( $productDescription );
		$this->addOffer ( $product );
		
		$this->setHasData ();
		
		return $productDescription;
	}
	
	/**
	 * Export product attributes.
	 */
	protected function exportAttributes(Mage_Catalog_Model_Product $product, DOMNode $node, Mage_Catalog_Model_Product $configurableProduct = null) {
		$configurableAttributes = array ();
		
		if ($configurableProduct != null && $configurableProduct->getTypeId () == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
			foreach ( $configurableProduct->getTypeInstance ( true )->getConfigurableAttributes ( $configurableProduct ) as $attr ) {
				$configurableAttributes [] = $attr->getProductAttribute ()->getAttributeCode ();
			}
		}
		
		foreach ( $product->getAttributes () as $attribute ) {
			/* @var $attribute Mage_Eav_Model_Attribute */
			
			$isConfigurable = in_array ( $attribute->getAttributeCode (), $configurableAttributes );
			
			if (strlen ( $attribute->getMeinpaketAttribute () ) <= 0 || $attribute->getMeinpaketAttribute () == 'None') {
				continue;
			}
			
			$productValue = $product->getData ( $attribute->getAttributeCode () );
			
			if ($attribute->isValueEmpty ( $productValue ) || empty ( $productValue )) {
				continue;
			}
			
			$frontendValue = $attribute->getFrontend ()->getValue ( $product );
			
			if (empty ( $frontendValue )) {
				continue;
			}
			
			$storeLabel = $attribute->getFrontendLabel ();
			
			if (strlen ( $storeLabel ) <= 0) {
				continue;
			}
			
			$attributeNode = $this->getDocument ()->createElement ( "attribute" );
			if ($isConfigurable) {
				$attributeNode->setAttribute ( "variant", "true" );
			}
			$attributeNode->setAttribute ( "code", $attribute->getMeinpaketAttribute () == 'Default' ? $attribute->getAttributeCode () : $attribute->getMeinpaketAttribute () );
			
			$nameNode = $this->getDocument ()->createElement ( "name", $storeLabel );
			$attributeNode->appendChild ( $nameNode );
			$valueNode = $this->getDocument ()->createElement ( "value", $frontendValue );
			$attributeNode->appendChild ( $valueNode );
			
			$node->appendChild ( $attributeNode );
		}
	}
	
	/**
	 * Export a configurable product.
	 *
	 * @param Mage_Catalog_Model_Product $product        	
	 */
	protected function handleConfigurableProduct(Mage_Catalog_Model_Product $product) {
		if (! $product->getData ( 'sync_with_dhl_mein_paket' )) {
			/* @var $productDescription DOMNode */
			$productDeletion = $this->getDocument ()->createElement ( 'productDeletion' );
			$this->getDeletions ()->appendChild ( $productDeletion );
			$pId = $this->getDocument ()->createElement ( 'common:productId', $product->getId () );
			$productDeletion->appendChild ( $pId );
			return false;
		}
		
		if ($product->getTypeId () != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
			return false;
		}
		
		$typeInstance = $product->getTypeInstance ( true );
		$configurableType = $typeInstance->setProduct ( $product );
		$variantGroupNode = null;
		
		$variantGroupNode = $this->getDocument ()->createElement ( "variantGroup" );
		$variantGroupNode->setAttribute ( "code", $product->getId () );
		// $this->getVariantGroups ()->appendChild ( $variantGroupNode );
		
		/*
		 * $configurationNode = $this->getDocument ()->createElement ( "configuration" ); $variantGroupNode->appendChild ( $configurationNode ); $configurationNode->setAttribute ( "code", $variantMapping->getMeinpaketVariantId () );
		 */
		
		$titleNode = $this->getDocument ()->createElement ( "title" );
		$variantGroupNode->appendChild ( $titleNode );
		$titleNode->appendChild ( $this->getDocument ()->createTextNode ( $product->getName () ) );
		
		$simpleCollection = $configurableType->getUsedProductCollection ()->addAttributeToSelect ( '*' )->addFilterByRequiredOptions ();
		foreach ( $simpleCollection as $simpleProduct ) {
			$this->handleSimpleProduct ( $simpleProduct, $product );
		}
		
		return $variantGroupNode;
	}
}
