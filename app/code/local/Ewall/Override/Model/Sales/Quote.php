<?php

class Ewall_Override_Model_Sales_Quote extends Mage_Sales_Model_Quote
{
	/** Check quote item is virtual or not and return false if quote item is ugiftcert product
	 * 
	 * @return boolean true|false
	 */
    public function isVirtual()
    {
		$isVirtual = true;
        $countItems = 0;
        foreach ($this->getItemsCollection() as $_item) {
            /* @var $_item Mage_Sales_Model_Quote_Item */
            if ($_item->isDeleted() || $_item->getParentItemId()) {
                continue;
            }
            $countItems ++;
            if (!$_item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
                break;
            }
            if ($_item->getProductType()=='ugiftcert') {
				$isVirtual = false;
			}
        }
        return $countItems == 0 ? false : $isVirtual;
    }
}
