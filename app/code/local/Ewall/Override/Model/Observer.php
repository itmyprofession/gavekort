<?php
class Ewall_Override_Model_Observer extends Varien_Object
{
	/**
	 * Send shipment notification email based on VendorTimedDispatchNo of Vendor
	*/
	public function sendShipmentNotification(){
		Mage::helper('override')->sendShipmentNotificationEmail();
	}
	
	/**
	 * Set delivery methods to products
	 * 
	 * @params $observer
	*/
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
	
	/**
	 * Set delivery methods to product collection
	 * 
	 * @params $observer
	*/
	public function catalog_product_collection_load_after($observer)
	{
		$productCollection = $observer->getEvent()->getCollection();
		$storeId = null;
        foreach ($productCollection as $product) {
			$vendorproducts = Mage::getModel('override/vendorproducts')->load($product->getId(),'product_id');
			$product->setDeliveryMethod($vendorproducts->getDeliveryIds());
		}
	}
	
	/**
	 * Set delivery methods to product
	 * 
	 * @params $observer
	*/
	public function catalog_product_load_after($observer)
    {
        $product = $observer->getProduct();
        $vendorproducts = Mage::getModel('override/vendorproducts')->load($product->getId(),'product_id');
        $product->setDeliveryMethod($vendorproducts->getDeliveryIds());
    }
    
    /**
	 * Set delivery methods to quote items
	 * 
	 * @params $observer
	*/
    public function setProductInfo($observer){
		$event = $observer->getEvent();
        $quoteItem = $event->getQuoteItem();
        $product = $event->getProduct()->getData('udropship_vendor');
        $type = $event->getProduct()->getData('type_id');
        if($type!='ugiftcert')
			return;
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
	
	/**
	 * Shipment status
	 * 
	 * @params $observer
	*/	
	public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
	{
		$shipment = $observer->getEvent()->getShipment();
		$order = $shipment->getOrder();
		$items = $order->getAllItems();
        foreach($items as $item){
			$item->setQtyShipped($item->getQtyOrdered())->save();
		}
		$this->setShipmentDetails($shipment);
	}
	
	/**
	 * Add comment to shipment
	 * 
	 * @params $observer
	*/
	public function controller_action_predispatch_udropshipadmin_adminhtml_shipment_addComment($observer)
	{
		$shipment_id = Mage::app()->getRequest()->getParam('shipment_id');
		if(!$shipment_id)
			return;
		$shipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
		if($shipment->getId())
			$this->setShipmentDetails($shipment);
	}
	
	/**
	 * Send shipment details to vendor api
	 * 
	 * @params $shipment
	*/
	protected function setShipmentDetails($shipment)
	{
		$order = $shipment->getOrder();
		$vendor = Mage::getModel('udropship/vendor')->load($shipment->getData('udropship_vendor'));
		$apiForm = $vendor->getData('vendor_api_form_value');
		$apiName = $vendor->getData('vendor_api_shortname');
		if($apiForm && $apiName) {

			$serviceApi = Mage::getBaseDir('base').DS.Mage::getStoreConfig('udropship/vendor_api_config/path').$apiName.'.php';
			
			if(!file_exists($serviceApi))
				return;
			require_once($serviceApi);
			
			foreach($shipment->getItemsCollection() as $item) {
				$items_arr[] = $item->getOrderItemId();
			}
			foreach($order->getAllItems() as $item) {
				if(!in_array($item->getId(),$items_arr))
					continue;
				static $item_id = 0;
				if($item->getData('product_type')=='ugiftcert') {
					$item_data['certificate_data'] = $item->getData('certificate_data');
					$infos = unserialize($item->getData('product_options'));
					$info = $infos['info_buyRequest'];
					$item_data['recipient_infos']['recipient_name'] = $info['recipient_name'];
					if($info['recipient_email']) {
						$item_data['recipient_infos']['recipient_email'] = $info['recipient_email'];
					} else {
						$item_data['recipient_infos']['recipient_address'] = $info['recipient_address'];
					}
					$item_data['recipient_infos']['recipient_message'] = $info['recipient_message'];
					$item_data['recipient_infos']['qty'] = $info['qty'];
				}
				$item_data['data'] = $item->getData();
				unset($item_data['data']['product']);//remove product
				$datatopost['items'][$item_id] = $item_data;
				$item_id++;
			}
			$datatopost['shipment_data'] = $shipment->getData();
			$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
			$datatopost['shipment_data']['status'] = $statuses[$shipment->getData('udropship_status')];
			try {
				$service = new $apiName($apiForm);
				$response = $service->initiate(json_encode($datatopost));
				Mage::app('admin');
				Mage::register('isSecureArea', 1);
				Mage::helper('override')->setShipmentDetail($shipment->getId(), 'api_order_details', json_encode($response));
				//$shipment->setApiOrderDetails(json_encode($response))->save();
			} catch(Exception $e) {
				//print errors in the service API
			}
		}

	}
	
	/**
	 * Save purchased code to products
	 * 
	 * @params $observer
	*/
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
	
	/**
	 * Create GC from prepurchased code
	 * 
	 * @params $observer
	*/
	public function ugiftcert_cert_create_from_order($observer)
	{
		$cert = $observer->getCert();
		$order_item = $observer->getOrderItem();
		$item = Mage::getModel('sales/order_item')->load($order_item->getId());
		$product_id = $item->getData('product_id');
		$product_type = $item->getData('product_type');
		if($product_type=='ugiftcert') {
			$item->setIsVirtual(0)->save();
		}
		$vendorprepurchased = Mage::getModel('override/vendorprepurchased')->getCollection()->addFieldToFilter('used',0)->addFieldToFilter('pid',$product_id);
		if($vendorprepurchased->count()>0) {
			foreach($vendorprepurchased as $codes) {
				$cert->setCertNumber($codes->getCode());
				$codes->setUsed(1)->save();
				break;
			}
		}
	}
	
	/**
	 * Delete prepurchased code while deleting GC's
	 * 
	 * @params $observer
	*/
	public function controller_action_predispatch_ugiftcertadmin_adminhtml_cert_delete($observer)
	{
		$id = Mage::app()->getRequest()->getParam('id');
		$this->deleteit($id);
	}
	
	/**
	 * Delete prepurchased code while deleting GC's
	 * 
	 * @params $observer
	*/
	public function controller_action_predispatch_ugiftcertadmin_adminhtml_cert_massdelete($observer)
	{
		$ids = Mage::app()->getRequest()->getParam('cert');
		foreach($ids as $id) {
			$this->deleteit($id);
		}
	}
	
	/**
	 * Delete prepurchased code while deleting GC's
	 * 
	 * @params $observer
	*/
	public function deleteit($id)
	{
		$gift = Mage::getModel('ugiftcert/cert')->load($id);
		$prepurchasedcode = Mage::getModel('override/vendorprepurchased')->load($gift->getCertNumber(),'code');
		if($prepurchasedcode) {
			$prepurchasedcode->delete();
		}
	}
	
}
