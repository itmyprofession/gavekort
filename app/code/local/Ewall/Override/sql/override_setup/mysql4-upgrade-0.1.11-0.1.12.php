<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('udropship_vendor')}` ADD COLUMN `create_per_item_shipment` INT NOT NULL;
");
$installer->endSetup();
