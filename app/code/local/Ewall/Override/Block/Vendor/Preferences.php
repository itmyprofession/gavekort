<?php

class Ewall_Override_Block_Vendor_Preferences extends Unirgy_Dropship_Block_Vendor_Preferences
{
    public function getDeliveryCollection(){
		$collection = Mage::getResourceModel('override/deliverymethods_collection');
		return $collection;
	}
	
	public function getDeliveryGrid($id){
		$grid = Mage::getModel('override/vendordelivery')->load($id,'delivery_id');
		return $grid;
	}
	
	public function getDelivery($id){
		$grid = Mage::getModel('override/deliverymethods')->load($id);
		return $grid;
	}
	
	
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
	
	public function getDeliveryGridPrice($v_id,$id){
		$grid_collection = Mage::getModel('override/vendordelivery')->getCollection()->addFieldToFilter('vendor_id',$v_id)->addFieldToFilter('delivery_id',$id);
		foreach($grid_collection as $grid_v){
			$grid_p[] = $grid_v->getPrice();
		}
		if($grid_p[0]){ return 1; }else{ return 0; }
	}
	
	public function getDeliveryGridId($v_id,$id){
		$grid_collection = Mage::getModel('override/vendordelivery')->getCollection()->addFieldToFilter('vendor_id',$v_id)->addFieldToFilter('delivery_id',$id);
		foreach($grid_collection as $grid_v){
			$grid_p[] = $grid_v->getDeliveryId();
		}
		if($grid_p[0]){ return 1; }else{ return 0; }
	}
	
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
