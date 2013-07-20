<?php

require_once "app/code/community/Unirgy/Dropship/controllers/Adminhtml/VendorController.php";

class Ewall_Override_Adminhtml_VendorController extends Unirgy_Dropship_Adminhtml_VendorController
{

    public function saveAction()
    {
      
        if ( $this->getRequest()->getPost() ) {
            $r = $this->getRequest();
            $hlp = Mage::helper('udropship');
            try {
                $id = $r->getParam('id');
                $new = !$id;
                $data = $r->getParams();
                
                $data['vendor_id'] = $id;
                $data['status'] = $data['status1'];

                $model = Mage::getModel('udropship/vendor');
                if ($id) {
                    $model->load($id);
                }
                $hlp->processPostMultiselects($data);
                $model->addData($data);
                
                if(isset($data['links'])){
					$dmethods = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['links']['deliverymethods']); //Save the array to your database

					$collection = Mage::getModel('override/vendordelivery')->getCollection();
					$collection->addFieldToFilter('vendor_id',$id);
					foreach($collection as $obj){
						$obj->delete();
					}
					foreach($dmethods as $key => $value){
						$model2 = Mage::getModel('override/vendordelivery');
						$model2->setVendorId($id);
						$model2->setDeliveryId($key);
						$model2->setPrice($value['price']);
						$model2->save();
					}
				}

                $shipping = array();
                if ($r->getParam('vendor_shipping')) {
                    $shipping = Zend_Json::decode($r->getParam('vendor_shipping'));
                }
                $model->setPostedShipping($shipping);

                $products = array();
                if ($r->getParam('vendor_products')) {
                    $products = Zend_Json::decode($r->getParam('vendor_products'));
                }
                $model->setPostedProducts($products);

                Mage::getSingleton('adminhtml/session')->setData('uvendor_edit_data', $model->getData());
                $model->save();
                Mage::getSingleton('adminhtml/session')->unsetData('uvendor_edit_data');

                Mage::getSingleton('adminhtml/session')->addSuccess($hlp->__('Vendor was successfully saved'));

                $nonSavedMethodIds = array_diff(array_keys($shipping), array_keys($model->getNonCachedShippingMethods()));

                if (!empty($nonSavedMethodIds)) {
                    $shippingMethods = $hlp->getShippingMethods();
                    $nonSavedMethods = array();
                    foreach ($nonSavedMethodIds as $id) {
                        if (($sItem = $shippingMethods->getItemById($id))) {
                            $nonSavedMethods[$id] = $sItem->getShippingTitle();
                        }
                    }
                    if (!empty($nonSavedMethods)) {
                        Mage::getSingleton('adminhtml/session')->addNotice($hlp->__('This shipping methods were not saved: %s. Try to use overrides.', implode(', ', $nonSavedMethods)));
                    }
                    $this->_redirect('*/*/edit', array('id' => $r->getParam('id'), 'tab'=>'shipping_section'));
                } else {
                    if ($r->getParam('save_continue')) {
                        $this->_redirect('*/*/edit', array('id' => $r->getParam('id')));
                    } else {
                        $this->_redirect('*/*/');
                    }
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($r->getParam('reg_id')) {
                    $this->_redirect('umicrositeadmin/adminhtml_registration/edit', array('reg_id'=>$r->getParam('reg_id')));
                    return;
                }
                $this->_redirect('*/*/edit', array('id' => $r->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function deliveryAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('vendor.delivery.grid')
		->setDeliverymethods($this->getRequest()->getPost('deliverymethods', null));
		$this->renderLayout();
	}
	
	public function deliveryGridAction()
	{
		
		$this->loadLayout();
		$this->getLayout()->getBlock('vendor.delivery.grid')
		->setDeliverymethods($this->getRequest()->getPost('deliverymethods', null));
		$this->renderLayout();
	}
   
}
