<?php
class Catalog_Form_LockParam extends ZendX_JQuery_Form
{

	public function init () {
		
		$this->setName('LockscriptParam');
		$this->setAttrib('id', 'LockscriptParam');
		
		$objElement = new Zend_Form_Element_Hidden(Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG_PARAM);
		$objElement->setAttrib('id', Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG_PARAM);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Select(Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_PREFIX);
		$objElement->setLabel('LBL_LOCK_FORM_PARAM_PREFIX')
			->setAttrib('id', Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_SUFFIX)
			->setRequired(TRUE)
			->setAllowEmpty(FALSE);
		$objElement->addMultiOptions(Qstat_Db_Table_LockManagmentParams::$arrPrefix);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Text(Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_NAME);
		$objElement->setLabel('LBL_LOCK_FORM_PARAM_NAME')
			->setAttrib('id', Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_NAME)
			->setRequired(TRUE)
			->setAllowEmpty(FALSE);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Select(Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_SUFFIX);
		$objElement->setLabel('LBL_LOCK_FORM_PARAM_SUFFIX')
			->setAttrib('id', Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_SUFFIX)
			->setRequired(TRUE)
			->setAllowEmpty(FALSE);
		$objElement->addMultiOptions(Qstat_Db_Table_LockManagmentParams::$arrSuffix);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Button('button');
		
      $objElement->setLabel('LBL_ADD_NEW_PARAM')
            ->setAttrib('onclick',' sendParam(); ');
		$this->addElement($objElement);
	
	}
	
	public function setScriptId($intScriptId){
		
		$objElement = new Zend_Form_Element_Hidden(Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG);
		$objElement->setAttrib('id', Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG);
		$objElement->setValue($intScriptId);
		$this->addElement($objElement);
	}

	public function setParamElements ($intEavTypeId) {
		
	    $objEav = new Bf_Eav_Db_EntitiesTypes();
	    $objEavSelect = $objEav->select(TRUE)->setIntegrityCheck(FALSE);
	    $objEavSelect->join(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME, Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME.".".Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES." = ".Bf_Eav_Db_EntitiesTypes::TBL_NAME.".".Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES);
	    $objEavSelect->join(Bf_Eav_Db_GroupAttributes::TBL_NAME,Bf_Eav_Db_GroupAttributes::TBL_NAME.'.'.Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP." = ".Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME.'.'.Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES_GRP);
	    $objEavSelect->join(Bf_Eav_Db_Attributes::TBL_NAME,Bf_Eav_Db_Attributes::TBL_NAME.'.'.Bf_Eav_Db_Attributes::COL_ID_ATTR." = ".Bf_Eav_Db_GroupAttributes::TBL_NAME.'.'.Bf_Eav_Db_GroupAttributes::COL_ID_ATTR);
	    $objEavSelect->where(Bf_Eav_Db_GroupAttributes::TBL_NAME.'.'.Bf_Eav_Db_GroupAttributes::COL_IS_DELETED." = ?",FALSE);
	    $objEavSelect->where(Bf_Eav_Db_Attributes::TBL_NAME.'.'.Bf_Eav_Db_Attributes::COL_IS_DELETED." = ?",FALSE);
	    
	    $objEavSelect->reset(Zend_Db_Select::COLUMNS);
	    
	    $objEavSelect->columns(array(Bf_Eav_Db_Attributes::COL_ID_ATTR,Bf_Eav_Db_Attributes::COL_ATTR_CODE),Bf_Eav_Db_Attributes::TBL_NAME);
	    
	    $arrElements = $objEav->getAdapter()->fetchPairs($objEavSelect);
	    
		$objElement = new Zend_Form_Element_Select(Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_ATTRIB_ID);
		$objElement->setLabel('LBL_LOCK_FORM_PARAM_ATTRIBUTES')
			->setAttrib('id', Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_ATTRIB_ID)
			->setRequired(TRUE)
			->setAllowEmpty(FALSE);
		$objElement->addMultiOptions($arrElements);
		$this->addElement($objElement);
		
		$this->addDisplayGroup(array(Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG_PARAM,Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_PREFIX,Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_NAME,Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_SUFFIX,Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_ATTRIB_ID, 'button'), 'NewParam');
	
	}

}