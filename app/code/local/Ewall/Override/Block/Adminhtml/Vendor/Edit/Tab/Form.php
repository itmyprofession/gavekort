<?php

class Ewall_Override_Block_Adminhtml_Vendor_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
	 * Delayed / Timed Shipping Block
	 *
	 */
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_form');
    }
    
    /**
	 * Fieldsets for Delayed / Timed Shipping Bock
	 *
	 */
    
    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('override');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('vendor_form', array(
            'legend'=>$hlp->__('Delayed / Timed Shipping')
        ));

       $fieldset->addField('vendor_timed_dispatch', 'select', array(
			'name'      => 'vendor_timed_dispatch',
			'label'     => $hlp->__('Will support timed dispatch?'),
			'class'     => 'required-entry',
			'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(true),
			'value'     => 1,
			'onchange' => "if(this.value==0){ var d = document.getElementById('vendor_timed_dispatch_no');d.className = d.className + ' required-entry';document.getElementById('advice-required-entry-vendor_timed_dispatch_no').style.display=''; } else if(this.value==1){ document.getElementById('vendor_timed_dispatch_no').classList.remove('required-entry');document.getElementById('vendor_timed_dispatch_no').classList.remove('validation-passed');document.getElementById('advice-required-entry-vendor_timed_dispatch_no').style.display='none'; }",
			'note'      => 'If no, Fill the below field',
		));
        
        $fieldset->addField('vendor_timed_dispatch_no', 'text', array(
            'name'      => 'vendor_timed_dispatch_no',
            'label'     => $hlp->__('Days'),
            'note'      => 'Numbers only',
        ));
        if ($vendor) {
            $form->setValues($vendor->getData());
        }
        return parent::_prepareForm();
    }

}
