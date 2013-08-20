<?php
class Ewall_Remove_Block_Onepage extends Mage_Checkout_Block_Onepage
{
	/**
     * Get 'one step checkout' step data
     * Remove shipping and shipping method
     * 
     * @return array
     */
	public function getSteps()
	{
		$steps = parent::getSteps();
		$noneed = array('shipping','shipping_method');
		foreach($noneed as $remove) {
			unset($steps[$remove]);
		}
		return $steps;
	}
}
