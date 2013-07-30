<?php
class Ewall_Override_Block_Adminhtml_Vendor_Edit_Tab_Delivery extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct()
    {
        parent::__construct();
        $this->setId('override_vendor_display');
        $this->setDefaultSort('delivery_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    public function getVendor()
    {
        $vendor = Mage::registry('vendor_data');
        if (!$vendor) {
            $vendor = Mage::getModel('udropship/vendor')->load($this->getVendorId());
        }
        return $vendor;
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_vendor') {
             $productIds = $this->_getSelectedMethods();
             
            if (empty($productIds)) {
                $productIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('delivery_id', array('in'=>$productIds));
            }elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('delivery_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }


	
    protected function _prepareCollection()
    {
        
        $tm_id = $this->getRequest()->getParam('id');
		if(!isset($tm_id)) {
			$tm_id = 0;
		}
        $collection = Mage::getResourceModel('override/deliverymethods_collection')->addFieldToFilter('status',1);
        $this->setCollection($collection);
	
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $hlp = Mage::helper('override');
        
        $this->addColumn('in_vendor', array(
			'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_vendor',
			'values'            => $this->_getSelectedMethods(),
			'align'             => 'center',
			'index'             => 'delivery_id'
		));
		
		$this->addColumn('delivery_id', array(
            'header'    => $hlp->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'delivery_id'
        ));
                    
        $this->addColumn('title', array(
            'header'    => $hlp->__('Title'),
            'name'      => 'title',
            'index'     => 'title'
        ));
        $this->addColumn('price', array(
            'header'    => $hlp->__('Price'),
            'type'      => 'input',
            'name'      => 'price',
            'index'     => 'price',
            'sortable'  => false,
            'filter'    => false
        ));

        $this->addColumn('status', array(
            'header'    => $hlp->__('Status'),
            'type' => 'options',
            'name'      => 'status',
            'index'     => 'status',
            'options'=>Ewall_Override_Block_Adminhtml_Deliverymethods_Grid::getOptionArray2()	
        ));
        

        return parent::_prepareColumns();
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/deliveryGrid', array('_current'=>true));
    }
   
    protected function _getSelectedMethods()   // Used in grid to return selected customers values.
	{
		$customers = array_keys($this->getSelectedMethods());
		return $customers;
	}
	public function getSelectedMethods()
	{
		$tm_id = $this->getRequest()->getParam('id');
		if(!isset($tm_id)) {
			$tm_id = 0;
		}
		$collection = Mage::getModel('override/vendordelivery')->getCollection();
		$collection->addFieldToFilter('vendor_id',$tm_id);
		$custIds = array();
		foreach($collection as $obj){
			$custIds[$obj->getDeliveryId()] = array('price'=>$obj->getPrice());
		}
		return $custIds;
	}
}
