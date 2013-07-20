<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('sales_flat_quote_item')}` ADD COLUMN `vendor_delivery_id` INT NOT NULL;
ALTER TABLE `{$this->getTable('sales_flat_order_item')}` ADD COLUMN `vendor_delivery_id` INT NOT NULL;
");
$installer->endSetup();
