<?php

/**
 * Service class which encapsulates the product export process.
 * 
 * @category	Mage
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Service_Product
 */
class Dhl_MeinPaket_Model_Service_Product_Export extends Dhl_MeinPaket_Model_Service_Abstract {
	
	/**
	 *
	 * @var Dhl_MeinPaket_Helper_Product
	 */
	protected $_productHelper = null;
	
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->_processableProducts = array ();
		$this->_productHelper = Mage::helper ( 'meinpaket/product' );
	}
	
	/**
	 * Exports products.
	 *
	 * @param integer $selectionMode        	
	 * @return Dhl_MeinPaket_Model_Service_Product_Export_Result
	 */
	public function exportProducts() {
		$seenMagentoProducts = array ();
		
		/* @var $productBacklogs Dhl_MeinPaket_Model_Mysql4_Backlog_Product_Collection */
		$productBacklogs = Mage::getModel ( 'meinpaket/backlog_product' )->getCollection ();
		
		/* @var $uploadRequest Dhl_MeinPaket_Model_Xml_Request_UploadRequest */
		$uploadRequest = Mage::getModel ( 'meinpaket/xml_request_uploadRequest' );
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$client = Mage::getModel ( 'meinpaket/client_xmlOverHttp' );
		
		$count = 0;
		
		foreach ( $productBacklogs as $productBacklog ) {
			$productId = $productBacklog->getProductId ();
			
			$changes = explode ( ',', $productBacklog->getChanges () );
			
			try {
				if (! isset ( $seenMagentoProducts [$productId] )) {
					$seenMagentoProducts [$productId] = true;
					
					$product = Mage::getModel ( 'catalog/product' )->load ( $productId );
					
					$syncMode = $product->getData ( 'sync_with_dhl_mein_paket' );
					
					switch ($syncMode) {
						case Dhl_MeinPaket_Model_Entity_Attribute_Source_ProductSyncMode::COMPLETE :
							$uploadRequest->addProductDescription ( $product );
							break;
						case Dhl_MeinPaket_Model_Entity_Attribute_Source_ProductSyncMode::OFFER :
							$uploadRequest->addOffer ( $product );
							break;
						default :
							$uploadRequest->removeProduct($product);
							break;
					}
				} else {
					Mage::log ( 'Product m' . $productId . ' already synced' );
				}
				
				$count ++;
				
				$productBacklog->delete ();
			} catch ( Exception $ex ) {
				Mage::logException ( $ex );
				// TODO: add error
				// $result ['error'] ++;
				Mage::log ( 'Error syncing product m' . $productId );
			}
		}
		
		if ($uploadRequest->isHasData()) {
			$response = $client->send ( $uploadRequest, true );
			$this->_markAsExported ();
		}
		
		return Mage::helper ( 'meinpaket/data' )->__ ( "Processed %d products", $count );
	}
	
	/**
	 * Sets the was_exported_for_dhl_mein_paket attribute to "1" for the
	 * products with the given product ids.
	 *
	 * @param array $productIds        	
	 * @return void
	 */
	protected function _markAsExported() {
		
		// $product = Mage::getModel ( 'catalog/product' );
		
		// foreach ( $this->_result->getOfferedProductIds () as $productId ) {
		// $product->load ( $productId );
		// $product->setData ( 'product_was_exported_for_dhl', 1 );
		// $product->getResource ()->saveAttribute ( $product, 'product_was_exported_for_dhl' );
		// }
	}
}
