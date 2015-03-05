<?php

class Bf_Eav_Db_Attributes extends Bf_Db_Table
{
	CONST TBL_NAME = "attributes";

	CONST COL_ID_ATTR = 'id_attributes';
	CONST COL_ATTR_CODE = 'attribute_code';
	CONST COL_LANSWEEPER_CODE = 'lansweeper_code';
	CONST COL_DESCRIPTION = 'description';
	CONST COL_UNITS = 'units';
	CONST COL_VALUE_TYPE = 'value_type';
	CONST COL_IS_MULTI_VALUE = 'is_multi_value';
	CONST COL_IS_SHOW_LIST = 'is_show_list';
	CONST COL_IS_USER_CAN_EDIT = 'is_user_can_edit';
	CONST COL_IS_DELETED = 'is_deleted';

	CONST ATTR_VAL_TYPE_TEXT = 'text';
	CONST ATTR_VAL_TYPE_LONGTEXT = 'longtext';
	CONST ATTR_VAL_TYPE_INT = 'integer';
	CONST ATTR_VAL_TYPE_FLOAT = 'float';
	CONST ATTR_VAL_TYPE_DATETIME = 'datetime';
	CONST ATTR_VAL_TYPE_SELECT = 'select';
	CONST ATTR_VAL_TYPE_FILE = 'file';
	CONST ATTR_VAL_TYPE_BLOB = 'blob';
	CONST ATTR_VAL_TYPE_DATE = 'date';
	CONST ATTR_VAL_TYPE_BOOLEAN = 'boolean';
	CONST ATTR_VAL_TYPE_MULTISELECT = 'multiselect';
	CONST ATTR_VAL_TYPE_RADIOSELECT = 'radioselect';
	CONST ATTR_VAL_TYPE_CHECKLIST = 'checklist';

	protected $_name = self::TBL_NAME;

	public static $arrAttrValType = array(
		self::ATTR_VAL_TYPE_TEXT => 'LBL_ATTR_VAL_TYPE_TEXT',
		self::ATTR_VAL_TYPE_LONGTEXT => 'LBL_ATTR_VAL_TYPE_LONGTEXT',
		self::ATTR_VAL_TYPE_INT => 'LBL_ATTR_VAL_TYPE_INT',
		self::ATTR_VAL_TYPE_FLOAT => 'LBL_ATTR_VAL_TYPE_FLOAT',
		//		self::ATTR_VAL_TYPE_DATETIME => 'LBL_ATTR_VAL_TYPE_DATETIME',
		//		self::ATTR_VAL_TYPE_FILE => 'LBL_ATTR_VAL_TYPE_FILE',
		//		self::ATTR_VAL_TYPE_BLOB => 'LBL_ATTR_VAL_TYPE_BLOB',
		//		self::ATTR_VAL_TYPE_DATE => 'LBL_ATTR_VAL_TYPE_DATE',
		self::ATTR_VAL_TYPE_SELECT => 'LBL_ATTR_VAL_TYPE_SELECT'
	);

	public static function getId($strAtribCode){
		$objAttr = new self();

		$objAtributeSelect = $objAttr
		->select(TRUE)
		->where(
			self::COL_ATTR_CODE." = '".$strAtribCode."' or ".
			self::COL_DESCRIPTION." =  '".$strAtribCode."' or ".
			self::COL_ATTR_CODE." = '".str_replace(" ","_",$strAtribCode)."' or ".
			self::COL_DESCRIPTION." =  '".str_replace(" ","_",$strAtribCode)."'"
		)
		->where(self::COL_IS_DELETED." = 0");

		$objAtributeRowSet=$objAttr->fetchAll($objAtributeSelect);

		$data = ($objAtributeRowSet->toArray());

		return @$data[0]['id_attributes'];
	}


	public static function getAttribList($boolReload = FALSE,$boolGetAll = FALSE, $arrIds = NULL){
		$objAttr = new self();
		$objSelect = $objAttr->select(TRUE);
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);
		if (!$boolGetAll) {
			$objSelect->where(self::COL_IS_SHOW_LIST." = ?",TRUE);
		}

		if (!empty($arrIds)){
			$objSelect->where(self::COL_ID_ATTR." in (?)",$arrIds);
		}

		return $objAttr->fetchAll($objSelect);

	}

	public static function getIsUserCanEdit($strAtribCode){

		$session = new Zend_Session_Namespace("atributes");
		if (empty($session->data)){
			$objAttr = new self();
			$objAtributeSelect = $objAttr->select(TRUE);
			$objAtributeSelect->where(Bf_Eav_Db_Attributes::COL_IS_DELETED."= ?",false);
			$objAtributeSelect->where(Bf_Eav_Db_Attributes::COL_IS_USER_CAN_EDIT."= ?",TRUE);
			$objAtributeRowSet=$objAttr->fetchAll($objAtributeSelect);

			$arrResult=array();
			foreach ($objAtributeRowSet as $objAtributeRow ){
				$strAtribudeSlug=str_replace(' ','',$objAtributeRow->{Bf_Eav_Db_Attributes::COL_ATTR_CODE});
				$arrResult[$strAtribudeSlug]=true;
			}
			$session->data=$arrResult;
		}

		if (!empty($session->data[$strAtribCode])){
			return true;
		}
		return false;
	}

	public static function getPairSelect($intId = null){
		$objModel = new self();
		$objSelect = $objModel->select(TRUE);
		$objSelect->reset(Zend_Db_Select::COLUMNS);
		$objSelect->columns(array(self::COL_ID_ATTR,self::COL_ATTR_CODE));
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);
		$objSelect->order(self::COL_ATTR_CODE);

		if (!empty($intId)){
			$objSelect->where(self::COL_ID_ATTR." = ?",$intId);
		}

		return $objSelect;
	}
}