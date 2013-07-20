<?php
class Ewall_Override_Block_Adminhtml_Catalog_Product_Tab extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    /**
     * Set the template for the block
     *
     */
    public function _construct()
    {
        parent::_construct();
         
        $this->setTemplate('override/catalog/product/tab.phtml');
    }
     
    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Prepurchased Codes');
    }
     
    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Click here to view your prepurchased code tab content');
    }
     
    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
		if($this->getRequest()->getControllerName()=='catalog_product' && $this->getRequest()->getActionName()=='new') {
			if($this->checkAttSet($this->getRequest()->getParam('set')) && $this->getRequest()->getParam('type')=='ugiftcert') {
				return true;
			} else {
				return false;
			}
		} else {
			$_product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
			if($_product->getTypeId() == 'ugiftcert' && $this->checkAttSet($_product->getAttributeSetId())) {
				return true;
			} else {
				return false;
			}
		}
    }
    
    public function checkAttSet($attid)
    {
		$attributeSetModel = Mage::getModel("eav/entity_attribute_set")->load($attid);
		$attributeSetName  = $attributeSetModel->getAttributeSetName();
		if($attributeSetName=='Pre Purchased GC') {
			return true;
		} else {
			return false;
		}
	}
     
    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

 }
