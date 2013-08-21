<?php
	
class Ewall_Override_Block_Adminhtml_Deliverymethods_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	
	/**
	 * Delivery methods block
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = "delivery_id";
		$this->_blockGroup = "override";
		$this->_controller = "adminhtml_deliverymethods";
		$this->_updateButton("save", "label", Mage::helper("override")->__("Save Item"));
		$this->_updateButton("delete", "label", Mage::helper("override")->__("Delete Item"));

		$this->_addButton("saveandcontinue", array(
			"label"     => Mage::helper("override")->__("Save And Continue Edit"),
			"onclick"   => "saveAndContinueEdit()",
			"class"     => "save",
		), -100);
		$this->_formScripts[] = "
					function saveAndContinueEdit(){
						editForm.submit($('edit_form').action+'back/edit/');
					}
				";
	}

	public function getHeaderText()
	{
		if( Mage::registry("deliverymethods_data") && Mage::registry("deliverymethods_data")->getId() ) {
			return Mage::helper("override")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("deliverymethods_data")->getTitle()));
		} else {
			return Mage::helper("override")->__("Add Item");
		}
	}
}
