<?php
class Dhl_MeinPaket_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup {
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
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => false,
										'is_visible' => false,
										'required' => false,
										'user_defined' => false,
										'searchable' => false,
										'filterable' => false,
										'comparable' => false,
										'visible_on_front' => false,
										'visible_in_advanced_search' => false,
										'unique' => false,
										'is_configurable' => false 
								),
								'sync_with_dhl_mein_paket' => array (
										'type' => 'int',
										'label' => 'Sync with MeinPaket.de',
										'frontend' => 'meinpaket/entity_attribute_frontend_labelTranslation',
										'input' => 'select',
										'source' => 'meinpaket/entity_attribute_source_productSyncMode',
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => false,
										'required' => false,
										'user_defined' => false,
										'searchable' => false,
										'filterable' => false,
										'comparable' => false,
										'visible_on_front' => false,
										'visible_in_advanced_search' => false,
										'unique' => false 
								),
								'max_stock_for_dhl_mein_paket' => array (
										'type' => 'int',
										'label' => 'Maximum stock qty. for MeinPaket.de',
										'frontend' => 'meinpaket/entity_attribute_frontend_labelTranslation',
										'input' => 'text',
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => false,
										'required' => false,
										'user_defined' => false,
										'searchable' => false,
										'filterable' => false,
										'comparable' => false,
										'visible_on_front' => false,
										'visible_in_advanced_search' => false,
										'unique' => false 
								),
								'meinpaket_category' => array (
										'type' => 'text',
										'label' => 'DHL MeinPaket Categories',
										'input' => 'select',
										'source' => 'meinpaket/entity_attribute_source_meinPaketCategory',
										'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
										'visible' => false,
										'required' => false,
										'user_defined' => false,
										'searchable' => false,
										'filterable' => false,
										'comparable' => false,
										'visible_on_front' => false,
										'visible_in_advanced_search' => false,
										'unique' => false 
								) 
						) 
				),
				'order' => array (
						'entity_model' => 'sales/order',
						'table' => 'sales/order',
						'increment_model' => 'eav/entity_increment_numeric',
						'increment_per_store' => true,
						'backend_prefix' => 'sales_entity/order_attribute_backend',
						'attributes' => array (
								'meinpaket_id' => array (
										'type' => 'int',
										'label' => 'Order MeinPaket Id',
										'required' => false,
										'is_visible' => false,
										'visible' => false 
								) 
						) 
				) ,
				'customer' => array (
						'entity_model' => 'customer/customer',
						'attribute_model' => 'customer/attribute',
						'table' => 'customer/entity',
						'increment_model' => 'eav/entity_increment_numeric',
						'additional_attribute_table' => 'customer/eav_attribute',
						'entity_attribute_collection' => 'customer/attribute_collection',
						'attributes' => array (
								'meinpaket_buyer_id' => array (
										'type' => 'int',
										'label' => 'VIA eBay Buyer Id',
										'input' => 'text',
										'required' => false,
										'sort_order' => 200,
										'visible' => false 
								),
								'meinpaket_buyer_name' => array (
										'type' => 'varchar',
										'label' => 'VIA eBay Buyer Name',
										'input' => 'text',
										'required' => false,
										'sort_order' => 201,
										'visible' => false 
								) 
						) 
				) 
		);
	}
}
