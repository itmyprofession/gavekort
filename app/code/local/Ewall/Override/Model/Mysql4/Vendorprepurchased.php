<?php

class Ewall_Override_Model_Mysql4_Vendorprepurchased extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('override/vendorprepurchased', 'id');
	}
}
