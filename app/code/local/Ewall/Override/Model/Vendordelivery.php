<?php
class Ewall_Override_Model_Vendordelivery extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init("override/vendordelivery");
	}
}
