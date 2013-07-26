<?php

class Ewall_Override_Model_Giftcert_Quote extends Unirgy_Giftcert_Model_Quote
{
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
            if ($item->getProductType()=='ugiftcert') {
                if (!$item->getProduct()->getTypeInstance()->isQuoteItemVirtual($item)) {
                    $isVirtual = false;
                } else {
					$isVirtual = false;
				}
            } elseif (!$item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
            }
        }
        return $countItems == 0 ? false : $isVirtual;
    }
}
