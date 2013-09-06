<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('udropship_vendor')}` DROP COLUMN `vendor_api_url`;
ALTER TABLE `{$this->getTable('udropship_vendor')}` ADD COLUMN `vendor_api_shortname` varchar(255) NULL;
ALTER TABLE `{$this->getTable('udropship_vendor')}` ADD COLUMN `vendor_api_form_value` TEXT NULL;
");
$installer->endSetup();
