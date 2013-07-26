<?php

class Ewall_Override_Helper_ShipmentCollection extends Unirgy_Dropship_Helper_Data
{
    public function getVendorShipmentCollection()
    {
        if (!$this->_vendorShipmentCollection) {
            $vendorId = Mage::getSingleton('udropship/session')->getVendorId();
            $vendor = Mage::helper('udropship')->getVendor($vendorId);
            $collection = Mage::getModel('sales/order_shipment')->getCollection();
            $sqlMap = array();
            if (!$this->isSalesFlat()) {
                $collection
                    ->addAttributeToSelect(array('order_id', 'total_qty', 'udropship_status', 'udropship_method', 'udropship_method_description'))
                    ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                    ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                    ->joinAttribute('shipping_method', 'order/shipping_method', 'order_id');
            } else {
                $orderTableQted = $collection->getResource()->getReadConnection()->quoteIdentifier('sales/order');
                $sqlMap['order_increment_id'] = "$orderTableQted.increment_id";
                $sqlMap['order_created_at']   = "$orderTableQted.created_at";
                $collection->join('sales/order', "$orderTableQted.entity_id=main_table.order_id", array(
                    'order_increment_id' => 'increment_id',
                    'order_created_at' => 'created_at',
                    'shipping_method',
                ));
            }
            $collection->addAttributeToFilter('udropship_vendor', $vendorId);
            $r = Mage::app()->getRequest();
            if (($v = $r->getParam('filter_order_id_from'))) {
                $collection->addAttributeToFilter($this->mapField('order_increment_id', $sqlMap), array('gteq'=>$v));
            }
            if (($v = $r->getParam('filter_order_id_to'))) {
                $collection->addAttributeToFilter($this->mapField('order_increment_id', $sqlMap), array('lteq'=>$v));
            }
            if (($v = $r->getParam('filter_order_date_from'))) {
                $_filterDate = Mage::app()->getLocale()->date();
                $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
                $collection->addAttributeToFilter($this->mapField('order_created_at', $sqlMap), array('gteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }
            if (($v = $r->getParam('filter_order_date_to'))) {
                $_filterDate = Mage::app()->getLocale()->date();
                $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $_filterDate->addDay(1);
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
                $collection->addAttributeToFilter($this->mapField('order_created_at', $sqlMap), array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }
            if (($v = $r->getParam('filter_shipment_date_from'))) {
                $_filterDate = Mage::app()->getLocale()->date();
                $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
                $collection->addAttributeToFilter('main_table.created_at', array('gteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }
            if (($v = $r->getParam('filter_shipment_date_to'))) {
                $_filterDate = Mage::app()->getLocale()->date();
                $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $_filterDate->addDay(1);
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
                $collection->addAttributeToFilter('main_table.created_at', array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }
            if (!$r->getParam('apply_filter') && $vendor->getData('vendor_po_grid_status_filter')) {
                $filterStatuses = $vendor->getData('vendor_po_grid_status_filter');
                $filterStatuses = array_combine($filterStatuses, array_fill(0, count($filterStatuses), 1));
                $r->setParam('filter_status', $filterStatuses);
            }
            if (!$this->isSalesFlat()) {
                if (($v = $r->getParam('filter_method'))) {
                    $collection->addAttributeToFilter('udropship_method', array('in'=>array_keys($v)));
                }
                if (($v = $r->getParam('filter_status'))) {
                    $collection->addAttributeToFilter('udropship_status', array('in'=>array_keys($v)));
                }
            } else {
                if (($v = $r->getParam('filter_method'))) {
                    $collection->addAttributeToFilter('main_table.udropship_method', array('in'=>array_keys($v)));
                }
                if (($v = $r->getParam('filter_status'))) {
                    $collection->addAttributeToFilter('main_table.udropship_status', array('in'=>array_keys($v)));
                }
            }
            if (!$r->getParam('sort_by') && $vendor->getData('vendor_po_grid_sortby')) {
                $r->setParam('sort_by', $vendor->getData('vendor_po_grid_sortby'));
                $r->setParam('sort_dir', $vendor->getData('vendor_po_grid_sortdir'));
            }
            if (($v = $r->getParam('sort_by'))) {
                $map = array('order_date'=>'order_created_at', 'shipment_date'=>'created_at');
                if (isset($map[$v])) {
                    $v = $map[$v];
                }
                $collection->setOrder($v, $r->getParam('sort_dir'));
            }
            $this->_vendorShipmentCollection = $collection;
        }
        return $this->_vendorShipmentCollection;
    }
}
