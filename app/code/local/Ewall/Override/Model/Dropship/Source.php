<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Ewall_Override_Model_Dropship_Source extends Unirgy_Dropship_Model_Source
{
	/**
     * Get all vendors
     *
     * @param boolean $includeInactive
     * @param string $field
     * @return array
     */
    protected function _getVendors($includeInactive=false, $field='vendor_name')
    {
		$request = Mage::app()->getRequest();
		$params = $request->getParams();
		
		/**
		 * If creating new product or editing existing product return only local vendor if product attribute set is Pre Purchased GC or System GC
		 */
		if($request->getControllerName()=='catalog_product' &&  ($request->getActionName()=='new' || $request->getActionName()=='edit')) {
			if(isset($params['id'])) {
				$product = Mage::getModel('catalog/product')->load($params['id']);
				$attributeSetModel = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId());
				$attributeSetName  = $attributeSetModel->getAttributeSetName();
				$vid = ($attributeSetName=='Pre Purchased GC' || $attributeSetName=='System GC') ? Mage::helper('udropship')->getLocalVendorId($product->getStoreId()) : false;
			} elseif(isset($params['set']) && isset($params['type'])) {
				$attributeSetModel = Mage::getModel('eav/entity_attribute_set')->load($params['set']);
				$attributeSetName  = $attributeSetModel->getAttributeSetName();
				if($params['type']=='ugiftcert' && ($attributeSetName=='Pre Purchased GC'||$attributeSetName=='System GC')) {
					$vid = Mage::helper('udropship')->getLocalVendorId();
				} else {
					$vid = false;
				}
			} else {
				$vid = false;
			}
		} else {
			$vid = false;
		}
        $key = $includeInactive.'-'.$field;
        if (empty($this->_vendors[$key])) {
            $this->_vendors[$key] = array();
            $vendors = Mage::getModel('udropship/vendor')->getCollection()
                ->setItemObjectClass('Varien_Object')
                ->addFieldToSelect(array($field))
                ->addStatusFilter($includeInactive ? array('I', 'A') : 'A')
                ->setOrder('vendor_name', 'asc');
            if($vid) {
				$vendors->addFieldToFilter('vendor_id',$vid);
			}
            foreach ($vendors as $v) {
                $this->_vendors[$key][$v->getVendorId()] = $v->getDataUsingMethod($field);
            }
        }
        return $this->_vendors[$key];
    }
}
