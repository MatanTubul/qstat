<?php
require_once ('ZendX/JQuery/Form.php');
class Catalog_Form_EntSelector extends ZendX_JQuery_Form {
    public function initForm($boolIsFolder =  FALSE){
        // Add Selector Element
        $objElement = new Bf_Form_Element_DbSelect(Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES);
        $objElement->setIdentityColumn(Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES)->setDbSelect(Bf_Eav_Db_EntitiesTypes::getPairSelect($boolIsFolder))->setValueColumn(Bf_Eav_Db_EntitiesTypes::COL_ENTITY_TYPE_TITLE)->setRequired(TRUE);
		$objElement->setAllowEmpty(FALSE)->setLabel('LBL_EAV_ENT_TYPE_SELECT');
		$objElement->addMultiOption(0,Zend_Registry::get("Zend_Translate")->translate('LBL_EAV_SELECT_OR_LEAVE') );
		$this->addElement($objElement);
        
        // Add additional Elements (For Data Transfer)
        $objElement = new Zend_Form_Element_Hidden(Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER);
        $objElement->setAttrib('id', Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER);
        //$objElement->setLabel('IS_FOLDER');
        if ($boolIsFolder) {
        	$objElement->setValue(1);
        } else {
        	$objElement->setValue(0);
        }
 		$objElement->removeDecorator( 'HtmlTag' );
        $objElement->removeDecorator( 'Label' );
        $this->addElement($objElement);

        
        $objElement = new Zend_Form_Element_Hidden('is_new_form');
        $objElement->setAttrib('id', 'is_new_form');
		$objElement->setValue(1);
 		$objElement->removeDecorator( 'HtmlTag' );
        $objElement->removeDecorator( 'Label' );         
        $this->addElement($objElement);

        return $this;
    }
}