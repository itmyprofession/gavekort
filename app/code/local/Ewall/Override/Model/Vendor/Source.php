<?php

class Ewall_Override_Model_Vendor_Source extends Unirgy_Dropship_Model_Vendor_Source
{
	/**
	 * Get all vendors and removed empty array value
	 * 
	 * @param boolean $withEmpty
	 * @param boolean $defaultValues
	 * @return array
	 */
    public function getAllOptions($withEmpty = false, $defaultValues = false)
    {
        $options = $this->toOptionArray();
        if ($withEmpty) {
            //array_unshift($options, array('label' => '', 'value' => ''));
        }
        return $options;
    }
}
