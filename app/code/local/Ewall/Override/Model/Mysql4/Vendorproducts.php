<?php

class Ewall_Override_Model_Mysql4_Vendorproducts extends Mage_Core_Model_Mysql4_Abstract
{
	/**
     * Object initialization using Constructor
     */
	public function _construct()
	{
		$this->_init('override/vendorproducts', 'id');
	}
}
