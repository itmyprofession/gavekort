<?php

class Ewall_Override_Helper_Ugiftcert_Data extends Unirgy_Giftcert_Helper_Data
{
	
	/**
	 * Show delivery type in cart line items
	 * 
	 * @param array $result
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function addOrderItemCertOptions(&$result, $item)
    {
        if ($item->getProductType() !== 'ugiftcert') {
            return;
        }

        if ($options = $item->getProductOptionByCode('info_buyRequest')) {
			$vendordelivery = Mage::getModel('override/vendordelivery')->getCollection()->addFieldToFilter('delivery_id',$options['vendor_delivery_id'])->addFieldToFilter('vendor_id',$item->getData('udropship_vendor'));
			foreach($vendordelivery as $v_d_p){
				$vdp = $v_d_p->getPrice();
			}
			$delivery = Mage::getModel('override/deliverymethods')->load($options['vendor_delivery_id']);
			$deliveryPrice = $delivery->getPrice();
			$deliveryName = $delivery->getTitle();
			if($vdp){
				$price = $vdp;
			}else{
				$price = $deliveryPrice;
			}
			
            foreach ($this->getGiftcertOptionVars() as $code=> $label) {
                if (!empty($options[$code])) {
                    $value = $options[$code];
                    if($code == 'delivery_type'){
                        $value = $deliveryName.' ('.Mage::helper('core')->currency($price, true, false).')';
                    }
                    $result[] = array(
                        'label'        => Mage::helper('override')->__($label),
                        'value'        => $value,
                        'option_value' => $value,
                    );
                }
            }
        }

        if (empty($options['recipient_email']) && empty($options['recipient_address'])) {
            $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection()->addItemFilter($item->getId());
            if ($giftcerts->count()) {
                $gcs = array();
                foreach ($giftcerts as $gc) {
                    $gcs[] = $gc->getCertNumber();
                }
                $gcsStr   = join("\n", $gcs);
                $result[] = array(
                    'label'        => Mage::helper('override')->__('Certificate number(s)'),
                    'value'        => $gcsStr,
                    'option_value' => $gcsStr,
                );
            }
        }
    }
}
