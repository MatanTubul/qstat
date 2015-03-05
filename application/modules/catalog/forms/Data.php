<?php
class Catalog_Form_Data extends Bf_Catalog_Data_Form_Abstract {

	/**
	 * (non-PHPdoc)
	 * @see Bf_Catalog_Data_Form_Abstract::getElements()
	 */
	public function getElements($intEntTypeId){

		$arrElements[Catalog_Model_CatalogData::COL_TITLE] = $this->createTitleElement();
		$arrElements[Catalog_Model_CatalogData::COL_DESCRIPTION] = $this->createDescriptionElement();
		if (! $this->getOptions('isFolder')) {
			$arrElements[Catalog_Model_CatalogData::COL_ID_SITES] = $this->createSiteElement();
			$arrElements[Catalog_Model_CatalogData::COL_ID_GROUPS] = $this->createGroupElement();
			$arrElements[Catalog_Model_CatalogData::COL_IS_SHARED] = $this->createIsSharedElement();
			$arrElements[Catalog_Model_CatalogData::COL_ID_SCRIPT_LOCK] = $this->createIdLockScript($intEntTypeId);
			$arrElements[Catalog_Model_CatalogData::COL_ID_SCRIPT_UNLOCK] = $this->createIdUnLockScript($intEntTypeId);
			$arrElements[Catalog_Model_CatalogData::COL_LOCK_LIMIT_TIME] = $this->createLockLimitTime($intEntTypeId);
		}
		return $arrElements;
	}

	/**
	 *
	 * @return Zend_Form_Element_Text
	 */
	protected function createTitleElement(){
		$objElement = new Zend_Form_Element_Text(Catalog_Model_CatalogData::COL_TITLE);
		$objElement->setLabel('LBL_CAT_FORM_DATA_FIELD_TITLE')->setAttrib('id', Catalog_Model_CatalogData::COL_TITLE)->setRequired(TRUE);
		return $objElement;
	}
	protected function createGroupElement(){
		$objElement = new Bf_Form_Element_DbSelect(Catalog_Model_CatalogData::COL_ID_GROUPS);
		$objElement->setIdentityColumn(Qstat_Db_Table_Groups::COL_ID_GROUPS)->setDbSelect(Qstat_Db_Table_Groups::getPairSelect())->setValueColumn(Qstat_Db_Table_Groups::COL_GROUP_NAME);//->setRequired(TRUE);
		$objElement->addMultiOption(0,Zend_Registry::get("Zend_Translate")->translate('LBL_EAV_SELECT_OR_LEAVE') );
		$objElement->setLabel('Group')->setAttrib('id', Catalog_Model_CatalogData::COL_ID_GROUPS);//->setRequired(TRUE);
		return $objElement;
	}
	protected function createSiteElement(){
        $objElement = new Bf_Form_Element_DbSelect(Catalog_Model_CatalogData::COL_ID_SITES);
		$objElement->setIdentityColumn(Qstat_Db_Table_Sites::COL_ID_SITES)->setDbSelect(Qstat_Db_Table_Sites::getPairSelect())->setValueColumn(Qstat_Db_Table_Sites::COL_SITE_TITLE);//->setRequired(TRUE);
		$objElement->addMultiOption(0,Zend_Registry::get("Zend_Translate")->translate('LBL_EAV_SELECT_OR_LEAVE') );
		$objElement->setLabel('LBL_CAT_FORM_DATA_FIELD_SITE')->setAttrib('id', Catalog_Model_CatalogData::COL_ID_GROUPS);//->setRequired(TRUE);
		return $objElement;
	}

	protected function createDescriptionElement(){
		$objElement = new Zend_Form_Element_Text(Catalog_Model_CatalogData::COL_DESCRIPTION);
		$objElement->setLabel('LBL_CAT_FORM_DATA_FIELD_DESCRIPTION')->setAttrib('id', Catalog_Model_CatalogData::COL_DESCRIPTION)->setRequired(FALSE);
		return $objElement;
	}

	protected function createIsSharedElement(){
		$objElement = new Zend_Form_Element_Checkbox(Catalog_Model_CatalogData::COL_IS_SHARED);
		$objElement->setLabel('LBL_CAT_FORM_DATA_FIELD_IS_SHARED')->setAttrib('id', Catalog_Model_CatalogData::COL_IS_SHARED)->setRequired(FALSE);
		return $objElement;
	}

	protected  function createIdLockScript($intEntTypeId){
		$objElement = new Bf_Form_Element_DbSelect(Catalog_Model_CatalogData::COL_ID_SCRIPT_LOCK);
		$objElement->setIdentityColumn(Qstat_Db_Table_LockManagment::COL_ID_LOCK_MNG)->setDbSelect(Qstat_Db_Table_LockManagment::getPairSelect($intEntTypeId, Qstat_Db_Table_LockManagment::LCK_CMD_LOCK))->setValueColumn(Qstat_Db_Table_LockManagment::COL_LOCK_NAME)->setRequired(TRUE);
		$objElement->addMultiOption(0,Zend_Registry::get("Zend_Translate")->translate('LBL_EAV_SELECT_OR_LEAVE') );
		$objElement->setLabel('LBL_CAT_FORM_DATA_FIELD_ID_LOCK')->setAttrib('id', Catalog_Model_CatalogData::COL_ID_SCRIPT_LOCK)->setRequired(FALSE);
		return $objElement;
	}
	protected  function createIdUnLockScript($intEntTypeId){
		$objElement = new Bf_Form_Element_DbSelect(Catalog_Model_CatalogData::COL_ID_SCRIPT_UNLOCK);
		$objElement->setIdentityColumn(Qstat_Db_Table_LockManagment::COL_ID_LOCK_MNG)->setDbSelect(Qstat_Db_Table_LockManagment::getPairSelect($intEntTypeId, Qstat_Db_Table_LockManagment::LCK_CMD_UNLOCK))->setValueColumn(Qstat_Db_Table_LockManagment::COL_LOCK_NAME)->setRequired(TRUE);
		$objElement->addMultiOption(0,Zend_Registry::get("Zend_Translate")->translate('LBL_EAV_SELECT_OR_LEAVE') );
		$objElement->setLabel('LBL_CAT_FORM_DATA_FIELD_ID_UnLOCK')->setAttrib('id', Catalog_Model_CatalogData::COL_ID_SCRIPT_UNLOCK)->setRequired(FALSE);
		return $objElement;
	}
	protected function createLockLimitTime(){
		$objElement = new Zend_Form_Element_Text(Catalog_Model_CatalogData::COL_LOCK_LIMIT_TIME);
		$objElement->setLabel('LBL_CAT_FORM_DATA_FIELD_LOCK_LIMIT_TIME_MINUTES')->setAttrib('id', Catalog_Model_CatalogData::COL_LOCK_LIMIT_TIME)->setRequired(FALSE);
		return $objElement;
	}


}