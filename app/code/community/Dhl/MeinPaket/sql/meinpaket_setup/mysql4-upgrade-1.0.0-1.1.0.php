<?php

/* @var $installer Dhl_MeinPaket_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup ();

$installer->installEntities();

/*
$installer->run ( "
DROP TABLE IF EXISTS {$this->getTable('meinpaket/variants')};
CREATE TABLE {$this->getTable('meinpaket/variants')} (
  `variant_id` int(10) unsigned not null auto_increment ,
  `meinpaket_code` varchar(255) not null ,
  `meinpaket_name` varchar(255) not null ,
  `meinpaket_selection_rule` varchar(255) not null ,
  `meinpaket_index_as_group` tinyint(1) unsigned not null ,
  PRIMARY KEY (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='MeinPaket Variants';
" );

$installer->run ( "
DROP TABLE IF EXISTS {$this->getTable('meinpaket/attributes')};
CREATE TABLE {$this->getTable('meinpaket/attributes')} (
  `attribute_id` int(10) unsigned not null auto_increment ,
  `variant_id` int(10) unsigned not null ,
  `meinpaket_code` varchar(255) not null ,
  `meinpaket_name` varchar(255) not null ,
  PRIMARY KEY (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='MeinPaket Attributes';
" );

$installer->run ( "
DROP TABLE IF EXISTS {$this->getTable('meinpaket/values')};
CREATE TABLE {$this->getTable('meinpaket/values')} (
  `value_id` int(10) unsigned not null auto_increment ,
  `attribute_id` int(10) unsigned not null ,
  `value` varchar(255) not null ,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='MeinPaket Attribute Values';
" );

$installer->run ( "
DROP TABLE IF EXISTS {$this->getTable('meinpaket/variant_mappings')};
CREATE TABLE {$this->getTable('meinpaket/variant_mappings')} (
  `variant_mapping_id` int(10) unsigned not null auto_increment ,
  `meinpaket_variant_id` int(10) unsigned not null ,
  `attribute_set_id` int(10) unsigned not null ,
  PRIMARY KEY (`variant_mapping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='MeinPaket Variant to Magento Attribute Set Mapping';
" );

$installer->run ( "
DROP TABLE IF EXISTS {$this->getTable('meinpaket/attribute_mappings')};
CREATE TABLE {$this->getTable('meinpaket/attribute_mappings')} (
  `attribute_mapping_id` int(10) unsigned not null auto_increment ,
  `variant_mapping_id` int(10) unsigned not null ,
  `meinpaket_attribute_id` int(10) unsigned not null ,
  `attribute_id` int(10) unsigned not null ,
  PRIMARY KEY (`attribute_mapping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='MeinPaket Attribute to Magento Attribute Mapping';
" );

$installer->run ( "
DROP TABLE IF EXISTS {$this->getTable('meinpaket/value_mappings')};
CREATE TABLE {$this->getTable('meinpaket/value_mappings')} (
  `value_mapping_id` int(10) unsigned not null auto_increment ,
  `attribute_mapping_id` int(10) unsigned not null ,
  `meinpaket_value_id` int(10) unsigned not null ,
  `option_id` int(10) unsigned not null ,
  PRIMARY KEY (`value_mapping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='MeinPaket Value to Magento Option Mapping';
" );

$installer->endSetup ();
*/
