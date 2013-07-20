<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('udropship_vendor')}` ADD COLUMN `vendor_timed_dispatch` INT NOT NULL;
ALTER TABLE `{$this->getTable('udropship_vendor')}` ADD COLUMN `vendor_timed_dispatch_no` INT NOT NULL;
");
$installer->endSetup();
