<?php

$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('ewall_vendor_products_deliverymethods')};
CREATE TABLE `{$this->getTable('ewall_vendor_products_deliverymethods')}` (
`id` int(11) unsigned NOT NULL auto_increment,
`product_id` int(11) unsigned NOT NULL,
`vendor_id` int(11) unsigned NOT NULL,
`delivery_ids` text NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
