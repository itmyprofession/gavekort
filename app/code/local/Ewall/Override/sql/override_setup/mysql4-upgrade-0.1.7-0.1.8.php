<?php

$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('ewall_vendor_products_prepurchased')};
CREATE TABLE `{$this->getTable('ewall_vendor_products_prepurchased')}` (
`id` int(11) unsigned NOT NULL auto_increment,
`code` text NOT NULL,
`pid` int(11) unsigned NOT NULL,
`used` int(11) unsigned NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
