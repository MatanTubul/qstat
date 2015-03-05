<?php
require_once ('ZendX/JQuery/Form.php');
class Eav_Form_AttrSelector extends ZendX_JQuery_Form {
    public function initForm(){

    	// Add Selector Element
        $objElement = new Zend_Form_Element_Select(Bf_Eav_Db_Attributes::COL_VALUE_TYPE);
		$objElement->setAllowEmpty(FALSE)->setLabel('LBL_EAV_ENT_TYPE_SELECT');
		$objElement->addMultiOption(0,'LBL_EAV_SELECT_OR_LEAVE' );
		$objElement->addMultiOptions(Bf_Eav_Db_Attributes::$arrAttrValType);
		$this->addElement($objElement);

        return $this;
    }
}