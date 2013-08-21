<?php

class Ewall_Override_Model_Mysql4_Vendorproducts_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	/**
     * Object initialization using Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('override/vendorproducts');
    }
}
