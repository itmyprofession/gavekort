<?php

class Ewall_Override_Block_Ugiftcert_Cart_Item extends Mage_Checkout_Block_Cart_Item_Renderer
{
    public function getProductOptions()
    {
        $options = parent::getProductOptions();
        
        $itemm = Mage::getModel('sales/quote_item')->load($this->getItem()->getId());
        $vendor_delivery_id = $itemm->getData('vendor_delivery_id');
        $udropship_vendor = $itemm->getData('udropship_vendor');
        $vendordelivery = Mage::getModel('override/vendordelivery')->getCollection()->addFieldToFilter('delivery_id',$vendor_delivery_id)->addFieldToFilter('vendor_id',$udropship_vendor);
		foreach($vendordelivery as $v_d_p){
			$vdp = $v_d_p->getPrice();
		}
		$delivery = Mage::getModel('override/deliverymethods')->load($vendor_delivery_id);
		$deliveryPrice = $delivery->getPrice();
		$deliveryName = $delivery->getTitle();
		if($vdp){
			$price = $vdp;
		}else{
			$price = $deliveryPrice;
		}
        foreach (Mage::helper('ugiftcert')->getGiftcertOptionVars() as $code=> $label) {
            if ($option = $this->getItem()->getOptionByCode($code)) {
                switch ($code) {
                    case 'delivery_type':
                        $value = $deliveryName.' ('.Mage::helper('core')->currency($price, true, false).')';
                        break;
                    default :
                        $value = $this->escapeHtml($option->getValue());
                        break;
                }
                $check = $this->getLabelCheck($label);
                if($check){
					$options[] = array(
						'label' => $label,
						'value' => $value,
					);
				}
            }
        }
        return $options;
    }
    
    public function getLabelCheck($label){
		if($label!='Sender Name' && $label!='Sender Email' && $label!='Sender Address' ){
				return 1;
		}
	}
}
