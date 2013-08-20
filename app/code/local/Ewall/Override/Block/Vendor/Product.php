<?php

class Ewall_Override_Block_Vendor_Product extends Unirgy_DropshipVendorProduct_Block_Vendor_Product
{
	/**
     * Get Delivery methods  collection
     * 
     * 
     * @return array
     */
	public function getDeliveryCollection(){
		$collection = Mage::getResourceModel('override/deliverymethods_collection');
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
}
