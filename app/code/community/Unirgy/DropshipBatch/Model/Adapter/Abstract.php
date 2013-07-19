<?php

abstract class Unirgy_DropshipBatch_Model_Adapter_Abstract extends Varien_Object
{
    public function init()
    {
        $this->setHasOutput(false);
        return $this;
    }

    abstract public function addPO($po);

    abstract public function renderOutput();

    public function preparePO($po)
    {
        $order = $po->getOrder();
        if (!$order->getEntityId()) {
            $order = Mage::getModel('sales/order')->load($po->getOrderId() ? $po->getOrderId() : $po->getParentId());
        }
        $this->processGiftMessage($order);
        $uvendorGiftmessage = $order->getData('uvendor_giftmessage');
        if (is_string($uvendorGiftmessage)) {
            $uvendorGiftmessage = @unserialize($uvendorGiftmessage);
        }
        $vId = $this->getBatch()->getVendor()->getId();
        if (!empty($uvendorGiftmessage[$vId])) {
            $this->processGiftMessage($po, $uvendorGiftmessage[$vId]);
        }
        $this->setOrder($order);
        $this->setPo($po);
        Mage::helper('udropship')->addVendorSkus($po);

        $billing = $this->getOrder()->getBillingAddress();
        $shipping = $this->getOrder()->getShippingAddress();

        if (!$billing->getEmail()) {
            $billing->setEmail($order->getCustomerEmail());
        }
        if (!$shipping->getEmail()) {
            $shipping->setEmail($order->getCustomerEmail());
        }

        $udMethod = Mage::helper('udropship')->mapSystemToUdropshipMethod(
            $this->getPo()->getUdropshipMethod(),
            $this->getBatch()->getVendor()
        );

        $this->setUdropshipMethod($udMethod);

        $udMethodArr = explode('_', $this->getPo()->getUdropshipMethod(), 2);
        $cMethodNames = !empty($udMethodArr[0])
            ? Mage::helper('udropship')->getCarrierMethods(@$udMethodArr[0])
            : array();
        $this->setSystemCarrierTitle(
            !empty($udMethodArr[0]) ? Mage::helper('udropship')->getCarrierTitle(@$udMethodArr[0]) : ''
        );
        $this->setSystemMethodTitle(@$cMethodNames[@$udMethodArr[1]]);
        $this->setSystemCarrierCode(@$udMethodArr[0]);
        $this->setSystemMethodCode(@$udMethodArr[1]);
        
        $vars = array(
            'vendor'=>$this->getBatch()->getVendor(),
            'system_carrier_title' => $this->getSystemCarrierTitle(),
            'system_carrier_code' => $this->getSystemCarrierCode(),
            'system_method_title' => $this->getSystemMethodTitle(),
            'system_method_code' => $this->getSystemMethodCode(),
            'udropship_method' => $this->getUdropshipMethod(),
            'udropship_method_code' => $this->getUdropshipMethod()->getShippingCode(),
            'udropship_method_title' => $this->getUdropshipMethod()->getShippingTitle(),
            'order' => $this->getOrder(),
            'order_id' => $this->getOrder()->getIncrementId(),
            'billing' => $billing,
            'shipping' => $shipping,
            'po' => $this->getPo(),
            'po_id' => $this->getPo()->getIncrementId(),
        );
        Mage::dispatchEvent('udbatch_prepare_po', array('vars'=>&$vars));
        $this->setVars($vars);

        return true;
    }

    public function processGiftMessage($object, $gmId=null)
    {
        $gmHlp = Mage::helper('giftmessage/message');
        if (null === $gmId) {
            $gmId = $object->getGiftMessageId();
        }
        if ($gmId && ($_giftMessage = $gmHlp->getGiftMessage($gmId))
        ) {
            $object->setGiftMessageFrom($_giftMessage->getSender());
            $object->setGiftMessageFromWithLabel($gmHlp->__('From:').' '.$_giftMessage->getSender());
            $object->setGiftMessageTo($_giftMessage->getRecipient());
            $object->setGiftMessageToWithLabel($gmHlp->__('To:').' '.$_giftMessage->getRecipient());
            $object->setGiftMessageText($_giftMessage->getMessage());
            $object->setGiftMessageTextWithLabel($gmHlp->__('Message:').' '.$_giftMessage->getMessage());
            $object->setGiftMessageCombined(
                $gmHlp->__('From:').' '.$_giftMessage->getSender()."\n".
                $gmHlp->__('To:').' '.$_giftMessage->getRecipient()."\n".
                $gmHlp->__('Message:').' '.$_giftMessage->getMessage()
            );
        }
    }

    public function preparePOItem($item)
    {
        if (!$this->getOrder()) {
            Mage::throwException('Order is not initialized');
        }

        $orderItem = $item->getOrderItem();
        if (!$orderItem) {
            if (!$item->getOrderItemId()) {
                $item = Mage::getModel('sales/order_shipment_item')->load($item->getEntityId());
            }
            $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
        }

        if (($orderItem->getChildren() || $orderItem->getChildrenItems() and $orderItem->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            || ($orderItem->getParentItem() and $orderItem->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            || in_array($orderItem->getStatus(), $this->getBatch()->getSkipItemStatuses())) {
            return false;
        }

        $this->setOrderItem($orderItem);
        $this->setPoItem($item);

        $productOptions = $this->getOrderItem()->getProductOptions();
        $productOptionsArr = array();
        if (!empty($productOptions['options'])) {
            foreach ($productOptions['options'] as $o) {
                $productOptionsArr[] = $o['label'].': '.(!empty($o['print_value']) ? $o['print_value'] : $o['value']);
            }
        }
        if (!empty($productOptions['attributes_info'])) {
            foreach ($productOptions['attributes_info'] as $o) {
                $productOptionsArr[] = $o['label'].': '.$o['value'];
            }
        }
        if (!empty($productOptions['additional_options'])) {
            foreach ($productOptions['additional_options'] as $o) {
                $productOptionsArr[] = $o['label'].': '.(!empty($o['print_value']) ? $o['print_value'] : $o['value']);
            }
        }
        if ($orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $this->setOrigSku($item->getSku());
            $item->setSku($orderItem->getProductOptionByCode('simple_sku'));
            if ($item->getVendorSimpleSku()) {
                $this->setOrigVendorSku($item->getVendorSku());
                $item->setVendorSku($item->getVendorSimpleSku());
            }
        }
        $this->processGiftMessage($this->getOrderItem());
        $vars = array(
            'vendor'=>$this->getBatch()->getVendor(),
            'system_carrier_title' => $this->getSystemCarrierTitle(),
            'system_carrier_code' => $this->getSystemCarrierCode(),
            'system_method_title' => $this->getSystemMethodTitle(),
            'system_method_code' => $this->getSystemMethodCode(),
            'udropship_method' => $this->getUdropshipMethod(),
            'udropship_method_code' => $this->getUdropshipMethod()->getShippingCode(),
            'udropship_method_title' => $this->getUdropshipMethod()->getShippingTitle(),
            'order' => $this->getOrder(),
            'order_id' => $this->getOrder()->getIncrementId(),
            'billing' => $this->getOrder()->getBillingAddress(),
            'shipping' => $this->getOrder()->getShippingAddress(),
            'po' => $this->getPo(),
            'po_id' => $this->getPo()->getIncrementId(),

            'item' => $this->getPoItem(),
            'order_item' => $this->getOrderItem(),
            'product_options' => join('; ', $productOptionsArr),
        );
        Mage::dispatchEvent('udbatch_prepare_po_item', array('vars'=>&$vars));
        $this->setVars($vars);

        return true;
    }

    public function restoreItem()
    {
        if ($this->getOrigSku()) {
            $this->getPoItem()->setSku($this->getOrigSku());
            $this->unsOrigSku();
        }
        if ($this->hasOrigVendorSku()) {
            $this->getPoItem()->setVendorSku($this->getOrigVendorSku());
            $this->unsOrigVendorSku();
        }
        return $this;
    }

    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = Mage::getModel('udbatch/templateFilter');
        }
        return $this->_templateFilter;
    }

    public function getExportTemplate()
    {
        if (!$this->hasData('export_template')) {
        	$exportTpl = $this->getBatch()->getVendor()->getBatchExportOrdersTemplate();
        	if (trim($exportTpl) == '') {
        		Mage::throwException(Mage::helper('udbatch')->__('Empty Export Template'));
        	}
            $this->setData('export_template', $exportTpl);
        }
        return $this->getData('export_template');
    }

    public function renderTemplate($text, $vars)
    {
        if (preg_match_all('#\[([a-z0-9._]+(?::[^\]]*)?)\]#i', $text, $m, PREG_PATTERN_ORDER)) {
            $keys = array_unique($m[1]);
            $replaceFrom = array();
            $replaceTo = array();
            foreach ($keys as $key) {
                $_key = explode(':', $key, 2);
                $keyArr = explode('.', $_key[0]);
                $value = $vars;
                foreach ($keyArr as $k) {
                    if (!isset($value[$k])) {
                        $value = '';
                        break;
                    }
                    $value = $value[$k];
                }

                $replaceFrom[] = '['.$key.']';
                $value = empty($value) && !empty($_key[1]) ? $_key[1] : $value;
                $replaceTo[] = is_numeric($value) ? $value*1 : $value;
            }
            if (Mage::getStoreConfigFlag('udropship/batch/replace_nl2customchar')) {
                foreach ($replaceTo as &$_var) {
                    $_var = str_replace("\n", Mage::getStoreConfig('udropship/batch/replace_nl2customchar_value'), $_var);
                }
                unset($_var);

            }
            $text = str_replace($replaceFrom, $replaceTo, $text);
        }
        $text = $this->getTemplateFilter()->setVariables($vars)->filter($text);

        return $text;
    }

    public function __destruct()
    {
        $this->_data = array();
    }
}
