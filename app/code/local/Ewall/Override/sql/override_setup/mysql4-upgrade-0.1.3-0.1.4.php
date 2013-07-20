<?php

$oEntitySetup = $this;
$oEntitySetup->startSetup();

$sNewSetName = 'Vendor GC';
$iCatalogProductEntityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();

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

$oEntitySetup->endSetup();
