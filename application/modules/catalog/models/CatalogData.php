<?php

class Catalog_Model_CatalogData extends Bf_Catalog_Data_Db_Table_Abstract {

	const COL_TITLE 		= 'title';
	const COL_DESCRIPTION 	= 'description';

	const COL_IP_ADDRESS = 'ip_address';
	const COL_ID_GROUPS = 'id_groups';
	const COL_ID_SITES = 'id_sites';
	const COL_IS_SHARED = 'is_shared';
	const COL_CAT_ID = 'cat_id';
	const COL_ID_SCRIPT_LOCK = 'id_script_lock';
	const COL_ID_SCRIPT_UNLOCK = 'id_script_unlock';
	const COL_LOCK_LIMIT_TIME = 'lock_limit_time';

	const SERVER_DEVICES = 'Servers';
	const SWITCH_DEVICES = 'Switches';
	const ORCA_DEVICES   = 'Orca';
	const CABLES_DEVICES = 'Cables';
	const CARDS_DEVICES  = 'Cards';

	/**
	* (non-PHPdoc)
	* @see Bf_Catalog_Models_Db_Data_Interface::addDataToCatalogSelect()
	*/
	public function addDataToCatalogSelect(Zend_Db_Table_Select &$objSelect,$strCatlogIdColumn,$strCatalogTableName,$intLanguageId = null) {
		$strCond = self::TBL_NAME.".".self::COL_ID_CATALOG."={$strCatalogTableName}.{$strCatlogIdColumn}";
		if (!is_null($intLanguageId)) {
			$strCond .= " AND ".self::TBL_NAME.".".self::COL_ID_LANGUAGES."=".(int)$intLanguageId;
		}

		$objSelect->joinLeft(self::TBL_NAME,$strCond,array(
			self::getColumnName(self::COL_TITLE),
			self::getColumnName(self::COL_DESCRIPTION),
			self::getColumnName(self::COL_IP_ADDRESS),
			self::getColumnName(self::COL_ID_GROUPS),
			self::getColumnName(self::COL_ID_SITES),
			self::getColumnName(self::COL_IS_SHARED),
			self::getColumnName(self::COL_ID_SCRIPT_LOCK),
			self::getColumnName(self::COL_ID_SCRIPT_UNLOCK),
			self::getColumnName(self::COL_LOCK_LIMIT_TIME),
		));

		$objSelect->columns(array(self::COL_CAT_ID=>self::TBL_NAME.".".self::COL_ID_CATALOG));
		// $objSelect->order(self::TBL_NAME.self::COL_TITLE." ".Zend_Db_Select::SQL_ASC);
	}

	public static function getParentId($deviceName) {
		$objCatalogData = new self();
		$objCatalogDataSelect = $objCatalogData
		->select(TRUE)->setIntegrityCheck(FALSE)
		->join(
			Bf_Catalog_Models_Db_Catalog::TBL_NAME,
			Bf_Catalog_Models_Db_Catalog::TBL_NAME.'.'.Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."=".self::TBL_NAME.'.'.self::COL_ID_CATALOG.
			" AND ".Bf_Catalog_Models_Db_Catalog::TBL_NAME.'.'.Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED." = 0"
		)
		->where( self::TBL_NAME.'.'.self::COL_TITLE . "=?", $deviceName )
		->where( self::TBL_NAME.'.'.self::COL_IS_DELETED." = 0" )
		->where( Bf_Catalog_Models_Db_Catalog::TBL_NAME.'.'.Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER." = 1" )
		->where( Bf_Catalog_Models_Db_Catalog::TBL_NAME.'.'.Bf_Catalog_Models_Db_Catalog::COL_HAS_CHILDREN." = 1" )
		->reset(Zend_Db_Select::COLUMNS)
		->columns( array( self::TBL_NAME.'.'.self::COL_ID_CATALOG, ) );
		$objCatalogDataRow = $objCatalogData->fetchRow($objCatalogDataSelect);

		return ( isset( $objCatalogDataRow->{self::COL_ID_CATALOG} ) ) ? intval( $objCatalogDataRow->{self::COL_ID_CATALOG} ) : 0;
	}

	public function getDeviceName($id) {
		$objCatalogDataRowSet = $this->find( array( $id, 0 ), array( Catalog_Model_CatalogData::COL_ID_CATALOG, Catalog_Model_CatalogData::COL_ID_LANGUAGES ) );
		return
		$objCatalogDataRowSet
		->current()
		->{Catalog_Model_CatalogData::COL_ID_TITLE};
	}
}
