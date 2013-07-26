<?php
class Ewall_Override_Model_Observer extends Varien_Object
{
	public function sendShipmentNotification(){
		$shipment_collection = Mage::getModel('sales/order_shipment')->getCollection();
		echo '<pre>';
		foreach($shipment_collection as $sc) {
			$shipment = Mage::getModel('sales/order_shipment');
			$shipment->load($sc->getId());
			$model = Mage::getModel('udropship/vendor');
			$id = $shipment->getUdropshipVendor();
			if ($id) {
				$model->load($id);
				if($model->getVendorTimedDispatch()==0){
					$today = date("Y-m-d H:i:s",strtotime('+'.$model->getVendorTimedDispatchNo().' days'));
					$to = date("Y-m-d H:i:s",strtotime('+10 days'));
					//echo $date = $shipment->getCreatedAt()-$today;
					//echo $date = $to->diff($today)->("%d");
					echo $hoursDiff = (strtotime($to) - strtotime($today) )/(60 * 60);
				}
			}
			echo '<br/>';
		}
		exit;
	}
	public function catalog_product_prepare_save($observer)
	{
		$product = $observer->getProduct();
		$vendorproducts = Mage::getModel('override/vendorproducts')->load($product->getId(),'product_id');
		if(count($vendorproducts->getData())<=0) {
			$vendorproducts = Mage::getModel('override/vendorproducts');
		}
		$vendorproducts->setProductId($product->getId());
		$vendorproducts->setDeliveryIds(implode(',',$product->getDeliveryMethod()));
		$vendorproducts->setVendorId($product->getUdropshipVendor());
		$vendorproducts->save();
	}
	public function catalog_product_collection_load_after($observer)
	{
		$productCollection = $observer->getEvent()->getCollection();
		$storeId = null;
        foreach ($productCollection as $product) {
			$vendorproducts = Mage::getModel('override/vendorproducts')->load($product->getId(),'product_id');
			$product->setDeliveryMethod($vendorproducts->getDeliveryIds());
		}
	}
	public function catalog_product_load_after($observer)
    {
        $product = $observer->getProduct();
        $vendorproducts = Mage::getModel('override/vendorproducts')->load($product->getId(),'product_id');
        $product->setDeliveryMethod($vendorproducts->getDeliveryIds());
    }
    
    public function setProductInfo($observer){
		$event = $observer->getEvent();
        $quoteItem = $event->getQuoteItem();
        $product = $event->getProduct()->getData('udropship_vendor');
		$pid = Mage::app()->getFrontController()->getRequest()->getParams();
		$vendordelivery = Mage::getModel('override/vendordelivery')->getCollection()->AddFieldToFilter('delivery_id',$pid['vendor_delivery_id'])->addFieldToFilter('vendor_id',$product);
		foreach($vendordelivery as $v_d_p){
			$vdp = $v_d_p->getPrice();
	    }
		$deliveryPrice = Mage::getModel('override/deliverymethods')->load($pid['vendor_delivery_id'])->getPrice();
		if($vdp){
			$price = $pid['amount']+$vdp;
		}else{
			$price = $pid['amount']+$deliveryPrice;
		}
		$quoteItem->setVendorDeliveryId($pid['vendor_delivery_id'])->setCustomPrice($price)->setOriginalCustomPrice($price);
	}
		
	public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer){
		$shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $items = $order->getAllItems();
        foreach($items as $item){
			$item->setQtyShipped($item->getQtyOrdered())->save();
		}
	}
}
