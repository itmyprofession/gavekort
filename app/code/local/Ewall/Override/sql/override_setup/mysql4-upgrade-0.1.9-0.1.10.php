<?php

$installer = $this;

$installer->startSetup();

$sql = "UPDATE `eav_attribute` SET `backend_type` =  'text' WHERE `eav_attribute`.`attribute_code` = 'note'";

$installer->run($sql);

$sql_code ="ALTER TABLE `eav_attribute` CHANGE `note` `note` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Note'";

$installer->run($sql_code);

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'ugiftcert_amount_config', 'note','Pattern examples:<br/><strong>50 - 1500</strong> : a range between $50 and $1500<br/><strong>25; 50; 100</strong> : a dropdown with values of $25, $50, $100<br/><strong>50</strong> : a static value of $50<br/><strong>-</strong> : enter "dash" to allow any amount value<br/>For multi-currency setups enter configuration for each currency on new line, like this:<br/><strong>EUR: 25; 50; 100<br/>CAD, USD: 50; 100; 200<br/>*: 100; 200; 500</strong><br/>Whitespaces are optional and will be ignored.');
