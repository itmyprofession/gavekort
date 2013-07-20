<?php

class Ewall_Override_Helper_ProductEdit extends Unirgy_DropshipVendorProduct_Helper_Data
{
    public function initProductEdit($config)
    {
        $r = Mage::app()->getRequest();
		$controllername = $r->getControllerName();
		$actionname = $r->getActionName();
		if($controllername=='vendor' && ($actionname=='productPost' || $actionname=='productNew')) {
			$att_set_id = $r->getParam('set_id');
			$attributeSetModel = Mage::getModel('eav/entity_attribute_set');
			$attributeSetModel->load($att_set_id);
			if($attributeSetModel->getAttributeSetName()) {
				$att_set_name  = $attributeSetModel->getAttributeSetName();
				if($att_set_name=='Vendor GC' || $att_set_name=='Pre Purchased GC') {
					$config['type_id'] = 'ugiftcert';
				}
			}
		}
        $udSess = Mage::getSingleton('udropship/session');
        $pId         = array_key_exists('id', $config) ? $config['id'] : $r->getParam('id');
        $prTpl       = !empty($config['template_id']) ? $config['template_id'] : null;
        $typeId      = array_key_exists('type_id', $config) ? $config['type_id'] : $r->getParam('type_id');
        $setId       = array_key_exists('set_id', $config) ? $config['set_id'] : $r->getParam('set_id');
        $skipCheck   = !empty($config['skip_check']);
        $skipPrepare = !empty($config['skip_prepare']);
        $vendor      = !empty($config['vendor']) ? $config['vendor'] : $udSess->getVendor();
        $productData = !empty($config['data']) ? $config['data'] : array();
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        if (!$vendor->getId()) {
            Mage::throwException('Vendor not specified');
        }
        $product = Mage::getModel('udprod/product')->setStoreId(0);
        if ($pId) {
            if (!$skipCheck) $this->checkProduct($pId);
            $product->load($pId);
        }
        if (!$product->getId()) {
            if (null === $prTpl) {
                $prTpl = $this->getTplProdBySetId($vendor, $setId);
            } else {
                $prTpl = Mage::getModel('udprod/product')->load($prTpl);
            }
            if ($setId) $prTpl->setAttributeSetId($setId);
            if (!$prTpl->getStockItem()) {
                $prTpl->setStockItem(Mage::getModel('cataloginventory/stock_item'));
            }
            $tplStockData = $prTpl->getStockItem()->getData();
            unset($tplStockData['item_id']);
            unset($tplStockData['product_id']);
            if (empty($productData['stock_data'])) {
                $productData['stock_data'] = array();
            }
            $productData['is_in_stock'] = !isset($productData['is_in_stock']) ? 1 : $productData['is_in_stock'];
            $productData['stock_data'] = array_merge($tplStockData, $productData['stock_data']);
            if (!isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 1;
            }
            if (isset($productData['stock_data']['qty']) && (float)$productData['stock_data']['qty'] > self::MAX_QTY_VALUE) {
                $productData['stock_data']['qty'] = self::MAX_QTY_VALUE;
            }
            $this->prepareTplProd($prTpl);
            $product->setData($prTpl->getData());
            if (!$product->getAttributeSetId()) {
                $product->setAttributeSetId(
                    $product->getResource()->getEntityType()->getDefaultAttributeSetId()
                );
            }
            if ($typeId) {
                $product->setTypeId($typeId);
            } elseif (!$product->getTypeId()) {
                $product->setTypeId('simple');
            }
            if (!$product->hasData('status')) {
                $product->setData('status', Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_PENDING);
            }
            if (!$product->hasData('visibility')) {
                $product->setData('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            }
        }
        $product->setData('_edit_in_vendor', true);
        $product->setData('_edit_mode', true);
        if (is_array($productData)) {
            if (!$skipPrepare) $this->prepareProductPostData($product, $productData);
            $udmulti = @$productData['udmulti'];
            if (!isset($productData['price']) && is_array($udmulti) && isset($udmulti['vendor_price'])) {
                $productData['price'] = $udmulti['vendor_price'];
            }
            $product->addData($productData);
        }
        if (!$product->getId()) {
            $product->setUdropshipVendor($vendor->getId());
        }
        return $product;
    }
}
