<?php

/**
 * Service class which encapsulates the product export process.
 * 
 * @category	Mage
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Service_Product
 */
class Dhl_MeinPaket_Model_Service_Product_Export extends Dhl_MeinPaketCommon_Model_Service_Abstract {
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
		$cycleCount = Mage::getStoreConfig ( 'meinpaket/advanced/cycle_product_count' );
		
		$seenMagentoProducts = array ();
		
		$uploadRequest = new Dhl_MeinPaketCommon_Model_Xml_Request_UploadRequest ();
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$client = Mage::getModel ( 'meinpaketcommon/client_xmlOverHttp' );
		
		$count = 0;
		
		/* @var $productBacklogs Dhl_MeinPaket_Model_Mysql4_Backlog_Product_Collection */
		$productBacklogs = Mage::getModel ( 'meinpaket/backlog_product' )->getCollection ();
		$productBacklogs->setPageSize ( 100 )->setCurPage ( 1 );
		
		while ( $count < $cycleCount && $productBacklogs->count () > 0 && $productBacklogs->getCurPage () <= $productBacklogs->getLastPageNumber () ) {
			foreach ( $productBacklogs as $productBacklog ) {
				$productId = $productBacklog->getProductId ();
				
				$changes = explode ( ',', $productBacklog->getChanges () );
				
				try {
					if (! isset ( $seenMagentoProducts [$productId] )) {
						$seenMagentoProducts [$productId] = true;
						
						/* @var $product Mage_Catalog_Model_Product */
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
								$uploadRequest->removeProduct ( $product );
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
			
			// Set next page
			$productBacklogs->setCurPage ( $productBacklogs->getCurPage () + 1 );
		}
		
		if ($uploadRequest->isHasData ()) {
			$response = $client->send ( $uploadRequest, true );
		}
		
		if ($productBacklogs->count () >= $cycleCount) {
			Mage::helper ( 'meinpaket/cron' )->scheduleJobs ( array (
					Dhl_MeinPaket_Model_Cron::SYNC_ASYNC 
			), false );
		}
		
		return Mage::helper ( 'meinpaket/data' )->__ ( "Processed %d products", $count );
	}
	
	/**
	 * Exports products.
	 *
	 * @param Mage_Catalog_Model_Product $product        	
	 * @return Dhl_MeinPaket_Model_Service_Product_Export_Result
	 */
	public function deleteProduct(Mage_Catalog_Model_Product $product) {
		$uploadRequest = new Dhl_MeinPaketCommon_Model_Xml_Request_UploadRequest ();
		
		/* @var $client Dhl_MeinPaket_Model_Client_XmlOverHttp */
		$client = Mage::getModel ( 'meinpaketcommon/client_xmlOverHttp' );
		
		$uploadRequest->removeProduct ( $product );
		
		if ($uploadRequest->isHasData ()) {
			$response = $client->send ( $uploadRequest, true );
		}
		
		return Mage::helper ( 'meinpaket/data' )->__ ( "Processed %d products", $count );
	}
}
