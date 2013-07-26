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
        ->initFromSkeleton($setup->getAttributeSetId('catalog_product', 'default'))
        ->save();
}
else {
    die('Attributeset with name ' . $sNewSetName . ' already exists.');
}

$oEntitySetup->endSetup();
