<?php

class Ewall_Override_Block_Adminhtml_Deliverymethods_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Delivery methods block
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId("deliverymethodsGrid");
		$this->setDefaultSort("delivery_id");
		$this->setDefaultDir("ASC");
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("override/deliverymethods")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
		$this->addColumn("delivery_id", array(
		"header" => Mage::helper("override")->__("ID"),
		"align" =>"right",
		"width" => "50px",
		"type" => "number",
		"index" => "delivery_id",
		));
		
		$this->addColumn("title", array(
		"header" => Mage::helper("override")->__("Title"),
		"index" => "title",
		));
		$this->addColumn("price", array(
		"header" => Mage::helper("override")->__("Price"),
		"index" => "price",
		));
		$this->addColumn('status', array(
		'header' => Mage::helper('override')->__('Status'),
		'index' => 'status',
		'type' => 'options',
		'options'=>Ewall_Override_Block_Adminhtml_Deliverymethods_Grid::getOptionArray2(),				
		));
		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return $this->getUrl("*/*/edit", array("id" => $row->getId()));
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('delivery_id');
		$this->getMassactionBlock()->setFormFieldName('delivery_ids');
		$this->getMassactionBlock()->setUseSelectAll(true);
		$this->getMassactionBlock()->addItem('remove_deliverymethods', array(
				 'label'=> Mage::helper('override')->__('Remove Deliverymethods'),
				 'url'  => $this->getUrl('*/adminhtml_deliverymethods/massRemove'),
				 'confirm' => Mage::helper('override')->__('Are you sure?')
			));
		return $this;
	}
		
	static public function getOptionArray2()
	{
		$data_array=array(); 
		$data_array[1]= Mage::helper('override')->__('Enabled');
		$data_array[0]= Mage::helper('override')->__('Disabled');
		return($data_array);
	}
	
	static public function getValueArray2()
	{
		$data_array=array();
		foreach(Ewall_Override_Block_Adminhtml_Deliverymethods_Grid::getOptionArray2() as $k=>$v){
		   $data_array[]=array('value'=>$k,'label'=>$v);		
		}
		return($data_array);

	}
	
	static public function getOptionArray3()
	{
		$data_array=array(); 
		$data_array[0]=	Mage::helper('override')->__('Email');
		$data_array[1]= Mage::helper('override')->__('Snail Mail');
		return($data_array);
	}
	
	static public function getValueArray3()
	{
		$data_array=array();
		foreach(Ewall_Override_Block_Adminhtml_Deliverymethods_Grid::getOptionArray3() as $k=>$v){
		   $data_array[]=array('value'=>$k,'label'=>$v);		
		}
		return($data_array);
	}
}
