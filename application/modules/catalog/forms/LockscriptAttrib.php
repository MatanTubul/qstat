<?php
class Catalog_Form_LockscriptAttrib extends ZendX_JQuery_Form {

	public function setLockscriptsSelect($arrAttribOptions){
		
		$objElement = new Zend_Form_Element_Text(Qstat_Db_Table_LockManagment::LCK_SCRIPT_ATTRIB_PREFIX);
		$objElement->setLabel('LBL_LOCK_SCRIPT_PREFIX')->setAttrib('id', Qstat_Db_Table_LockManagment::COL_LOCK_NAME)->setRequired(TRUE)->setAllowEmpty(FALSE);
		$this->addElement($objElement);
						
		$objElement = new Zend_Form_Element_Select(Qstat_Db_Table_LockManagment::LCK_SCRIPT_ATTRIB_CODE);
		$objElement->setLabel('LBL_LOCK_SCRIPT_ATTRIB')->setAttrib('id', Qstat_Db_Table_LockManagment::COL_LOCK_CMD_TYPE)->setRequired(TRUE)->setAllowEmpty(FALSE);
		$objElement->addMultiOptions($arrAttribOptions);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Button('send');
		$objElement->setValue('LBL_ADD_NEW_PARAM');
		$this->addElement($objElement);		
		
		$this->addDisplayGroup(array(Qstat_Db_Table_LockManagment::LCK_SCRIPT_ATTRIB_PREFIX,Qstat_Db_Table_LockManagment::LCK_SCRIPT_ATTRIB_CODE,'send'), 'param_group');
		
	}
	
}