<?php

class Qstat_Db_Table_LockEvents extends Bf_Db_Table
{
	CONST TBL_NAME = "lock_events";

	const COL_ID_LOCK_EVENTS = 'id_lock_events';
	const COL_ID_GROUP = 'id_groups';
	const COL_ID_USER = 'id_users';
	const COL_TIME_UNLOCK = 'time_unlock';
	const COL_IS_EACH_DATE = 'is_each_date';
	const COL_IS_DELETED = 'is_deleted';

	protected $_name = self::TBL_NAME;

	public static function unlock(){
		// get Currnet time.
		$objDateCurrentTime=new DateTime();

		$arrConfigs= Zend_Registry::get("config");
		$strFile = APPLICATION_PATH . '/../files/tmp/cron.Qstat_Plugin_Cron_UnlockGroups.touch';
		$arrTemp=array();
		if (file_exists($strFile)){
			$arrTemp['datetime']=fileatime($strFile);
			$arrTemp['name']=basename($strFile);
			$arrTemp['path']=$strFile;
			$arrTemp['dir_path']=dirname($strFile);
			$objDateLastUpdatesTime=new DateTime();
			$objDateLastUpdatesTime->setTimestamp($arrTemp['datetime']);
		}

		//get all
		$objLockEvent= new self();
		$objLockEventSelect=$objLockEvent->select();
		$objLockEventSelect->where(self::COL_IS_DELETED." = ?",false);
		$objLockEventRowSet=$objLockEvent->fetchAll($objLockEventSelect);

		foreach ($objLockEventRowSet as $objLockEventRow){
			//1 check if unlocked today
			$objLockEventLog=new Qstat_Db_Table_LockEventsLog();
			$objLockEventLogSelect=$objLockEventLog->select();
			$objLockEventLogSelect->where(Qstat_Db_Table_LockEventsLog::COL_ID_LOCK_EVENTS." = ? ",$objLockEventRow->{self::COL_ID_LOCK_EVENTS} );
			$objLockEventLogSelect->where(Qstat_Db_Table_LockEventsLog::COL_DATE_TIME_UNLOCK." LIKE '".$objDateCurrentTime->format($arrConfigs["dateformat"]["php"]["mysqldate"])." %'");
			$objLockEventLogSelect->where(Qstat_Db_Table_LockEventsLog::COL_IS_DELETED." = ?",false);
			$objLockEventLogRowSet = $objLockEventLog->fetchAll($objLockEventLogSelect);
			if (!empty($objLockEventLogRowSet)&&$objLockEventLogRowSet->count()>0){
				//				echo "<br>";
				//				echo "unlocked today";
				continue;
			}

			if (intval($objDateCurrentTime->format("H"))>= intval($objLockEventRow->{self::COL_TIME_UNLOCK})){
				//				echo "<br>";
				//				echo "release all llocks by group";

				//unlock all locks by group id
				$intGroupId = $objLockEventRow->{self::COL_ID_GROUP};
				$objLockEvent->unlockByGroup($intGroupId);

				//set log that
				$objLockEventLog=new Qstat_Db_Table_LockEventsLog();
				$objLockEventLog = $objLockEventLog->createRow();
				$objLockEventLog->{Qstat_Db_Table_LockEventsLog::COL_ID_LOCK_EVENTS}=$objLockEventRow->{self::COL_ID_LOCK_EVENTS};
				$objLockEventLog->{Qstat_Db_Table_LockEventsLog::COL_DATE_TIME_UNLOCK}=$objDateCurrentTime->format($arrConfigs["dateformat"]["mysql"]["shortdatetime"]);
				$objLockEventLog->save();
			}else{

				//echo "Time Not Seted";
			}
		}
	}

	function unlockByGroup($intGroup){

		if (empty($intGroup)){
			return false;
		}
		$objLocks = new Qstat_Db_Table_Lock();
		$objLocksSelect = $objLocks->select(TRUE)->setIntegrityCheck(FALSE);
		$objLocksSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
			Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . " = " . Qstat_Db_Table_Lock::TBL_NAME . '.' . Qstat_Db_Table_Lock::COL_ID_CATALOG. " AND ".Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED."='' AND ".Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED."=".true );
		$objLocksSelect->join(Catalog_Model_CatalogData::TBL_NAME ,Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG . " = ".Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG);;
		$objLocksSelect->where(Qstat_Db_Table_Lock::TBL_NAME . '.' . Qstat_Db_Table_Lock::COL_IS_DELETED . " = ?", FALSE);
		$objLocksSelect->where(Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_GROUPS . " = ?", $intGroup);
		$objLocksRowSet = $objLocks->fetchAll($objLocksSelect);

		$config = new Zend_Config_Ini(APPLICATION_PATH."/modules/catalog/configs/module.ini","production");
		$objApplicationOptions = new Zend_Config($config->toArray());
		$objCatalog = new Bf_Catalog($objApplicationOptions->catalog);

		$strResult="";
		foreach ($objLocksRowSet as $objLocksRow){
			if (empty($objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_END})) {
				$strResult.= "----- ".$objLocksRow->{Catalog_Model_CatalogData::COL_TITLE}."\n" ;
				$objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_END} = date(Qstat_Db_Table_Lock::MYSQL_DATETIME);
				$objLocksRow->save();

				$objItemRow = $objCatalog->getCatalogModel()
				->getObjCatalogTable()
				->find($objLocksRow->{Catalog_Model_CatalogData::COL_ID_CATALOG})
				->current();
				$objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} = 0;
				$objItemRow->save();
			}
		}

		//		         echo "<br>";
		//		         echo $strResult;

		return true;
	}

}