<?php

class Ewall_Override_Adminhtml_DeliverymethodsController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("override/deliverymethods")->_addBreadcrumb(Mage::helper("adminhtml")->__("Deliverymethods  Manager"),Mage::helper("adminhtml")->__("Delivery Methods Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Override"));
			    $this->_title($this->__("Manager Delivery Methods"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function prepurchasedgridAction()
		{
			$this->getResponse()->setBody(
				$this->getLayout()->createBlock('override/adminhtml_catalog_product_prepurchasedcode_grid')->toHtml()
			);
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Override"));
				$this->_title($this->__("Delivery Methods"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("override/deliverymethods")->load($id);
				if ($model->getId()) {
					Mage::register("deliverymethods_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("override/deliverymethods");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Delivery Methods Manager"), Mage::helper("adminhtml")->__("Delivery Methods Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Delivery Methods Description"), Mage::helper("adminhtml")->__("Delivery Methods Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("override/adminhtml_deliverymethods_edit"))->_addLeft($this->getLayout()->createBlock("override/adminhtml_deliverymethods_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("override")->__("Delivery Method does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Override"));
		$this->_title($this->__("Delivery Method"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		$model  = Mage::getModel("override/deliverymethods")->load($id);
			
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("deliverymethods_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("override/deliverymethods");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Delivery Methods Manager"), Mage::helper("adminhtml")->__("Delivery Methods Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Delivery Methods Description"), Mage::helper("adminhtml")->__("Delivery Methods Description"));


		$this->_addContent($this->getLayout()->createBlock("override/adminhtml_deliverymethods_edit"))->_addLeft($this->getLayout()->createBlock("override/adminhtml_deliverymethods_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						
					$post_data['methods']=$post_data['methods'];
						$id = $this->getRequest()->getParam("id");
		
						if($id && ($post_data['status']==0)){
							$v_delivery = Mage::getModel("override/vendordelivery")->getCollection()->addFieldToFilter('delivery_id',$id);
							foreach($v_delivery as $obj){
								$obj->delete();
							}
						}

						$model = Mage::getModel("override/deliverymethods")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Delivery Method was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setDeliverymethodsData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setDeliverymethodsData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("override/deliverymethods");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Delivery Method was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('delivery_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("override/deliverymethods");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Delivery Methods was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'deliverymethods.csv';
			$grid       = $this->getLayout()->createBlock('override/adminhtml_deliverymethods_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'deliverymethods.xml';
			$grid       = $this->getLayout()->createBlock('override/adminhtml_deliverymethods_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
		
		public function getdeliverymethodsAction()
		{
			$vendor_id = $this->getRequest()->getParam('vendor_id');
			$product_id = $this->getRequest()->getParam('product_id');
			if($vendor_id) {
				$deliverymethods = Mage::getModel("override/vendordelivery")->getCollection();
				$deliverymethods->addFieldToFilter('vendor_id', array('eq' => $vendor_id));
				$deliverymethods->getSelect()->join(array('deliverymethods' => 'ewall_deliverymethods'),'main_table.delivery_id = deliverymethods.delivery_id','deliverymethods.*');
				if($deliverymethods->count()<1) {
					$deliverymethods = Mage::getModel("override/deliverymethods")->getCollection();
				}
			} else {
				$deliverymethods = Mage::getModel("override/deliverymethods")->getCollection();
			}
			$options = '';
			$vendorproducts = Mage::getModel('override/vendorproducts')->load($product_id,'product_id');
			$delivery_ids = explode(',',$vendorproducts->getDeliveryIds());
			foreach($deliverymethods as $deliver_method) {
				if(in_array($deliver_method->getDeliveryId(),$delivery_ids)) {
					if($vendorproducts->getVendorId()==$vendor_id) {
						$selected = 'selected="selected"';
					} else {
						$selected = '';
					}
				} else {
					$selected = '';
				}
				$options .= '<option '.$selected.' value="'.$deliver_method->getDeliveryId().'">'.Mage::helper('override')->__($deliver_method->getTitle()).'</option>';
			}
			echo $options;
		}
}
