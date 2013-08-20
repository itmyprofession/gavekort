<?php

class Ewall_Override_Model_Giftcert_Quote extends Unirgy_Giftcert_Model_Quote
{
	/** Check quote item is virtual or not and return false if quote item is ugiftcert product
	 * 
	 * @return boolean true|false
	 */
    public function isVirtual()
    {
        $isVirtual = true;
        $countItems = 0;
        foreach ($this->getItemsCollection() as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            if ($item->isDeleted() || $item->getParentItemId()) {
                continue;
            }
            $countItems ++;

            // If ugiftcert, check by quote item, not by product
            if ($item->getProductType()=='ugiftcert' || !$item->getProduct()->getTypeInstance()->isQuoteItemVirtual($item) || !$item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
            }
        }
        return $countItems == 0 ? false : $isVirtual;
    }
}
