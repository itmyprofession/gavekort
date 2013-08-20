<?php

class Ewall_Override_Block_Adminhtml_Vendor_Edit_Tabs extends Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs
{
	/**
     * Add additional tab in the vendor section
     *
     * 
     */
    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id', 0);

        $this->addTab('form_section', array(
            'label'     => Mage::helper('udropship')->__('Vendor Information'),
            'title'     => Mage::helper('udropship')->__('Vendor Information'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('preferences_section', array(
            'label'     => Mage::helper('udropship')->__('Preferences'),
            'title'     => Mage::helper('udropship')->__('Preferences'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_preferences', 'vendor.preferences.form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('custom_section', array(
            'label'     => Mage::helper('udropship')->__('Custom Data'),
            'title'     => Mage::helper('udropship')->__('Custom Data'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_custom', 'vendor.custom.form')
                ->setVendorId($id)
                ->toHtml(),
        ));
		
		$this->addTab('deliverymethod_section', array(
            'label'     => Mage::helper('override')->__('Delivery methods'),
            'title'     => Mage::helper('override')->__('Delivery methods'),
            'url'       => $this->getUrl('*/*/delivery', array('_current' => true)),
			'class'     => 'ajax',
        ));

        $this->addTab('shipping_section', array(
            'label'     => Mage::helper('udropship')->__('Shipping methods'),
            'title'     => Mage::helper('udropship')->__('Shipping methods'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_shipping', 'vendor.shipping.grid')
                ->setVendorId($id)
                ->toHtml(),
        ));
        
        $this->addTab('delayed_section', array(
            'label'     => Mage::helper('override')->__('Delayed / Timed Shipping'),
            'title'     => Mage::helper('override')->__('Delayed / Timed Shipping'),
            'content'   => $this->getLayout()->createBlock('override/adminhtml_vendor_edit_tab_form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        if ($id) {
            $this->addTab('products_section', array(
                'label'     => Mage::helper('override')->__('Associated Products'),
                'title'     => Mage::helper('override')->__('Associated Products'),
                'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_products', 'vendor.product.grid')
                    ->setVendorId($id)
                    ->toHtml(),
            ));
        }
        

        if(($tabId = $this->getRequest()->getParam('tab'))) {
            $this->setActiveTab($tabId);
        }

        Mage::dispatchEvent('udropship_adminhtml_vendor_tabs_after', array('block'=>$this, 'id'=>$id));

        return parent::_beforeToHtml();
    }
}
