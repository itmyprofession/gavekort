<?php

class Ewall_Override_Block_Adminhtml_Deliverymethods_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("override_form", array("legend"=>Mage::helper("override")->__("Item information")));
		$fieldset->addField("title", "text", array(
		"label" => Mage::helper("override")->__("Title"),
		"class" => "required-entry",
		"required" => true,
		"name" => "title",
		));
	
		$fieldset->addField("price", "text", array(
		"label" => Mage::helper("override")->__("Price"),
		"name" => "price",
		));
					
		 $fieldset->addField('status', 'select', array(
		'label'     => Mage::helper('override')->__('Status'),
		"class" => "required-entry",
		"required" => true,
		'values'   => Ewall_Override_Block_Adminhtml_Deliverymethods_Grid::getValueArray2(),
		'name' => 'status',
		));				
		 $fieldset->addField('methods', 'select', array(
		'label'     => Mage::helper('override')->__('Type'),
		"class" => "required-entry",
		"required" => true,
		'values'   => Ewall_Override_Block_Adminhtml_Deliverymethods_Grid::getValueArray3(),
		'name' => 'methods',
		));
		if (Mage::getSingleton("adminhtml/session")->getDeliverymethodsData()) {
			$form->setValues(Mage::getSingleton("adminhtml/session")->getDeliverymethodsData());
			Mage::getSingleton("adminhtml/session")->setDeliverymethodsData(null);
		} elseif(Mage::registry("deliverymethods_data")) {
			$form->setValues(Mage::registry("deliverymethods_data")->getData());
		}
		return parent::_prepareForm();
	}
}
