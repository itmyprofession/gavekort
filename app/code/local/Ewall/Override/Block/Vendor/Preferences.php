<?php

class Ewall_Override_Block_Vendor_Preferences extends Unirgy_Dropship_Block_Vendor_Preferences
{
    /**
     * Get Delivery methods  collection
     * 
     * 
     * @return array
     */
     
     public function getDeliveryCollection(){
		$collection = Mage::getResourceModel('override/deliverymethods_collection')->addFieldToFilter('status',1);
		return $collection;
	}
	
		/**
     * Get updated price row values
     * 
     * @param delivery method id $id
     * @return array
     */
     public function getDeliveryGrid($id){
		$grid = Mage::getModel('override/vendordelivery')->load($id,'delivery_id');
		return $grid;
	}
	
	/**
     * Get delivery method row values
     * 
     * @param delivery method id $id
     * @return array
     */
	public function getDelivery($id){
		$grid = Mage::getModel('override/deliverymethods')->load($id);
		return $grid;
	}
	
	/**
     * Get price values for delivery methods
     * 
     * @param delivery method id $id and vendor id $v_id
     * @return price value
     */
	public function getDeliveryPrice($v_id,$id){
		$grid_collection = Mage::getModel('override/vendordelivery')->getCollection()->addFieldToFilter('vendor_id',$v_id)->addFieldToFilter('delivery_id',$id);
		foreach($grid_collection as $grid_v){
			$grid_p[] = $grid_v->getPrice();
		}
		$this->getDeliveryGrid($id);
		$delivery = $this->getDelivery($id);
		if($grid_p[0]){
			$d_price = $grid_p[0];
		}else{
			$d_price = $delivery->getPrice();
		}
		return $d_price;
	}
	
	/**
     * Check price value
     * 
     * @param delivery method id $id and vendor id $v_id
     * @return boolian value
     */
	
	public function getDeliveryGridPrice($v_id,$id){
		$grid_collection = Mage::getModel('override/vendordelivery')->getCollection()->addFieldToFilter('vendor_id',$v_id)->addFieldToFilter('delivery_id',$id);
		foreach($grid_collection as $grid_v){
			$grid_p[] = $grid_v->getPrice();
		}
		if($grid_p[0]){ return 1; }else{ return 0; }
	}
	
	/**
     * check id value for delivery methods
     * 
     * @param delivery method id $id and vendor id $v_id
     * @return boolian value
     */
	
	public function getDeliveryGridId($v_id,$id){
		$grid_collection = Mage::getModel('override/vendordelivery')->getCollection()->addFieldToFilter('vendor_id',$v_id)->addFieldToFilter('delivery_id',$id);
		foreach($grid_collection as $grid_v){
			$grid_p[] = $grid_v->getDeliveryId();
		}
		if($grid_p[0]){ return 1; }else{ return 0; }
	}
	
	/**
     * Get status for delivery method
     * 
     * @param delivery method id $id
     * @return Enable/Disable
     */
     
	public function getDeliveryStatus($id){
		$delivery = $this->getDelivery($id);
		if($delivery->getStatus()==1){ 
			$status = 'Enabled';
		 } else { 
			$status = 'Disabled';
		 }
		return $status;
	}
	
	/**
     * Add additional fieldset in the vendor settings page
     * 
     * 
     * @return array
     */
	
	public function getFieldsets()
    {
        $hlp = Mage::helper('udropship');

        $fieldsets = parent::getFieldsets();
        
        $fieldsets['vendor_api'] = array(
            'position' => 1000,
            'legend' => 'Vendor API',
            'fields' => array(
				'vendor_api_url' => array(
                    'position' => 1,
                    'name' => 'vendor_api_url',
                    'type' => 'text',
                    'label' => 'Vendor API URL',
                ),
                'create_per_item_shipment' => array(
                    'position' => 2,
                    'name' => 'create_per_item_shipment',
                    'type' => 'select',
                    'label' => 'Supprt Detailed Shipment',
                    'options' => array(array('value'=>0,'label'=>'No'),array('value'=>1,'label'=>'Yes')),
                ),
            ),
		);

        uasort($fieldsets, array($hlp, 'usortByPosition'));
        foreach ($fieldsets as $k=>$v) {
            if (empty($v['fields'])) {
                continue;
            }
            uasort($v['fields'], array($hlp, 'usortByPosition'));
        }

        return $fieldsets;
    }
	
}
