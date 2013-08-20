<?php

class Ewall_Override_Block_Vendor_Product_Renderer_FieldsetElement extends Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_FieldsetElement
{
	/**
     * Set the template file
     * 
     * 
     * 
     */
    protected function _construct()
    {
        $this->setTemplate('override/dropship/vendor/product/renderer/fieldset_element.phtml');
    }
}
