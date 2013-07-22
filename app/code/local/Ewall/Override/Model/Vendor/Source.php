<?php

class Ewall_Override_Model_Vendor_Source extends Unirgy_Dropship_Model_Vendor_Source
{
    public function getAllOptions($withEmpty = false, $defaultValues = false)
    {
        $options = $this->toOptionArray();
        if ($withEmpty) {
            //array_unshift($options, array('label' => '', 'value' => ''));
        }
        return $options;
    }
}
