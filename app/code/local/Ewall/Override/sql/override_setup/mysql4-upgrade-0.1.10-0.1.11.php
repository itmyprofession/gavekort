<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('udropship_vendor')}` ADD COLUMN `vendor_api_url` TEXT NULL;
");
$installer->endSetup();
