<?php

$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('ewall_vendor_delivery')};
CREATE TABLE `{$this->getTable('ewall_vendor_delivery')}` (
`vendor_delivery_id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(11) unsigned NOT NULL,
`delivery_id` int(10) unsigned NOT NULL,
`price` decimal(12,4) default NULL,
PRIMARY KEY  (`vendor_delivery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");


$this->endSetup();
