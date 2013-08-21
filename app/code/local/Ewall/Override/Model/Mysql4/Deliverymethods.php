<?php

class Ewall_Override_Model_Mysql4_Deliverymethods extends Mage_Core_Model_Mysql4_Abstract
{
	/**
     * Object initialization using Constructor
     */
    protected function _construct()
    {
        $this->_init("override/deliverymethods", "delivery_id");
    }
}
