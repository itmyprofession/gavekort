<?php
$installer = $this;

$setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');

$installer->startSetup();

$setup->addAttribute('catalog_product', 'delivery_method', array(
	'group' => 'General',
	'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'type' => 'int',
    'input' => 'multiselect',
    'label' => 'Delivery Method',
    'global' => 2,
    'user_defined' => 1,
    'required' => 1,
    'visible' => 1,
    'used_in_product_listing'       => true,
    'source' => 'override/deliverymethods_source',
));

$setup->endSetup();
