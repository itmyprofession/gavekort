<?php
class Ewall_Override_Model_Deliverymethods_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
    {
		$delivery = Mage::getModel("override/deliverymethods")->getCollection()->addFieldToFilter('status', array('eq' => 1));
		if(Mage::app()->getRequest()->getControllerName()=='vendor' && (Mage::app()->getRequest()->getActionName()=='productEdit' || Mage::app()->getRequest()->getActionName()=='productNew')) {
			$vendor_id = Mage::getSingleton('udropship/session')->getVendor()->getId();
			$deliverymethods = Mage::getModel("override/vendordelivery")->getCollection();
			$deliverymethods->addFieldToFilter('vendor_id', array('eq' => $vendor_id));
			$deliverymethods->getSelect()->join(array('deliverymethods' => 'ewall_deliverymethods'),'main_table.delivery_id = deliverymethods.delivery_id','deliverymethods.*');
			if($deliverymethods->count()<1) {
				$deliverymethods = Mage::getModel("override/deliverymethods")->getCollection()->addFieldToFilter('status', array('eq' => 1));
			}
			$delivery = $deliverymethods;
		}
		$method = array();
		$i=0;
		foreach($delivery as $deliver_method) {
			$method[$i]['value'] = $deliver_method->getDeliveryId();
			$method[$i]['label'] = $deliver_method->getTitle();
			$i++;
		}
        if (!$this->_options) {
            $this->_options = $method;
        }
        return $this->_options;
    }
}
