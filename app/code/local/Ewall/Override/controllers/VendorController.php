<?php

 require_once "app/code/community/Unirgy/Dropship/controllers/VendorController.php";

class Ewall_Override_VendorController extends Unirgy_Dropship_VendorController
{
	/**
     * Set delivery methods , vendor_timed_dispatch , vendor_timed_dispatch_no , vendor_api_url , create_per_item_shipment values  for vendors
     * 
     * 
     */
    public function preferencesPostAction()
    {
        $defaultAllowedTags = Mage::getStoreConfig('udropship/vendor/preferences_allowed_tags');
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        if ($r->isPost()) {
            $p = $r->getPost();
            $hlp->processPostMultiselects($p);
            try {
                $v = $session->getVendor();
                foreach (array(
                    'vendor_name', 'vendor_attn', 'email', 'password', 'telephone',
                    'street', 'city', 'zip', 'country_id', 'region_id',
                    'billing_vendor_attn', 'billing_email', 'billing_telephone',
                    'billing_street', 'billing_city', 'billing_zip', 'billing_country_id', 'billing_region_id'
                ) as $f) {
                    if (array_key_exists($f, $p)) $v->setData($f, $p[$f]);
                }
                foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
                    if (!isset($p[$code])) {
                        continue;
                    }
                    $param = $p[$code];
                    if (is_array($param)) {
                        foreach ($param as $key=>$val) {
                            $param[$key] = strip_tags($val, $defaultAllowedTags);
                        }
                    }
                    else {
                        $allowedTags = $defaultAllowedTags;
                        if ($node->filter_input && ($stripTags = $node->filter_input->strip_tags) && isset($stripTags->allowed)) {
                            $allowedTags = (string)$node->strip_tags->allowed;
                        }
                        if ($allowedTags && $node->type != 'wysiwyg') {
                            $param = strip_tags($param, $allowedTags);
                        }

                        if ($node->filter_input && ($replace = $node->filter_input->preg_replace) && isset($replace->from) && isset($replace->to)) {
                            $param = preg_replace((string)$replace->from, (string)$replace->to, $param);
                        }
                    } // end code injection protection
                    $v->setData($code, $param);
                    $v->setData('vendor_timed_dispatch',$this->getRequest()->getPost('vendor_timed_dispatch'));
                    $v->setData('vendor_timed_dispatch_no',$this->getRequest()->getPost('vendor_timed_dispatch_no'));
                    $v->setData('vendor_api_url',$this->getRequest()->getPost('vendor_api_url'));
                    $v->setData('create_per_item_shipment',$this->getRequest()->getPost('create_per_item_shipment'));
                    
                    if(isset($p['methods'])){
						for($kl=0;$kl<count($p['methods']['delivery_id']);$kl++){
							if($p['methods']['delivery_id'][$kl]){
								$dmethods[$p['methods']['delivery_id'][$kl]]=array('price'=>$p['methods']['price'][$kl]);
							}
						}
						$id = $v->getVendorId();
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
                }
                Mage::dispatchEvent('udropship_vendor_preferences_save_before', array('vendor'=>$v, 'post_data'=>&$p));
                $v->save();
                $session->addSuccess(Mage::helper('override')->__('Settings has been saved'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udropship/vendor/preferences');
    }
    
    /**
     * Shipment information action
     * 
     * 
     */    
    public function shipmentInfoAction()
    {
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info')->setTemplate('override/dropship/vendor/shipment/info.phtml');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }

}
