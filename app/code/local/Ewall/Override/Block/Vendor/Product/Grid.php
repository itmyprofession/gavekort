<?php

class Ewall_Override_Block_Vendor_Product_Grid extends Unirgy_Dropship_Block_Vendor_Product_Grid
{
    
    /**
     * Get vendor product collection for Grid view
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
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('type_id', array('in'=>array('simple','ugiftcert')))
                ->addAttributeToSelect(array('sku', 'name'))
            ;
            $conn = $collection->getConnection();
            $collection->addAttributeToFilter('entity_id', array('in'=>array_keys($v->getAssociatedProducts())));
            $collection->getSelect()->join(
                array('cisi' => $stockTable), 
                $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID), 
                array('_stock_status'=>$this->_getStockField('status'), '_is_stock_qty'=>$this->_getStockField('is_qty'))
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

            $this->_applyRequestFilters($collection);
            
            $this->_collection = $collection;
        }
        return $this->_collection;
    }
}
