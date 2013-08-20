<?php

class Ewall_Override_Block_Vendor_Products extends Unirgy_DropshipVendorProduct_Block_Vendor_Products
{
	/**
     * Get current vendor product collection
     * 
     * 
     * @return array
     */
	
	public function getProductCollection()
    {
        if (!$this->_collection) {
            $v = Mage::getSingleton('udropship/session')->getVendor();
            if (!$v || !$v->getId()) {
                return array();
            }
            $r = Mage::app()->getRequest();
            $res = Mage::getSingleton('core/resource');
            $stockTable = $res->getTableName('cataloginventory/stock_item');
            $stockStatusTable = $res->getTableName('cataloginventory/stock_status');
            $wId = (int)Mage::app()->getDefaultStoreView()->getWebsiteId();
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('type_id', array('in'=>array('simple','configurable','ugiftcert')))
                ->addAttributeToSelect(array('sku', 'name', 'status', 'price'));
            $collection->addAttributeToFilter('entity_id', array('in'=>array_keys($v->getAssociatedProducts())));
            $collection->addAttributeToFilter('visibility', array('in'=>Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()));
            $conn = $collection->getConnection();
            $collection->getSelect()
                ->join(
                array('cisi' => $stockTable),
                $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID),
                    array()
                )
                ->joinLeft(
                    array('ciss' => $stockStatusTable),
                    $conn->quoteInto('ciss.product_id=e.entity_id AND ciss.website_id='.$wId.' AND ciss.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID),
					array('_stock_status'=>$this->_getStockField('status'))
				);
            if (Mage::helper('udropship')->isUdmultiAvailable()) {
                $collection->getSelect()->joinLeft(
                    array('uvp' => $res->getTableName('udropship/vendor_product')),
                    $conn->quoteInto('uvp.product_id=e.entity_id AND uvp.vendor_id=?', $v->getId()),
                    array('_stock_qty'=>$this->_getStockField('qty'), 'vendor_sku'=>'uvp.vendor_sku', 'vendor_cost'=>'uvp.vendor_cost')
                );
            } else {
                $collection->getSelect()->columns(array('_stock_qty'=>$this->_getStockField('qty')));
            }
			$collection->addAttributeToFilter('udropship_vendor', $v->getId());
            $this->_applyRequestFilters($collection);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }
}
