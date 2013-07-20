<?php

class Ewall_Override_Block_Adminhtml_Deliverymethods_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("deliverymethods_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle(Mage::helper("override")->__("Item Information"));
	}
	protected function _beforeToHtml()
	{
		$this->addTab("form_section", array(
		"label" => Mage::helper("override")->__("Item Information"),
		"title" => Mage::helper("override")->__("Item Information"),
		"content" => $this->getLayout()->createBlock("override/adminhtml_deliverymethods_edit_tab_form")->toHtml(),
		));
		return parent::_beforeToHtml();
	}
}
