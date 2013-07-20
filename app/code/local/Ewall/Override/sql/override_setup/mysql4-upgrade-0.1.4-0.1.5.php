<?php

$oEntitySetup = $this;
$oEntitySetup->startSetup();

$sets[] = 'System GC';
$sets[] = 'Pre Purchased GC';
$iCatalogProductEntityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
foreach($sets as $sNewSetName) {
	$oAttributeset = Mage::getModel('eav/entity_attribute_set')
		->setEntityTypeId($iCatalogProductEntityTypeId)
		->setAttributeSetName($sNewSetName);

	if ($oAttributeset->validate()) {
		$oAttributeset
			->save()
			->initFromSkeleton(9)
			->save();
	}
	else {
		die('Attributeset with name ' . $sNewSetName . ' already exists.');
	}
}

$oEntitySetup->endSetup();
