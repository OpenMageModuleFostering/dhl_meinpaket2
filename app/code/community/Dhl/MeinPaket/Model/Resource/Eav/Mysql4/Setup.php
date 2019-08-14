<?php
class Dhl_MeinPaket_Model_Resource_Eav_Mysql4_Setup extends Mage_Catalog_Model_Resource_Setup {
	/**
	 *
	 * @return array
	 */
	public function getDefaultEntities() {
		return array (
				'catalog_product' => array (
						'entity_model' => 'catalog/product',
						'attribute_model' => 'catalog/resource_eav_attribute',
						'table' => 'catalog/product',
						'additional_attribute_table' => 'catalog/eav_attribute',
						'entity_attribute_collection' => 'catalog/product_attribute_collection',
						'attributes' => array (
								'meinpaket_id' => array (
										'type' => 'int',
										'label' => 'Product DHL MeinPaket Id',
										'required' => false,
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => false,
										'group' => 'DHL MeinPaket' 
								),
								'sync_with_dhl_mein_paket' => array (
										'type' => 'int',
										'label' => 'Sync with MeinPaket.de',
										'frontend' => 'meinpaket/entity_attribute_frontend_labelTranslation',
										'input' => 'select',
										'source' => 'meinpaket/entity_attribute_source_productSyncMode',
										'required' => false,
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => true,
										'group' => 'DHL MeinPaket' 
								),
								'max_stock_for_dhl_mein_paket' => array (
										'type' => 'int',
										'label' => 'Maximum stock qty. for MeinPaket.de',
										'frontend' => 'meinpaket/entity_attribute_frontend_labelTranslation',
										'input' => 'text',
										'required' => false,
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => true,
										'group' => 'DHL MeinPaket' 
								),
								'meinpaket_category' => array (
										'type' => 'text',
										'label' => 'DHL MeinPaket Category',
										'input' => 'select',
										'source' => 'meinpaket/entity_attribute_source_meinPaketCategory',
										'required' => false,
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => true,
										'group' => 'DHL MeinPaket' 
								) 
						) 
				) 
		);
	}
}
