<?php

$mageFilename = '../app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);

// Set store as admin
Mage::app('admin');
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

// Fetch shipment collection where the returned API order details are saved
if (Mage::helper('udropship')->isSalesFlat()) {
	$res = Mage::getSingleton('core/resource');
	$shipmentCollection = Mage::getResourceModel('sales/order_shipment_grid_collection');
	$shipmentCollection->getSelect()->join(
		array('t'=>$res->getTableName('sales/shipment')), 
		't.entity_id=main_table.entity_id', 
		array('udropship_vendor', 'udropship_available_at', 'udropship_method', 
			'udropship_method_description', 'udropship_status', 'shipping_amount'
		)
	);
	$shipmentCollection->setFlag('ee_gws_store_use_main', 1);
	$shipmentCollection->getSelect()->where('api_order_details IS NOT NULL');
} else {
	$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
		->addAttributeToSelect('*')
		->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
		->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
		->joinAttribute('order_increment_id', 'order/increment_id', 'order_id', null, 'left')
		->joinAttribute('order_created_at', 'order/created_at', 'order_id', null, 'left')
		->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
		->joinAttribute('vendor_timed_dispatch','udropship/vendor','udropship_vendor','left');
	$res = Mage::getSingleton('core/resource');
	$shipmentCollection->getSelect()->where('api_order_details IS NOT NULL');
}

// For each shipments get shipment status from corresponding Vendor API and update it to Magento
foreach($shipmentCollection as $shipment) {
	$shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());
	$vendor = Mage::getModel('udropship/vendor')->load($shipment->getData('udropship_vendor'));
	$apiForm = $vendor->getData('vendor_api_form_value');
	$apiName = $vendor->getData('vendor_api_shortname');
	if($apiForm && $apiName) {
		$serviceApi = Mage::getBaseDir('base').DS.Mage::getStoreConfig('udropship/vendor_api_config/path').$apiName.'.php';
		if(!file_exists($serviceApi))
			return;
		require_once($serviceApi);
		$service = new $apiName($apiForm);
		$response = $service->getOrderStatus($shipment->getApiOrderDetails());
		// Shipment status will updated to the system only if the status ID available in Magento
		$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
		$statuses_ids = array_keys($statuses);
		if(in_array($response, $statuses_ids)) {
			Mage::helper('override')->setShipmentDetail($shipment->getId(), 'udropship_status', $response);
		}
	}
}
