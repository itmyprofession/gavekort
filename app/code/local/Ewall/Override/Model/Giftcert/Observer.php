<?php

class Ewall_Override_Model_Giftcert_Observer extends Unirgy_Giftcert_Model_Observer
{

    protected function _addGcs($order, $data, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $order->getStoreId();
        }
        // process purchased gift certificates
        if ($this->_newGcs) {
            $config       = Mage::getStoreConfig('ugiftcert/default');
            $reqVars      = array_keys(Mage::helper('ugiftcert')->getGiftcertOptionVars());
            $autoSend     = Mage::getStoreConfig('ugiftcert/email/auto_send', $storeId);
            $changeStatus = Mage::getStoreConfig('ugiftcert/default/active_on_payment', $storeId);

            $data['action_code']   = 'create';
            $data['currency_code'] = $order->getOrderCurrencyCode();
            $data['order_id']      = $order->getId();
            $data['status']        = ($this->_gcInvoiced && $changeStatus) ? 'A' : $config['status'];

            foreach ($order->getAllItems() as $item) {
                /* @var $item Mage_Sales_Model_Order_Item */
                if ($item->getProductType() != 'ugiftcert') {
                    continue;
                }
                /* @var $product Mage_Catalog_Model_Product */
                if ($product = $item->getProduct()) {
                    $product->load($item->getData('product_id'));
                } else {
                    $product = Mage::getModel('catalog/product')->load($item->getData('product_id'));
                }
                $options = $item->getProductOptions();
                $r       = $options['info_buyRequest'];
                $defaultPdfSettings = $product->getData('ugiftcert_pdf_tpl_id');
                if (array_key_exists('pdf_template', $r)) {
                    $defaultPdfSettings = $r['pdf_template'];
                }
                $conditions           = $product->getData('ugiftcert_conditions');
                $defaultEmailTemplate = $product->getData('ugiftcert_email_template');
                if (array_key_exists('email_template', $r)) {
                    $defaultEmailTemplate = $r['email_template'];
                }
                $data['order_item_id'] = $item->getId();
                $data['amount']        = isset($r['amount']) ? $r['amount'] : $item->getPrice();

                for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                    /* @var $cert Unirgy_Giftcert_Model_Cert */
                    $cert = Mage::getModel('ugiftcert/cert')
                        ->setStatus($data['status'])
                        ->setBalance($data['amount'])
                        ->setCurrencyCode($data['currency_code'])
                        ->setStoreId($storeId);
                    if ($config['auto_cert_number']) {
                        $cert->setCertNumber($config['cert_number']);
                    }
                    if ($config['auto_pin']) {
                        $cert->setPin($config['pin']);
                    }
                    if (($days = intval($config['expire_timespan']))) {
                        $cert->setExpireAt(date('Y-m-d', time() + $days * 86400));
                    }
                    if ($defaultPdfSettings) {
                        $cert->setPdfSettings($defaultPdfSettings);
                    }
                    if ($defaultEmailTemplate) {
                        $cert->setData('template', $defaultEmailTemplate);
                    }

                    if ($conditions) {
                        $cert->getConditions()->setConditions(array())->loadArray(unserialize($conditions));
                    }

                    foreach ($reqVars as $f) {
                        if (!empty($r[$f])) {
                            $cert->setData($f, $r[$f]);
                        }
                    }
                    Mage::app()->dispatchEvent('ugiftcert_cert_create_from_order', array(
                                                                                        'cert'       => $cert,
                                                                                        'data'       => &$data,
                                                                                        'order_item' => $item,
                                                                                   ));
                    $cert->save();
                    $cert->addHistory($data);
                }
                if ((Unirgy_Giftcert_Model_Source_Autosend::ORDER == $autoSend)
                    || ((Unirgy_Giftcert_Model_Source_Autosend::PAYMENT == $autoSend) && $this->_gcInvoiced)
                ) {

                    Mage::helper('ugiftcert/email')->sendOrderItemEmail($item);
                }
            }
            $this->_newGcs = false;
            if ($this->_gcInvoiced) {
                $order->save();
            }
        }
    }

    public function sales_order_invoice_pay($observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order   = $invoice->getOrder();

        if ($order->getId()) {
            $changeStatus = Mage::getStoreConfig('ugiftcert/default/active_on_payment', $order->getStoreId());
            $autoSend     = Mage::getStoreConfig('ugiftcert/email/auto_send', $order->getStoreId());

            if ($changeStatus) {
                /* @var $certs Unirgy_GiftCert_Model_Mysql4_Cert_Collection */
                $certs = Mage::getModel('ugiftcert/cert')->getCollection()
                    ->addOrderFilter($order->getId());
                $data  = $this->_getDefaultGcData($order);
                foreach ($certs->getItems() as $cert) {
                    /* @var $cert Unirgy_Giftcert_Model_Cert */
                    $cert->load($cert->getId())
                        ->setStatus('A')->save();
                    $data['action']        = 'invoice';
                    $data['amount']        = $cert->getAmount();
                    $data['status']        = $cert->getStatus();
                    $data['currency_code'] = $cert->getCurrencyCode();
                    $cert->addHistory($data);
                }
            }

            if (Unirgy_Giftcert_Model_Source_Autosend::PAYMENT == $autoSend) {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getProductType() != 'ugiftcert') {
                        continue;
                    }
                    Mage::helper('ugiftcert/email')->sendOrderItemEmail($item);
                    //$item->setQtyShipped($item->getQtyInvoiced());
                }
            }
        }
        else {
            $this->_gcInvoiced = true;
        }
    }
}
