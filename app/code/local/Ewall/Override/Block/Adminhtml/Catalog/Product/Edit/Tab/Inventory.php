<?php

class Ewall_Override_Block_Adminhtml_Catalog_Product_Edit_Tab_Inventory extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory
{
    /**
	* Add additional tab for only Gift and Pre Purchased GC products 
	*
	*/
    public function __construct()
    {
        parent::__construct();
        $attributeSetModel = Mage::getModel('eav/entity_attribute_set')->load($this->getProduct()->getAttributeSetId());
		$attributeSetName  = $attributeSetModel->getAttributeSetName();
        if($this->getProduct()->getTypeId()=='ugiftcert' && $attributeSetName=='Pre Purchased GC')
			$this->setTemplate('override/catalog/product/tab/inventory.phtml');
    }
}
