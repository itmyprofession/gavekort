<?php

class Ewall_Override_Block_Adminhtml_Catalog_Product_Prepurchasedcode_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("vendorprepurchasedGrid");
		$this->setDefaultSort("used");
		$this->setDefaultDir("DESC");
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("override/vendorprepurchased")->getCollection();
		$pid = $this->getParam('id');
		if($this->getRequest()->getControllerName()=='catalog_product' && $this->getRequest()->getActionName()=='new') {
			$pid = 0;
		}
		$collection->addFieldToFilter('pid',$pid);
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
		$this->addColumn("code", array(
			"header" => Mage::helper("override")->__("GC Code"),
			"index" => "code",
		));
		$this->addColumn("used", array(
			"header" => Mage::helper("override")->__("Usage"),
			"index" => "used",
			"type" => "options",
			"options" => array(0 => "Unused", 1 => "Used")
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
	   return 'javascript:void(0)';
	}
	
	public function getGridUrl()
	{
		return Mage::helper("adminhtml")->getUrl("override/adminhtml_deliverymethods/prepurchasedgrid");
	}
}
