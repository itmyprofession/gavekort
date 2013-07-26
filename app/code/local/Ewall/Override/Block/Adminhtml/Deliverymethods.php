<?php

class Ewall_Override_Block_Adminhtml_Deliverymethods extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = "adminhtml_deliverymethods";
		$this->_blockGroup = "override";
		$this->_headerText = Mage::helper("override")->__("Delivery Methods Manager");
		$this->_addButtonLabel = Mage::helper("override")->__("Add New Item");
		parent::__construct();
	}
}
