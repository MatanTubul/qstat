<?php

class Qstat_Db_Table_Sites extends Bf_Db_Table
{
	CONST TBL_NAME = "sites";

	CONST COL_ID_SITES = 'id_sites';
	CONST COL_SITE_TITLE	= 'site_title';
	CONST COL_IS_DELETED	= 'is_deleted';
	CONST COL_USE_CUSTOM_COLUMNS = 'use_custom_fields';
	CONST COL_CUSTOM_COLUMNS = 'custom_fields';

	protected $_name = self::TBL_NAME;

	/**
	*
	* Enter description here ...
	* @return Zend_Db_Select
	*/
	public static function getPairSelect(){
		$objModel = new self();
		$objSelect = $objModel->select(TRUE);
		$objSelect->reset(Zend_Db_Select::COLUMNS);
		$objSelect->columns(array(self::COL_ID_SITES,self::COL_SITE_TITLE));
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);

		return $objSelect;
	}
}
