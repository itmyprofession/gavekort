<?php
class Ewall_Override_Model_Vendorprepurchased extends Mage_Core_Model_Abstract
{
	/**
     * Object initialization using Constructor
     */
	public function _construct()
	{
		parent::_construct();
		$this->_init("override/vendorprepurchased");
	}
}
