<?php

class Ewall_Override_Block_Product_Type extends Unirgy_Giftcert_Block_Product_Type
{
	/**
     * Get selected Delivery methods  
     * 
     * @param delivery methods id value in array format $id
     * @return array
     */
     public function getDeliveryCollection($deliverid){
		$collection = Mage::getModel('override/deliverymethods')->getCollection()->addFieldToFilter('delivery_id',array('in',$deliverid));
		return $collection;
	}
	
	/**
     * Get selected Delivery method price for vendors section 
     * 
     * @param delivery methods id value in array format $collect and vendor id $vendor_id
     * @return price
     */
	public function getDeliveryUpdatedPrice($collect,$vendor_id){
		
		$vendordelivery = Mage::getModel('override/vendordelivery')->getCollection()->AddFieldToFilter('delivery_id',$collect)->addFieldToFilter('vendor_id',$vendor_id);
		foreach($vendordelivery as $v_d_p){
			$price = $v_d_p->getPrice();
	    }
		return $price;
	}
	
	/**
     * Get selected Delivery method price for product section 
     * 
     * @param delivery methods id value in array format $collect and vendor id $vendor_id
     * @return price
     */
	
	public function getDeliveryOptions($collect,$vendor_id){
		$d_price = $this->getDeliveryUpdatedPrice($collect->getDeliveryId(),$vendor_id);
		return $this->__($collect->getTitle().' ( '.Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().round($d_price,2).' )');
	}
	
	/**
     * Get delivery method id from quote item 
     * 
     * 
     * @return id 
     */
	
	public function getDelivery(){
		
		$controller = Mage::app()->getRequest()->getControllerName();
		$action = Mage::app()->getRequest()->getActionName();
		if($controller=='cart'&&$action=='configure'){
			$item_id = Mage::app()->getRequest()->getParam('id');
			$item_detail = Mage::getModel('sales/quote_item')->load($item_id);
			return $item_detail['vendor_delivery_id'];
		}
	}
}
