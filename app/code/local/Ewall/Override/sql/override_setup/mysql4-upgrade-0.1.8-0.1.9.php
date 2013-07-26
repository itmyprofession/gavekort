<?php

$this->startSetup();

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

$eav->updateAttribute('catalog_product', 'udropship_vendor', 'source_model', 'Ewall_Override_Model_Vendor_Source');

$cEav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

$cEav->updateAttribute('catalog_product', 'udropship_vendor', 'source_model', 'Ewall_Override_Model_Vendor_Source');
