<?php

class Qstat_Db_Table_AttribFilter extends Bf_Db_Table
{
	CONST TBL_NAME = "attribute_filtering";

	CONST COL_ID_ATTRIB_FILTER = 'id_attrib_filt';
	CONST COL_ID_GROUPS = 'id_groups';
	CONST COL_ID_USER	= 'id_users';
	CONST COL_ATRIBUTE_ID	= 'id_attributes';
	CONST COL_FILTER_BY	= 'filtering_by';
	CONST COL_IS_DELETED	= 'is_deleted';

	protected $_name = self::TBL_NAME;

	/**
	 *
	 * Enter description here ...
	 * @return Zend_Db_Select
	 */
	public static function getPairSelect($intSiteId = null){
		$objModel = new self();
		$objSelect = $objModel->select(TRUE);
		$objSelect->reset(Zend_Db_Select::COLUMNS);
		$objSelect->columns(array(self::COL_ID_GROUPS,self::COL_GROUP_NAME));
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);

		if (!empty($intSiteId)){
			$objSelect->where(self::COL_ID_SITES." = ?",$intSiteId);
		}

		return $objSelect;
	}


}