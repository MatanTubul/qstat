<?php
class Catalog_Form_LockMng extends ZendX_JQuery_Form {

	public function init(){
		
		$this->setName('Lockscript');
		$this->setAttrib('id', 'Lockscript');
		
		$objElement = new Zend_Form_Element_Hidden(Qstat_Db_Table_LockManagment::COL_ID_LOCK_MNG);
		$objElement->setAttrib('id', Qstat_Db_Table_LockManagment::COL_ID_LOCK_MNG);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Text(Qstat_Db_Table_LockManagment::COL_LOCK_NAME);
		$objElement->setLabel('LBL_LOCK_FORM_FIELD_TITLE')->setAttrib('id', Qstat_Db_Table_LockManagment::COL_LOCK_NAME)->setRequired(TRUE)->setAllowEmpty(FALSE);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Text(Qstat_Db_Table_LockManagment::COL_LOCK_CMD_CODE);
		$objElement->setLabel('LBL_LOCK_FORM_FIELD_CMD_CODE')->setAttrib('id', Qstat_Db_Table_LockManagment::COL_LOCK_CMD_CODE)->setRequired(TRUE)->setAllowEmpty(FALSE);
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Select(Qstat_Db_Table_LockManagment::COL_LOCK_CMD_TYPE);
		$objElement->setLabel('LBL_LOCK_FORM_FIELD_CMD_TYPE')->setAttrib('id', Qstat_Db_Table_LockManagment::COL_LOCK_CMD_TYPE)->setRequired(TRUE)->setAllowEmpty(FALSE);
		$objElement->addMultiOptions(Qstat_Db_Table_LockManagment::$arrCmdLock);
		$this->addElement($objElement);
	}
	
	public function setOperator($strOper){
		$objElement = new Zend_Form_Element_Hidden('oper');
		$objElement->setValue($strOper);
		$this->addElement($objElement);		
	}
	
	public function setEavType($intEavTypeId){
		$objElement = new Zend_Form_Element_Hidden(Qstat_Db_Table_LockManagment::COL_LOCK_EAV_TYPE);
		$objElement->setValue($intEavTypeId);
		$this->addElement($objElement);		
	}
	
}