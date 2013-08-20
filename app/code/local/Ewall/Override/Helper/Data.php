<?php
class Ewall_Override_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/shipment/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE         = 'sales_email/shipment/guest_template';
    const XML_PATH_EMAIL_IDENTITY               = 'sales_email/shipment/identity';
    const XML_PATH_EMAIL_COPY_TO                = 'sales_email/shipment/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/shipment/copy_method';
    const XML_PATH_EMAIL_ENABLED                = 'sales_email/shipment/enabled';
	
	/**
	 * Send shipment notification email based on VendorTimedDispatchNo of Vendor
	 */
	public function sendShipmentNotificationEmail()
	{
		if (Mage::helper('udropship')->isSalesFlat()) {
			$res = Mage::getSingleton('core/resource');
			$shipment_collection = Mage::getResourceModel('sales/order_shipment_grid_collection');
			$shipment_collection->getSelect()->join(
				array('t'=>$res->getTableName('sales/shipment')), 
				't.entity_id=main_table.entity_id', 
				array('udropship_vendor', 'udropship_available_at', 'udropship_method', 
					'udropship_method_description', 'udropship_status', 'shipping_amount'
				)
			);
			$shipment_collection->getSelect()->join(
				array('vendor'=>$res->getTableName('udropship/vendor')),
				'vendor.vendor_id=t.udropship_vendor',
				array('vendor_timed_dispatch', 'vendor_timed_dispatch_no')
			);
			$shipment_collection->setFlag('ee_gws_store_use_main', 1);
			$shipment_collection->getSelect()->where('vendor.vendor_timed_dispatch=1 AND vendor.vendor_timed_dispatch_no>1 AND t.udropship_status=0');
		} else {
			$shipment_collection = Mage::getResourceModel('sales/order_shipment_collection')
				->addAttributeToSelect('*')
				->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
				->joinAttribute('order_increment_id', 'order/increment_id', 'order_id', null, 'left')
				->joinAttribute('order_created_at', 'order/created_at', 'order_id', null, 'left')
				->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
				->joinAttribute('vendor_timed_dispatch','udropship/vendor','udropship_vendor','left');
			$res = Mage::getSingleton('core/resource');
			$shipment_collection->getSelect()->join(
				array('vendor'=>$res->getTableName('udropship/vendor')),
				'vendor.vendor_id=main_table.udropship_vendor',
				array('vendor_timed_dispatch', 'vendor_timed_dispatch_no')
			);
			$shipment_collection->getSelect()->where('vendor.vendor_timed_dispatch=1 AND vendor.vendor_timed_dispatch_no>1 AND udropship_status=0');
		}
		foreach($shipment_collection as $shipment) {
			foreach($shipment->getAllItems() as $item) {
				$item = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
				$product_options = unserialize($item->getData('product_options'));
				$info_buyRequest = $product_options['info_buyRequest'];
				$send_on = $info_buyRequest['send_on'];
				if($send_on) {
					$send[$shipment->getId()] = array('shipment' => $shipment, 'date' => $send_on);
				}
			}
		}
		foreach($send as $shipment) {
			$ships = $shipment['shipment'];
			$date = $shipment['date'];
			$model = Mage::getModel('udropship/vendor');
			$before_date = $ships->getVendorTimedDispatchNo();
			$today = strtotime(date("Y-m-d H:i:s"));
			$delivery_date = strtotime($date);
			$date_diff = (($delivery_date - $today)/(60 * 60))/24;
			$should_send = $date_diff - $before_date;
			if($should_send<1 && $should_send>=0) {
				Mage::helper('override')->sendNotificationEmail($ships);
			}
		}
	}
	
	/**
	 * Send shipment notification email to vendor
	 *
	 * @param Mage_Sales_Model_Shipment $shipment 
	 * @param boolean $notifyCustomer
	 * @return Mage_Sales_Model_Shipment $shipment
	 */
	public function sendNotificationEmail($shipment, $notifyCustomer = true)
	{
		$order = $shipment->getOrder();
		
		$vendor_id = $shipment->getData('udropship_vendor');
		$vendor = Mage::getModel('udropship/vendor')->load($vendor_id);
		$vendor_name = $vendor->getVendorName();
		$vendor_email = $vendor->getEmail();

        $storeId = $order->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewShipmentEmail($storeId)) {
            return $shipment;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails($storeId, self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        // Check if at least one recepient is found
        if (!$notifyCustomer && !$copyTo) {
            return $shipment;
        }

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = 'override_override_shipment_new_guest_gavekort';
        } else {
            $templateId = 'override_override_shipment_new_gavekort';
        }
        
        $customerName = $vendor_name;

        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($vendor_email, $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }

        // Email copies are sent as separated emails if their copy method is 'copy' or a customer should not be notified
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'shipment'     => $shipment,
                'comment'      => $comment,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
                'vendor'	   => $vendor
            )
        );
        $mailer->send();

        return $shipment;
	}
	
	protected function _getEmails($storeId, $configPath)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
	
	/**
	 * Check whether product type is ugiftcert or not
	 * 
	 * @param Mage_Catalog_Model_Product $product
	 * @param boolean $set_id
	 */
	public function checkProduct($_product, $set_id = false)
	{
		if ($_product->getId()) {
			$attributeSetModel = Mage::getModel('eav/entity_attribute_set')->load($_product->getAttributeSetId());
			$attributeSetName  = $attributeSetModel->getAttributeSetName();
			if($attributeSetName=='Vendor GC') {
				$PrePurchasedGC = true;
			} else {
				$PrePurchasedGC = false;
			}
			if($PrePurchasedGC && $_product->getTypeId() == 'ugiftcert') {
				$goAhead = true;
			} else {
				$goAhead = false;
			}
		} else {
			if($set_id) {
				$attributeSetModel = Mage::getModel('eav/entity_attribute_set')->load($set_id);
				$attributeSetName  = $attributeSetModel->getAttributeSetName();
				if($attributeSetName=='Vendor GC') {
					$goAhead = true;
				} else {
					$goAhead = false;
				}
			} else {
				$goAhead = false;
			}
		}
		return $goAhead;
	}
	
	/**
	 * Get balance check url to customer
	 */
	public function getBalanceCheckUrl(){
		return Mage::getUrl('ugiftcert/customer/balance/');
	}
}
