<?php

class Ewall_Remove_Block_Onepage_Abstract extends Mage_Checkout_Block_Onepage_Abstract
{
   
    /**
     * Get checkout steps codes
     *
     * @return array
     */
    protected function _getStepCodes()
    {
        return array('login', 'billing', 'review', 'payment');
    }


}
