<?php

class Ewall_Override_Model_Giftcert_Product_TypeCE150 extends Unirgy_GiftCert_Model_Product_TypeCE150
{
	public function isVirtual($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        if (Mage::getStoreConfig('ugiftcert/address/always_virtual', $product->getStoreId())) {
            return true;
        }

        $item = $this->_getProductItem($product);

        if (!$item) {
            return false;
        }

        $options = array();
        foreach ($item->getOptions() as $option) {
            $options[$option->getCode()] = $option->getValue();
        }
        if(isset($options[self::DELIVERY_TYPE])){
            return false;
        }

        if ((!empty($options['recipient_email']) && empty($options['recipient_address']))
            || (empty($options['recipient_name']) && empty($options['toself_printed']))
        ) {
            return false;
        } else {
            return false;
        }
    }
}
