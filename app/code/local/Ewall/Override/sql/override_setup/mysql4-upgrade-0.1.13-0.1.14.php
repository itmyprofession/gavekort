<?php

$installer = $this;

$installer->startSetup();

$sales = new Mage_Sales_Model_Mysql4_Setup();
$sales->addAttribute("shipment", "api_order_details", array("type"=>"text"));

$installer->endSetup();
