<?php
class Ewall_Override_Model_Observer extends Varien_Object
{
	public function sendShipmentNotification(){
		Mage::helper('override')->sendShipmentNotificationEmail();
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
		$action = Mage::app()->getFrontController()->getAction();
		if ($action->getFullActionName() == 'sales_order_reorder' || $action->getFullActionName() == 'checkout_cart_addgroup')
		{
			$buyInfo = $quoteItem->getBuyRequest();
			$p_a = $buyInfo['amount'];
			$p_d_i = $buyInfo['vendor_delivery_id'];
		}else{
			$pid = Mage::app()->getFrontController()->getRequest()->getParams();
			$p_a = $pid['amount'];
			$p_d_i = $pid['vendor_delivery_id'];
		}
		$vendordelivery = Mage::getModel('override/vendordelivery')->getCollection()->AddFieldToFilter('delivery_id',$p_d_i)->addFieldToFilter('vendor_id',$product);
		foreach($vendordelivery as $v_d_p){
			$vdp = $v_d_p->getPrice();
	    }
		$deliveryPrice = Mage::getModel('override/deliverymethods')->load($p_d_i)->getPrice();
		if($vdp){
			$price = $p_a+$vdp;
		}else{
			$price = $p_a+$deliveryPrice;
		}
		$quoteItem->setVendorDeliveryId($p_d_i)->setCustomPrice($price)->setOriginalCustomPrice($price);
	}
		
	public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer){
		$shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $items = $order->getAllItems();
        foreach($items as $item){
			$item->setQtyShipped($item->getQtyOrdered())->save();
		}
	}
	
	public function savePrepurchasedTabData(Varien_Event_Observer $observer){
		 $product = $observer->getProduct();
		 $pid = $product->getId();
		 if(isset($_FILES['prepurchasedcode']['name']) && $_FILES['prepurchasedcode']['name'] != '') {
			try {	
				$uploader = new Varien_File_Uploader('prepurchasedcode');
				$uploader->setAllowedExtensions(array('csv'));
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);
				$path = Mage::getBaseDir('media') . DS .'prepurchased' . DS;
				$uploader->save($path, $_FILES['prepurchasedcode']['name'] );
				$way=$path.$_FILES['prepurchasedcode']['name'];
				$csv = new Varien_File_Csv();
				$data = $csv->getData($way); 
				for($i=1; $i<count($data); $i++)
				{
					$values[] = $data[$i];
				}
				foreach($values as $value){
					$vals[] = $value[0];
				}
				$unik = array_unique($vals);
				$collection = Mage::getModel('override/vendorprepurchased')->getCollection();
				foreach($collection as $collect){
					$coll[] = $collect->getCode();
				}
				$giftcollection = Mage::getModel('ugiftcert/cert')->getCollection();
				foreach($giftcollection as $giftcollect){
					$giftcoll[] = $giftcollect->getData('cert_number');
				}
				
				$default_gift = array_unique(array_intersect($vals,$giftcoll));
				$comp = array_unique(array_intersect($vals,$coll));
				$imp = implode(',',array_unique(array_merge($comp,$default_gift)));
				if($imp){
					Mage::getSingleton('adminhtml/session')->addError($imp.' code(s) already exists in the syatem. Please check the csv.');
				}else{
					foreach($unik as $val){
						$model = Mage::getModel('override/vendorprepurchased')->setCode($val)->setPid($pid)->save(); 
					}
					$prod = Mage::getModel('catalog/product')->load($pid);
					$stockItem = Mage::getModel('cataloginventory/stock_item');
					$stockItem->assignProduct($prod);
					$qty = count($unik)+$stockItem->getData('qty');
					$stockItem->setQty($qty)->save();
				}					
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
	}
	
	public function ugiftcert_cert_create_from_order($observer)
	{
		Mage::helper('override')->ugiftcert_cert_create_from_order($observer);
	}
	
	public function controller_action_predispatch_ugiftcertadmin_adminhtml_cert_delete($observer)
	{
		$id = Mage::app()->getRequest()->getParam('id');
		$this->deleteit($id);
	}
	
	public function controller_action_predispatch_ugiftcertadmin_adminhtml_cert_massdelete($observer)
	{
		$ids = Mage::app()->getRequest()->getParam('cert');
		foreach($ids as $id) {
			$this->deleteit($id);
		}
	}
	
	public function deleteit($id)
	{
		$gift = Mage::getModel('ugiftcert/cert')->load($id);
		$prepurchasedcode = Mage::getModel('override/vendorprepurchased')->load($gift->getCertNumber(),'code');
		if($prepurchasedcode) {
			$prepurchasedcode->delete();
		}
	}
}
