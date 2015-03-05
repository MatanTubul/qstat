<?php

class Qstat_Db_Table_LockManagmentParams extends Bf_Db_Table
{
	CONST TBL_NAME = "lockscripts_params";

	CONST COL_ID_LOCK_MNG_PARAM = 'id_lockscripts_params';
	CONST COL_ID_LOCK_MNG = 'id_lockscript';
	CONST COL_LOCK_PARAM_PREFIX	= 'param_prefix';
	CONST COL_LOCK_PARAM_NAME	= 'param_name';
	CONST COL_LOCK_PARAM_SUFFIX	= 'param_suffix';
	CONST COL_LOCK_PARAM_ATTRIB_ID	= 'id_attributes';

	CONST PREFIX_NONE = '';
	CONST PREFIX_SINGLE = '-';
	CONST PREFIX_DOUBLE = '--';

	CONST SUFFIX_NONE = '';
	CONST SUFFIX_EQUALE = '=';
	CONST SUFFIX_DASH = '-';
	CONST SUFFIX_DASH_SPACED = ' - ';

	protected $_name = self::TBL_NAME;

	public static $arrPrefix = array(
		self::PREFIX_NONE => '',
		self::PREFIX_SINGLE => '-',
		self::PREFIX_DOUBLE => '--',
	);

	public static $arrSuffix = array(
		self::SUFFIX_NONE => '',
		self::SUFFIX_EQUALE => '=',
		self::SUFFIX_DASH => '-',
		self::SUFFIX_DASH_SPACED => ' - ',
	);

	public function getScriptParams(Bf_Catalog $objCatalog, $objCatRow, $intLockId){
		$strCmdParams = "";

		// Get All Lock Params
		$objSelect = $this->select(TRUE);
		$objSelect->where(self::COL_IS_DELETED." = ?",false);
		$objSelect->where(self::COL_ID_LOCK_MNG." = ?",$intLockId);

		$objLockScrRowSet = $this->fetchAll($objSelect);

		foreach ($objLockScrRowSet as $objLockScrRow){
			$strCmdParams .= " ";
			// Prefix
			$strCmdParams .= $objLockScrRow->{self::COL_LOCK_PARAM_PREFIX};
			// Code
			$strCmdParams .= $objLockScrRow->{self::COL_LOCK_PARAM_NAME};
			// Suffix
			$strCmdParams .= $objLockScrRow->{self::COL_LOCK_PARAM_SUFFIX};
			// Value
			// Get Row Value Id
			$objAttr = new Bf_Eav_Db_Attributes();
			$objAttrSelect = $objAttr->select(TRUE)->setIntegrityCheck(FALSE);
			$objAttrSelect->join(Bf_Eav_Db_EntitiesValues::TBL_NAME,Bf_Eav_Db_EntitiesValues::TBL_NAME.'.'.Bf_Eav_Db_EntitiesValues::COL_ID_ATTR." = ".Bf_Eav_Db_Attributes::TBL_NAME.'.'.Bf_Eav_Db_Attributes::COL_ID_ATTR);
			$objAttrSelect->where(Bf_Eav_Db_Attributes::TBL_NAME.'.'.Bf_Eav_Db_Attributes::COL_ID_ATTR." = ?",$objLockScrRow->{self::COL_LOCK_PARAM_ATTRIB_ID});
			$objAttrSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.'.'.Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES." = ?",$objCatRow->{Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES});

			$objAttrRow = $objAttr->fetchRow($objAttrSelect);

			if (empty($objAttrRow)){
				throw new Exception('No Attribute '.$objLockScrRow->{self::COL_LOCK_PARAM_ATTRIB_ID});
			}

			$objValue = Bf_Eav_Value::factory($objAttrRow->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE});

			$strValueClassName = $objValue->getValuesDbClassName();

			$objValueDb = new $strValueClassName();
			$objValueRowSet = $objValueDb->find($objAttrRow->{Bf_Eav_Db_EntitiesValues::COL_ID_VALUES});

			if (0 == $objValueRowSet->count()){
				throw new Exception("No Value Row");
			}
			$objValueRow = $objValueRowSet->current();

			$strCmdParams .= $objValueRow->{Bf_Eav_Db_Values_Abstract::COL_VALUE};

		}

		return $strCmdParams;
	}

}