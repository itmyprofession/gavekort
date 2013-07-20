<?php

class Ewall_Override_Model_Deliverymethods_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
    {
		$delivery = Mage::getModel("override/deliverymethods")->getCollection()->addFieldToFilter('status', array('eq' => 1));
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
