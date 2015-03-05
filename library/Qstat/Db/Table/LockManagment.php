<?php

class Qstat_Db_Table_LockManagment extends Bf_Db_Table
{
	CONST TBL_NAME = "lockscripts";

	CONST COL_ID_LOCK_MNG = 'id_lockscript';
	CONST COL_LOCK_NAME	= 'lockscript_name';
	CONST COL_LOCK_CMD_CODE	= 'lockscript_command_code';
	CONST COL_LOCK_CMD_TYPE	= 'lockscript_command_type';
	CONST COL_LOCK_EAV_TYPE	= 'lockscript_eav_type';
	CONST COL_LOCK_PRM	= 'lockscript_params';
	CONST LCK_CMD_LOCK = 'lock';
	CONST LCK_CMD_UNLOCK = 'unlock';

	CONST LCK_SCRIPT_ATTRIB_PREFIX = 'attrib_prefix';
	CONST LCK_SCRIPT_ATTRIB_CODE = 'attrib_code';

	protected $_name = self::TBL_NAME;

	public static $arrCmdLock = array(
		self::LCK_CMD_LOCK => "LBL_LOCK_TYPE_LOCK",
		self::LCK_CMD_UNLOCK => "LBL_LOCK_TYPE_UNLOCK"
	);

	public static function getPairSelect($intEavType, $strLockCmd){
		$objModel = new self();
		$objSelect = $objModel->select(TRUE);
		$objSelect->where(self::COL_LOCK_EAV_TYPE." = ?",$intEavType);
		$objSelect->where(self::COL_LOCK_CMD_TYPE." = ?",$strLockCmd);
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);

		return $objSelect;
	}

	public function prepereCmd (Bf_Catalog $objCatalog, $objCatRow, $objLockRow, $intLockId){
		// Get Script Row
		$objScriptRowSet = $this->find($intLockId);
		if ($objScriptRowSet->count()==0){
			throw new Bf_Exception();
		}

		$objScriptRow = $objScriptRowSet->current();

		$strCmd = $objScriptRow->{self::COL_LOCK_CMD_CODE};
		$strCmd .= " ".$this->getUserDetails($strCmd, $objLockRow);

		$objLockScriptParams = new Qstat_Db_Table_LockManagmentParams();

		$strCmd .= " ".$objLockScriptParams->getScriptParams($objCatalog, $objCatRow, $intLockId);

		return $strCmd;
	}

	public function getUserDetails($strCmd, $objLockRow){

		// Get User Name
		$objUser = new User_Model_Db_Users();
		$objUSerRowSet = $objUser->find($objLockRow->{Qstat_Db_Table_Lock::COL_ID_USER});

		if (0 == $objUSerRowSet->count()){
			throw new Bf_Exception();
		}
		$objUSerRow = $objUSerRowSet->current();

		$strUserDetails = '-user '.$objUSerRow->{User_Model_Db_Users::COL_LOGIN};

		return $strUserDetails;
	}


}