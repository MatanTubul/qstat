<?php

class Qstat_Db_Table_Lock extends Bf_Db_Table
{
	CONST TBL_NAME = "locks";

	const COL_ID_LOCK = 'id_lock';
	const COL_ID_CATALOG = 'id_catalog';
	const COL_ID_GROUP = 'id_groups';
	const COL_ID_USER = 'id_users';
	const COL_LOCK_START = 'lock_start';
	const COL_LOCK_START_SCRIPT_ON = 'lock_start_script_on';
	const COL_LOCK_START_SCRIPT_COMMAND = 'lock_start_script_command';
	const COL_LOCK_START_SCRIPT_OUTPUT = 'lock_start_script_output';
	const COL_LOCK_END = 'lock_end';
	const COL_LOCK_END_SCRIPT_ON = 'lock_end_script_on';
	const COL_LOCK_END_SCRIPT_COMMAND = 'lock_end_script_command';
	const COL_LOCK_END_SCRIPT_OUTPUT = 'lock_end_script_output';
	CONST COL_LOCK_SCHEDULED_LOCK = 'lock_scheduled_lock';
	CONST COL_LOCK_SCHEDULED_UNLOCK = 'lock_scheduled_unlock';
	CONST COL_LOCK_NOTIFICATION_SENT = 'lock_notification_sent';
	CONST COL_END_LOCK_NOTIFICATION_SENT = 'end_lock_notification_sent';

	protected $_name = self::TBL_NAME;
	protected $_referenceMap = array();

	public function __construct ($config = array(), $definition = null)
	{
		parent::__construct($config, $definition);

		$this->_referenceMap = array(
			'Catalog' => array(
				'columns' => array(self::COL_ID_CATALOG),
				'refTableClass' => 'Catalog_Model_CatalogData',
				'refColumns' => array(Catalog_Model_CatalogData::COL_ID_CATALOG),
				'displayColumn' => Catalog_Model_CatalogData::COL_TITLE)
		);
	}

	public function createLock($intCatalogId, $intUserId, $defaultUnlockTime = 0, $scheduledUnlockTime = 0)
	{
		$arrLockData[self::COL_ID_CATALOG] = $intCatalogId;
		$arrLockData[self::COL_ID_USER] = $intUserId;

		// Get Cat Data
		$objCatdata = new Catalog_Model_CatalogData();
		$objCatdataRow = $objCatdata->find($intCatalogId, 0)->current();
		$arrLockData[self::COL_LOCK_START] = date(self::MYSQL_DATETIME);

		if ( intval($scheduledUnlockTime) ) {
			// Was supplied the Scheduled Unlock Time.
			$intLockLimitSeconds = $scheduledUnlockTime + time();
		} elseif ( ! empty($objCatdataRow) && ! empty( $objCatdataRow->{Catalog_Model_CatalogData::COL_LOCK_LIMIT_TIME} ) && intval( $objCatdataRow->{Catalog_Model_CatalogData::COL_LOCK_LIMIT_TIME} ) ) {
			// The Lock Limit Time is in minuts.
			$intLockLimitSeconds = ( intval( $objCatdataRow->{Catalog_Model_CatalogData::COL_LOCK_LIMIT_TIME} ) * 60 ) + time();
		} else {
			$intLockLimitSeconds = $defaultUnlockTime + time();
		}
		$arrLockData[self::COL_LOCK_SCHEDULED_UNLOCK] = date(self::MYSQL_DATETIME, $intLockLimitSeconds);

		$objLockRow = $this->createRow($arrLockData);
		return $objLockRow->save();
	}

	public function releaseLock($intLockId, $intCatalogId, $userObject = null)
	{
		$objLockRowSet = $this->find($intLockId);
		if ( ! $objLockRowSet->count() ) {
			return false;
		}
		// Lock found.
		$objLockRow = $objLockRowSet->current();

		$objLockedByRow = self::getLockUser($intCatalogId);

		if ( ! empty( $userObject->{User_Model_Db_Users::COL_ID_USERS} ) && $userObject->{User_Model_Db_Users::COL_ID_USERS} !== $objLockedByRow->{User_Model_Db_Users::COL_ID_USERS} ) {
			// Enable to unlock machines which lock by other user.
			// Enable for "Group Manager" to unlock machines which locked by other users in order to manage their groups.
			// The "Group Manager" will have the ability to unlock only machines which related to his group.
			$userGroup = $userLockedByGroup = 0;
			$arrUserExtraData = $userObject->extraArray;
			if ( ! empty($arrUserExtraData) && ! empty($arrUserExtraData['groups']) ) {
				$userGroup = intval( $arrUserExtraData['groups'] );
			}
			$arrLockedByExtra = unserialize( $objLockedByRow->{User_Model_Db_Users::COL_EXTRA_DATA} );
			if ( ! empty($arrLockedByExtra) && ! empty($arrLockedByExtra['groups']) ) {
				$userLockedByGroup = intval( $arrLockedByExtra['groups'] );
			}
			$objRolesTable = new User_Model_Db_Roles();

			if (
				! $userGroup ||
				$userGroup !== $userLockedByGroup ||
				empty( $userObject->id_roles ) ||
				(
					intval( $userObject->id_roles ) < intval( $objRolesTable->getRoleId('group_mng') )
				)
				) {
					$objCatalogData = new Catalog_Model_CatalogData();

					return
					Ingot_JQuery_JqGrid::RETURN_CODE_ERROR.' '.
					'for Machine '.$objCatalogData->getDeviceName($intCatalogId).'. '.
					Zend_Registry::get( 'Zend_Translate' )->translate('LBL_API_LOCK_EXIST')." by another user ".$objLockedByRow->{User_Model_Db_Users::COL_FIRST_NAME}." ".$objLockedByRow->{User_Model_Db_Users::COL_LAST_NAME}.PHP_EOL;
			}
		}

		if ( $objLockRow->{self::COL_ID_CATALOG} == $intCatalogId && empty($objLockRow->{self::COL_LOCK_END}) ) {
			// CatalogId Matched && Lock is still active.
			$objLockRow->{self::COL_LOCK_END} = date(self::MYSQL_DATETIME);
			return (bool) intval( $objLockRow->save() );
		}

		return false;
	}

	public function runStartLocks ($objCatalog)
	{
		// Get All Locks
		$objSelect = $this->select(TRUE);
		$objSelect->where(self::COL_LOCK_START_SCRIPT_ON . " IS NULL");

		$objRowSet = $this->fetchAll($objSelect);
		foreach ($objRowSet as $objRow) {
			$this->executeCommand($objCatalog, $objRow, TRUE);
		}
	}

	public function runEndLocks ($objCatalog)
	{
		// Get All Locks
		$objSelect = $this->select(TRUE);
		$objSelect->where(self::COL_LOCK_END . " IS NOT NULL");
		$objSelect->where(self::COL_LOCK_END_SCRIPT_ON . " IS NULL");
		$objRowSet = $this->fetchAll($objSelect);

		foreach ($objRowSet as $objRow) {
			$this->executeCommand($objCatalog, $objRow, FALSE);
		}
	}

	public function runSceduledEndLocks(Bf_Catalog $objCatalog)
	{
		if ( $this->run12HInterval(1) )
		{
			// Get All Locks to mail.
			$objSelect = $this->select(TRUE);
			$objSelect->where(self::COL_LOCK_END . " IS NULL");
			$objSelect->where(self::COL_LOCK_SCHEDULED_UNLOCK . " < NOW() ");
			$objSelect->where(self::COL_END_LOCK_NOTIFICATION_SENT . " = ?", FALSE);
			$objRowSet = $this->fetchAll($objSelect);

			$usersList = array();
			foreach ($objRowSet as $objRow) {
				if ( ! $usersList[$objRow->{self::COL_ID_USER}] )
				{
					$usersList[$objRow->{self::COL_ID_USER}]['userInfo'] = $objRow;
				}
				$usersList[$objRow->{self::COL_ID_USER}]['data'][] = $objRow->{self::COL_ID_CATALOG};

				$objRow->{self::COL_END_LOCK_NOTIFICATION_SENT} = TRUE;
				$objRow->save();
			}

			foreach ($usersList as $emailData)
			{
				// Notify Owner that his lock was relesed.
				$this->_notifyUserBulkSend($objCatalog, $emailData['userInfo'] , $emailData['data'] , 'LBL_SUBJECT_EMAIL_LOCK_ENDED', 'LBL_TEXT_EMAIL_LOCK_ENDED', 'LBL_HEAD_TEXT_EMAIL_LOCK_ENDED');
			}
		}

		// Get All Locks
		$objSelect = $this->select(TRUE);
		$objSelect->where(self::COL_LOCK_END . " IS NULL");
		$objSelect->where(self::COL_LOCK_SCHEDULED_UNLOCK . " < NOW()");
		$objRowSet = $this->fetchAll($objSelect);

		foreach ($objRowSet as $objRow)
		{
			$this->releaseLock( $objRow->{self::COL_ID_LOCK}, $objRow->{self::COL_ID_CATALOG} );

			$objItemRow = $objCatalog->getCatalogModel()
			->getObjCatalogTable()
			->find($objRow->{self::COL_ID_CATALOG})
			->current();

			$objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} = 0;
			$objItemRow->save();
		}
	}

	public function runSceduledEndLocksNotification (Bf_Catalog $objCatalog, Zend_Config $objConfig)
	{
		if ($this->run12HInterval(2))
		{
			// Get All Locks
			$objSelect = $this->select(TRUE);
			$objSelect->where(self::COL_LOCK_END . " IS NULL");
			$objSelect->where(self::COL_LOCK_SCHEDULED_UNLOCK . " < DATE_ADD(NOW(), INTERVAL ? MINUTE) ", $objConfig->defaultUnlockNotificationTime);
			$objSelect->where(self::COL_LOCK_NOTIFICATION_SENT . " = ?", FALSE);

			$objRowSet = $this->fetchAll($objSelect);

			$usersList = array();
			foreach ($objRowSet as $objRow) {
				if (!$usersList[$objRow->{self::COL_ID_USER}])
				{
					$usersList[$objRow->{self::COL_ID_USER}]['userInfo'] = $objRow;
				}
				$usersList[$objRow->{self::COL_ID_USER}]['data'][] = $objRow->{self::COL_ID_CATALOG};

				$objRow->{self::COL_LOCK_NOTIFICATION_SENT} = TRUE;
				$objRow->save();
			}

			foreach ($usersList as $emailData)
			{

				$this->_notifyUserBulkSend($objCatalog, $emailData['userInfo'] , $emailData['data'] , 'LBL_SUBJECT_EMAIL_LOCK_END_NOTIFICATION', 'LBL_TEXT_EMAIL_LOCK_END_NOTIFICATION', 'LBL_HEAD_TEXT_EMAIL_LOCK_END_NOTIFICATION');
			}
		}
	}

	private function run12HInterval($type)
	{
		$emailCron = new Qstat_Db_Table_LockMailCron; //get last update
		$objSelect = $emailCron->select(TRUE);
		$objSelect->where(Qstat_Db_Table_LockMailCron::COL_CRON_TYPE . " = ? ", $type);
		$objSelect->order(Qstat_Db_Table_LockMailCron::COL_ID_LOCK_MAIL_CRON. " DESC");
		$objSelect->limit(1);

		$objRowSet=$emailCron->fetchAll($objSelect);
		$last_cron = $objRowSet->toArray();
		$last_cron = $last_cron[0]['cron_timedate'];

		$format = 'Y-m-d H:i:s';

		$date_now =  strtotime(date($format));
		$cron_date =  strtotime($last_cron);
		$cron_date = strtotime('+720 minutes', $cron_date); // ADD 12 HOURS

		if ($cron_date<$date_now)
		{
			$objRowSet[0]->{Qstat_Db_Table_LockMailCron::COL_CRON_TIMEDATE} = date($format);
			$objRowSet[0]->save();

			return true;
		}

		return false;
	}

	public function _notifyUserBulkSend (Bf_Catalog $objCatalog, $objLockRow, $indexes,  $strSbjCode, $strCode, $strHeadCode)
	{

		// Get User
		$objUser = new User_Model_Db_Users();
		$objUserRowSet = $objUser->find($objLockRow->{self::COL_ID_USER});

		if ($objUserRowSet->count() > 0) {

			$objUserRow = $objUserRowSet->current();

			$objMail = new Zend_Mail();

			$objZendTRanslate = Zend_Registry::get('Zend_Translate');
			$strSubject = $objZendTRanslate->translate($strSbjCode);

			$objMail->setSubject($strSubject);

			$strNewMailMessage = "Hello ".$objUserRow->{User_Model_Db_Users::COL_FIRST_NAME} . ' ' . $objUserRow->{User_Model_Db_Users::COL_LAST_NAME}.",<br>";

			$strNewMailMessage .= $objZendTRanslate->translate($strHeadCode)."<br>";

			foreach ($indexes as $index)
			{

				$strNewMailMessage .= $this->_prepereUserLockOffMsg($objCatalog, $objLockRow, $strCode,$objUserRowSet,$index);
			}


			$objMail->setBodyHtml($strNewMailMessage);

			$objMail->addTo($objUserRow->{User_Model_Db_Users::COL_EMAIL}, $objUserRow->{User_Model_Db_Users::COL_FIRST_NAME} . ' ' . $objUserRow->{User_Model_Db_Users::COL_LAST_NAME});
			//$objMail->addTo("danny@webmark.co.il", $objUserRow->{User_Model_Db_Users::COL_FIRST_NAME} . ' ' . $objUserRow->{User_Model_Db_Users::COL_LAST_NAME});

			try {
				$objMail->send();
			} catch (Zend_Mail_Exception $objException) {
				return FALSE;
			}

		}
		return TRUE;
	}

	public function _notifyUserLockIsOff (Bf_Catalog $objCatalog, $objLockRow, $strSbjCode, $strCode)
	{

		// Get User
		$objUser = new User_Model_Db_Users();
		$objUserRowSet = $objUser->find($objLockRow->{self::COL_ID_USER});

		$strNewMailMessage = $this->_prepereUserLockOffMsg($objCatalog, $objLockRow, $strCode,$objUserRowSet);

		$objMail = new Zend_Mail();
		$objMail->setBodyHtml($strNewMailMessage);

		if ($objUserRowSet->count() > 0) {
			$objUserRow = $objUserRowSet->current();

			$objMail->addTo($objUserRow->{User_Model_Db_Users::COL_EMAIL}, $objUserRow->{User_Model_Db_Users::COL_FIRST_NAME} . ' ' . $objUserRow->{User_Model_Db_Users::COL_LAST_NAME});
			$objZendTRanslate = Zend_Registry::get('Zend_Translate');

			$strSubject =  $this->_prepereUserLockOffMsg($objCatalog, $objLockRow, $strSbjCode,$objUserRowSet);
			$objMail->setSubject($strSubject);

			try {
				$objMail->send();
			} catch (Zend_Mail_Exception $objException) {
				return FALSE;
			}
		}
		return TRUE;
	}

	private function _prepereUserLockOffMsg (Bf_Catalog $objCatalog , $objLockRow, $strCode,$objUserRowSet=null, $index=false )
	{
		if ($index === false)
			$index = $objLockRow->{self::COL_ID_CATALOG};

		// Get Catalog Title
		$objCatalogDbRow = $objCatalog->getCatalogModel()
		->getObjCatalogData()
		->getObjDbDataTable()
		->find($index,0)
		->current();
		$objZendTRanslate = Zend_Registry::get('Zend_Translate');
		$strNewMessage = $objZendTRanslate->translate($strCode);

		//find and replace catalog title
		$strPattern = "[%title%]";
		$strNewMessage = str_replace($strPattern, $objCatalogDbRow->{Catalog_Model_CatalogData::COL_TITLE}, $strNewMessage);

		//get user details
		if (empty($objUserRow)){
			$objUser = new User_Model_Db_Users();
			$objUserRowSet = $objUser->find($objLockRow->{self::COL_ID_USER});
			if ($objUserRowSet->count() > 0) {
				$objUserRow = $objUserRowSet->current();

				//find and replace username
				$strPattern = '[%name%]';
				$strName=$objUserRow->{User_Model_Db_Users::COL_FIRST_NAME}." ".$objUserRow->{User_Model_Db_Users::COL_LAST_NAME};
				$strNewMessage = str_replace($strPattern, $strName, $strNewMessage);
			}
		}

		//find and replace date
		$strPattern = '[%date_end%]';
		$strNewMessage = str_replace($strPattern, $objLockRow->{self::COL_LOCK_SCHEDULED_UNLOCK}, $strNewMessage);

		return $strNewMessage;
	}

	/**
	*
	* Enter description here ...
	* @param Bf_Catalog $objCatalog
	* @param unknown_type $objRow
	* @param unknown_type $boolIdLockScript
	*/
	protected function executeCommand (Bf_Catalog $objCatalog, $objLockRow, $boolIsLockScript)
	{
		// Get Catalog/Catalog Data Row
		$objCatalogSelect = $objCatalog->getCatalogModel()
		->getObjCatalogTable()
		->select(TRUE)
		->setIntegrityCheck(FALSE);
		$objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . "." . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . "=?", $objLockRow->{self::COL_ID_CATALOG});
		//$objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . "." . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED . " = ?", FALSE);


		$objCatalog->getCatalogModel()
		->getObjCatalogData()
		->addDataToCatalogSelect($objCatalogSelect);

		$objCatRow = $objCatalog->getCatalogModel()
		->getObjCatalogTable()
		->fetchRow($objCatalogSelect);

		if (! empty($objCatRow)) {
			if (empty($objCatRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED})) {

				$strCmd = $this->prepereCmd($objCatalog, $objCatRow, $objLockRow, $boolIsLockScript);
				if (! empty($strCmd)) {
					$arrOutput = array();

					$strOutput = shell_exec($strCmd . " 2>&1 ");

					if ($boolIsLockScript) {
						$objLockRow->{self::COL_LOCK_START_SCRIPT_COMMAND} = $strCmd;
						$objLockRow->{self::COL_LOCK_START_SCRIPT_OUTPUT} = $strOutput; //implode('\n', $arrOutput);
						$objLockRow->{self::COL_LOCK_START_SCRIPT_ON} = date(self::MYSQL_DATETIME);
					} else {
						$objLockRow->{self::COL_LOCK_END_SCRIPT_COMMAND} = $strCmd;
						$objLockRow->{self::COL_LOCK_END_SCRIPT_OUTPUT} = $strOutput; //implode('\n', $arrOutput);
						$objLockRow->{self::COL_LOCK_END_SCRIPT_ON} = date(self::MYSQL_DATETIME);
					}
				} else {
					if ($boolIsLockScript) {
						$objLockRow->{self::COL_LOCK_START_SCRIPT_ON} = date(self::MYSQL_DATETIME);
					} else {
						$objLockRow->{self::COL_LOCK_END_SCRIPT_ON} = date(self::MYSQL_DATETIME);
					}
				}
			}
		} else {
			throw new Bf_Exception();
		}

		if (! $objLockRow->save()) {
			throw new Bf_Exception();
		}
	}

	/**
	*
	* Enter description here ...
	* @param Bf_Catalog $objCatalog
	* @param unknown_type $objCatRow
	* @param unknown_type $objLockRow
	* @param unknown_type $boolIsLockScript
	*/
	protected function prepereCmd (Bf_Catalog $objCatalog, $objCatRow, $objLockRow, $boolIsLockScript)
	{
		// Get Lock Params...
		if ($boolIsLockScript) {
			$intLockId = $objCatRow->{Catalog_Model_CatalogData::COL_ID_SCRIPT_LOCK};
		} else {
			$intLockId = $objCatRow->{Catalog_Model_CatalogData::COL_ID_SCRIPT_UNLOCK};
		}

		if (! empty($intLockId)) {
			// Get Lock Script
			$objLockScript = new Qstat_Db_Table_LockManagment();
			$strLockScriptCmd = $objLockScript->prepereCmd($objCatalog, $objCatRow, $objLockRow, $intLockId);
			return $strLockScriptCmd;
		}
		return;

	}

	public static function getOpenLockForCatId ($intCatId)
	{
		$objCatalogData = new Catalog_Model_CatalogData();
		$objCatalogDataSelect = $objCatalogData->select(TRUE)->setIntegrityCheck(FALSE);
		$objCatalogDataSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
			Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . " = " . Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG);
		$objCatalogDataSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED . " = ?", FALSE);
		$objCatalogDataSelect->where(Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG . " = ?", $intCatId);
		$objCatalogDataRow = $objCatalogData->fetchRow($objCatalogDataSelect);

		if ($objCatalogDataRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED}) {

			$objLocks = new Qstat_Db_Table_Lock();
			$objLocksSelect = $objLocks->select(TRUE)->setIntegrityCheck(FALSE);
			$objLocksSelect->joinLeft(User_Model_Db_Users::TBL_NAME,
				User_Model_Db_Users::TBL_NAME . "." . User_Model_Db_Users::COL_ID_USERS . " = " . Qstat_Db_Table_Lock::TBL_NAME . "." . Qstat_Db_Table_Lock::COL_ID_USER);
			$objLocksSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_ID_CATALOG) . "=?", $intCatId);
			$objLocksSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_IS_DELETED) . "=?", FALSE);
			$objLocksSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END) . " IS NULL");

			$objLocksSelect->columns(array('display_name' => new Zend_Db_Expr("CONCAT(" . User_Model_Db_Users::COL_FIRST_NAME . ",' '," . User_Model_Db_Users::COL_LAST_NAME . ")")));

			$objLockRow = $objLocks->fetchRow($objLocksSelect);

			if (! empty($objLockRow)) {
				return $objLockRow->{Qstat_Db_Table_Lock::COL_ID_LOCK};
			}
		}
		return FALSE;
	}

	public static function getLockUser ($intCatalogId)
	{
		$intLockId = Qstat_Db_Table_Lock::getOpenLockForCatId($intCatalogId);
		$objLock = new Qstat_Db_Table_Lock();
		$objLockSelect = $objLock->select(TRUE)->setIntegrityCheck(FALSE);
		$objLockSelect->where(Qstat_Db_Table_Lock::COL_ID_LOCK . " = ?", $intLockId);
		$objLockRow = $objLock->fetchRow($objLockSelect);

		$objUser = new User_Model_Db_Users();
		if ( empty($objLockRow) ) {
			return null;
		}

		$objLockedByRowSet = $objUser->find( $objLockRow->{Qstat_Db_Table_Lock::COL_ID_USER} );
		return $objLockedByRowSet->current();
	}

	public function getCatalogIds($catalogIdsString) {
		$catalogIds = explode(',', $catalogIdsString);
		$catalogIds = array_map( array( $this, '_prepareCatalogIds' ), $catalogIds );
		$catalogIds = array_filter($catalogIds, array( $this, '_filterCatalogIds' ) );
		return array_unique($catalogIds);
	}

	private function _prepareCatalogIds($catalogId) {
		return intval( trim($catalogId) );
	}

	private function _filterCatalogIds($catalogId) {
		return $catalogId > 0;
	}
}
