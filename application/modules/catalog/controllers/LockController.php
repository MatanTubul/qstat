<?php
/**
* LockController
*
* @author
* @version
*/
require_once 'Zend/Controller/Action.php';

class Catalog_LockController extends Zend_Controller_Action
{
	protected $_options;

	public function init () {
		/* Initialize action controller here */
		$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
		$this->_options = $objApplicationOptions->catalog;
	}

	public function indexAction () {
		$objLockModel = new Qstat_Db_Table_Lock();
		$objLockModelSelect = $objLockModel->select(TRUE)->setIntegrityCheck(FALSE);
		$objLockModelSelect->join(Catalog_Model_CatalogData::TBL_NAME, Catalog_Model_CatalogData::TBL_NAME . "." . Catalog_Model_CatalogData::COL_ID_CATALOG . " = " . Qstat_Db_Table_Lock::TBL_NAME . "." . Qstat_Db_Table_Lock::COL_ID_CATALOG);
		$objLockModelSelect->joinLeft(User_Model_Db_Users::TBL_NAME, User_Model_Db_Users::TBL_NAME . "." . User_Model_Db_Users::COL_ID_USERS . " = " . Qstat_Db_Table_Lock::TBL_NAME . "." . Qstat_Db_Table_Lock::COL_ID_USER);

		$objLockModelSelect->reset(Zend_Db_Select::COLUMNS);

		$objLockModelSelect->columns(array(
			Qstat_Db_Table_Lock::COL_ID_LOCK, Qstat_Db_Table_Lock::COL_LOCK_START,
			Qstat_Db_Table_Lock::COL_LOCK_START_SCRIPT_COMMAND,
			Qstat_Db_Table_Lock::COL_LOCK_END,
			Qstat_Db_Table_Lock::COL_LOCK_START_SCRIPT_OUTPUT,
			Qstat_Db_Table_Lock::COL_LOCK_END_SCRIPT_COMMAND,
			Qstat_Db_Table_Lock::COL_LOCK_END_SCRIPT_OUTPUT,
			Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK
			), Qstat_Db_Table_Lock::TBL_NAME);

		$objLockModelSelect->columns(array(Catalog_Model_CatalogData::COL_TITLE), Catalog_Model_CatalogData::TBL_NAME);

		$objLockModelSelect->columns(array('display_name' => new Zend_Db_Expr("CONCAT(" . User_Model_Db_Users::COL_FIRST_NAME . "," . User_Model_Db_Users::COL_LAST_NAME . ")"), User_Model_Db_Users::COL_EXTRA_DATA), User_Model_Db_Users::TBL_NAME);

		$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'lock', 'action' => 'view-lock'), null, false, false);

		$arrOptions = array('caption' => '');

		$arrOptions['sortname'] = Qstat_Db_Table_Lock::COL_LOCK_START;
		$arrOptions['sortorder'] = Ingot_JQuery_JqGrid::SORT_DESC;
		$arrOptions['rowNum'] = 100;
		$arrOptions['shrinkToFit'] = FALSE;

		$objGrid = new Ingot_JQuery_JqGrid('LockActiveGrid', $objLockModelSelect, $arrOptions);
		$objGrid->setOption('ondblClickRow', "function(rowId, iRow, iCol, e){ if(rowId){ document.location.href='" . $strUrl . "/" . Qstat_Db_Table_Lock::COL_ID_LOCK . "/'+rowId; } }");
		$objGrid->setIdCol(Qstat_Db_Table_Lock::COL_ID_LOCK);
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Catalog_Model_CatalogData::COL_TITLE,array("width"=>"90")));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column("display_name", array('useHaving' => true,"width"=>"90")));

		$objSites = new Qstat_Db_Table_Groups();
		$objSitesSelect = $objSites->getPairSelect();
		$arrPairs = $objSites->getAdapter()->fetchPairs($objSitesSelect);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select(new Ingot_JQuery_JqGrid_Column('groups', array("width"=>"70",'useHaving' => true, 'customField' => User_Model_Db_Users::COL_EXTRA_DATA)), array("value" => $arrPairs));
		$objGrid->addColumn(new Qstat_JQuery_JqGrid_Column_Decorator_UserExtra($column, array('values' => $arrPairs)));

		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_Lock::COL_LOCK_START));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_Lock::COL_LOCK_START_SCRIPT_COMMAND));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_Lock::COL_LOCK_START_SCRIPT_OUTPUT));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_Lock::COL_LOCK_END));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_Lock::COL_LOCK_END_SCRIPT_COMMAND));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_Lock::COL_LOCK_END_SCRIPT_OUTPUT));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK));
		$objGridPager = $objGrid->getPager();

		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());

		$this->view->objGrid = $objGrid->render();
	}

	public function indexScheduledAction () {
		$objLockModel = new Qstat_Db_Table_LockScheduled();
		$objLockModelSelect = $objLockModel->select(TRUE)->setIntegrityCheck(FALSE);
		$objLockModelSelect->join(
			Catalog_Model_CatalogData::TBL_NAME,
			Catalog_Model_CatalogData::TBL_NAME . "." . Catalog_Model_CatalogData::COL_ID_CATALOG . " = " . Qstat_Db_Table_LockScheduled::TBL_NAME . "." . Qstat_Db_Table_LockScheduled::COL_ID_CATALOG
		);
		$objLockModelSelect->joinLeft(
			User_Model_Db_Users::TBL_NAME,
			User_Model_Db_Users::TBL_NAME . "." . User_Model_Db_Users::COL_ID_USERS . " = " . Qstat_Db_Table_LockScheduled::TBL_NAME . "." . Qstat_Db_Table_LockScheduled::COL_ID_USER
		);
		$objLockModelSelect->where(Qstat_Db_Table_LockScheduled::TBL_NAME.'.'.Qstat_Db_Table_LockScheduled::COL_IS_DELETED." = ?",FALSE);
		$objLockModelSelect->reset(Zend_Db_Select::COLUMNS);
		$objLockModelSelect->columns(array(Qstat_Db_Table_LockScheduled::COL_ID_LOCK, Qstat_Db_Table_LockScheduled::COL_LOCK_START), Qstat_Db_Table_LockScheduled::TBL_NAME);
		$objLockModelSelect->columns(array(Catalog_Model_CatalogData::COL_TITLE), Catalog_Model_CatalogData::TBL_NAME);
		$objLockModelSelect->columns(array(
			'display_name' => new Zend_Db_Expr("CONCAT(" . User_Model_Db_Users::COL_FIRST_NAME . "," . User_Model_Db_Users::COL_LAST_NAME . ")"),
			User_Model_Db_Users::COL_EXTRA_DATA
			), User_Model_Db_Users::TBL_NAME);

		$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'view-lock'), null, false, false);

		$arrOptions = array('caption' => '');
		$arrOptions['sortname'] = Qstat_Db_Table_LockScheduled::COL_LOCK_START;
		$arrOptions['sortorder'] = Ingot_JQuery_JqGrid::SORT_DESC;
		$arrOptions['rowNum'] = 100;

		$objGrid = new Ingot_JQuery_JqGrid('LockActiveGrid', $objLockModelSelect, $arrOptions);
		$objGrid->setIdCol(Qstat_Db_Table_LockScheduled::COL_ID_LOCK);
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Catalog_Model_CatalogData::COL_TITLE));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column("display_name", array('useHaving' => true)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_LockScheduled::COL_LOCK_START));

		$objGridPager = $objGrid->getPager();
		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());

		$this->view->objGrid = $objGrid->render();
	}

	public function viewLockAction () {
		$objLocks = new Qstat_Db_Table_Lock();
		$intLockId = $this->getRequest()->getParam(Qstat_Db_Table_Lock::COL_ID_LOCK, 0);

		if (! empty($intLockId)) {
			$objLocksRowSet = $objLocks->find($intLockId);
			if ($objLocksRowSet->count() > 0) {
				$objLocksRow = $objLocksRowSet->current();
				$strUrl = $this->view->url(array(
					'module' => 'catalog', 'controller' => 'index', 'action' => 'view', Bf_Catalog_Data_Db_Table_Abstract::COL_ID_CATALOG => $objLocksRow->{Qstat_Db_Table_Lock::COL_ID_CATALOG},"is_view"=>"1"
					), null, false, false);
			} else {
				$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'lock', 'action' => 'index'), null, TRUE, false);
			}
		} else {
			$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'lock', 'action' => 'index'), null, TRUE, false);
		}

		$this->_redirect($strUrl);
	}

	public function editLockFormAction() {
		$intLockId = $this->getRequest()->getParam(Qstat_Db_Table_Lock::COL_ID_LOCK, 0);
		$objLocks = new Qstat_Db_Table_Lock();

		$objLockRowSet = $objLocks->find($intLockId);
		if ( $objLockRowSet->count() > 0 ) {
			$objLock = $objLockRowSet->current();

			$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "view", 'onClick' => "save_lock();", "name" => "LBL_BUTTON_SAVE_LOCK");
			$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "view", 'onClick' => "load_locks();", "name" => "LBL_BUTTON_CANCEL");

			$objForm = new Catalog_Form_LockEdit();
			$objForm->populate($objLock->toArray());

			$this->view->objForm = $objForm;
			$this->view->arrActions = $arrButtons;

			Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		} else {
			$this->_forward('item-locks');
		}
	}

	public function saveAction() {
		if ( $this->getRequest()->isPost() ) {
			$arrData = $this->getRequest()->getParams();

			$objForm = new Catalog_Form_LockEdit();

			if ($objForm->isValid($arrData)) {
				$arrCleanData = $objForm->getValues();

				// Get Lock Line
				$objLock = new Qstat_Db_Table_Lock();
				$objLockRowSet = $objLock->find($arrCleanData[Qstat_Db_Table_Lock::COL_ID_LOCK]);

				if ( $objLockRowSet->count() > 0 ) {
					$objLockRow = $objLockRowSet->current();
					$objLockRow->{Qstat_Db_Table_Lock::COL_ID_USER} = $arrCleanData[Qstat_Db_Table_Lock::COL_ID_USER];
					$objLockRow->{Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK} = $arrCleanData[Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK];

					if ( $objLockRow->save() ) {
						// Save OK
						$arrResponse['error_msg'] = '';
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = '';
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
					} else {
						$arrResponse['error_msg'] = $objForm->getMessages();
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ERROR_LOCK_SAVE');
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
					}
				} else {
					$arrResponse['error_msg'] = $objForm->getMessages();
					$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ERROR_LOCK_SAVE');
					$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
				}
			} else {
				// Save Failed
				$arrResponse['error_msg'] = $objForm->getMessages();
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ERROR_LOCK_SAVE');
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
			}
		}

		$this->view->arrData = $arrResponse;
	}

	public function itemLocksAction () {
		$objLocks = new Qstat_Db_Table_Lock();
		$boolPopup = 1 == $this->getRequest()->getParam('popup', 0);
		$lockParameters = array(
			array(
				'cat_id' => 0,
				Qstat_Db_Table_Lock::COL_ID_LOCK => 0,
			),
		);

		$this->view->isMultipleSelect = false;
		$catalogIds = $objLocks->getCatalogIds( $this->getRequest()->getParam('catalog_ids', '') );
		if ( count($catalogIds) ) {
			// This is Multiple Select.
			$this->view->isMultipleSelect = true;
			$lockParameters = array();

			$objLocksSelect = $objLocks->select()
			->from( Qstat_Db_Table_Lock::TBL_NAME, array( Qstat_Db_Table_Lock::COL_ID_CATALOG, Qstat_Db_Table_Lock::COL_ID_LOCK, ) )
			->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_ID_CATALOG) . " IN (?)", $catalogIds)
			->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_IS_DELETED) . "=?", FALSE)
			->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END) . " IS NULL");
			$objLockRows = $objLocks->getAdapter()->fetchPairs($objLocksSelect);

			foreach ( $catalogIds as $catalogId ) {
				$lockParameters[] = array(
					'cat_id' => $catalogId,
					Qstat_Db_Table_Lock::COL_ID_LOCK => ( empty( $objLockRows[$catalogId] ) ) ? 0 : intval( $objLockRows[$catalogId] ),
				);
			}

			$arrButtons[] = array('module' => 'catalog', 'controller' => 'lock', "action" => "create-lock", 'onClick' => "create_machine_lock();", "name" => "LBL_BUTTON_CREATE_LOCK");
			$arrButtons[] = array('module' => 'catalog', 'controller' => 'lock', "action" => "release-lock", 'onClick' => "release_machine_lock();", "name" => "LBL_BUTTON_RELEASE_LOCK");
		} else {
			$this->view->intCatalogId = intval( $this->getRequest()->getParam(Qstat_Db_Table_Lock::COL_ID_CATALOG, 0) );
			if ( $this->view->intCatalogId ) {
				$objLocksSelect = $objLocks->select(TRUE)->setIntegrityCheck(FALSE)
				->joinLeft(User_Model_Db_Users::TBL_NAME, User_Model_Db_Users::TBL_NAME . "." . User_Model_Db_Users::COL_ID_USERS . " = " . Qstat_Db_Table_Lock::TBL_NAME . "." . Qstat_Db_Table_Lock::COL_ID_USER)
				->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_ID_CATALOG) . "=?", $this->view->intCatalogId)
				->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_IS_DELETED) . "=?", FALSE)
				->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END) . " IS NULL")
				->columns(array(
					'display_name' => new Zend_Db_Expr("CONCAT(" . User_Model_Db_Users::COL_FIRST_NAME . ",' '," . User_Model_Db_Users::COL_LAST_NAME . ")"),
				));
				$objLockRow = $objLocks->fetchRow($objLocksSelect);
			}

			$this->view->lock_details = '';
			if ( empty($objLockRow) ) {
				// There is no Lock now, check if there is sceduled lock.
				$this->view->isLocked = FALSE;
				$objScheduledLocks = new Qstat_Db_Table_LockScheduled();
				$objScheduledLocksSelect = $objScheduledLocks->select(TRUE)->setIntegrityCheck(FALSE);
				$objScheduledLocksSelect->joinLeft(
					User_Model_Db_Users::TBL_NAME, User_Model_Db_Users::TBL_NAME . "." . User_Model_Db_Users::COL_ID_USERS . " = " . Qstat_Db_Table_LockScheduled::TBL_NAME . "." . Qstat_Db_Table_LockScheduled::COL_ID_USER
				);
				$objScheduledLocksSelect->where(Qstat_Db_Table_LockScheduled::TBL_NAME . '.' . Qstat_Db_Table_LockScheduled::COL_ID_CATALOG . " = ?", $this->view->intCatalogId);
				$objScheduledLocksSelect->where(Qstat_Db_Table_LockScheduled::TBL_NAME . '.' . Qstat_Db_Table_LockScheduled::COL_IS_DELETED . " = ?", FALSE);
				$objScheduledLocksSelect->columns(array('display_name' => new Zend_Db_Expr("CONCAT(" . User_Model_Db_Users::COL_FIRST_NAME . ",' '," . User_Model_Db_Users::COL_LAST_NAME . ")")));
				$objScheduledLockRow = $objScheduledLocks->fetchRow($objScheduledLocksSelect);

				if ( empty($objScheduledLockRow) ) {
					$arrButtons[] = array('module' => 'catalog', 'controller' => 'lock', "action" => "create-lock", 'onClick' => "create_machine_lock();", "name" => "LBL_BUTTON_CREATE_LOCK");
					$arrButtons[] = array('module' => 'catalog', 'controller' => 'lock', "action" => "create-lock", 'onClick' => 'callDialog();', "name" => "LBL_BUTTON_CREATE_SCHEDULED_LOCK");

					$this->view->lock_details = $this->view->translate('LBL_NO_ACTIVE_LOCK');
				} else {
					$arrButtons[] = array('module' => 'catalog', 'controller' => 'lock', "action" => "release-lock", 'onClick' => "del_scheduled_lock_dialog();", "name" => "LBL_BUTTON_CREATE_DEL_LOCK");

					$this->view->lock_details = $this->view->translate('LBL_SCHEDULED_LOCK_PRESENT') . "<br/>";
					$this->view->lock_details .= $this->view->translate('LBL_SCHEDULED_LOCK_BY') . " " . $objScheduledLockRow->display_name . "<br/>";

					$objStartDate = DateTime::createFromFormat(Bf_Db_Table::MYSQL_DATETIME, $objScheduledLockRow->{Qstat_Db_Table_LockScheduled::COL_LOCK_START});
					$arrConf = $this->getInvokeArg('bootstrap')->getOptions();
					$this->view->lock_details .= $this->view->translate('LBL_SCHEDULED_LOCK_START') . " " . $objStartDate->format($arrConf['dateformat']['php']['shortdatetime']) . "<br/>";
					$this->view->lock_details .= '<input type="hidden" name="scheduledLockIdName" id="scheduledLockId" value="' . $objScheduledLockRow->{Qstat_Db_Table_LockScheduled::COL_ID_LOCK} . '" />';
				}

				$lockParameters = array(
					array(
						'cat_id' => $this->view->intCatalogId,
						Qstat_Db_Table_Lock::COL_ID_LOCK => 0,
					),
				);
			} else {
				$this->view->isLocked = TRUE;
				$this->view->lock_details = $this->view->lockData($objLockRow);
				$this->view->intLockId = $objLockRow->{Qstat_Db_Table_Lock::COL_ID_LOCK};
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'lock', "action" => "release-lock", 'onClick' => "release_machine_lock();", "name" => "LBL_BUTTON_RELEASE_LOCK");
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'lock', "action" => "edit-lock-form", 'onClick' => "edit_lock();", "name" => "LBL_BUTTON_EDIT_LOCK");
				$lockParameters = array(
					array(
						'cat_id' => $this->view->intCatalogId,
						Qstat_Db_Table_Lock::COL_ID_LOCK => $objLockRow->{Qstat_Db_Table_Lock::COL_ID_LOCK},
					),
				);
			}
		}

		$this->view->lockParameters = json_encode($lockParameters);

		if ( ! $boolPopup) {
			$this->view->arrActions = $arrButtons;
		}

		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
	}

	public function createLockAction() {
		$intCatalogIdRaw = $this->getRequest()->getParam(Qstat_Db_Table_Lock::COL_ID_CATALOG, 0);
		$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
		$errors = '';

		$scheduledUnlockTime = $this->getRequest()->getParam(Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK, '');
		if ( ! empty($scheduledUnlockTime)) {
			try {
				$date = new DateTime( str_replace('/', '.', $scheduledUnlockTime) );
				$scheduledUnlockTime = $date->getTimestamp() - time();
				$scheduledUnlockTime = ( ( $scheduledUnlockTime > 0 ) ? $scheduledUnlockTime : 0 );
			} catch (Exception $e) {
				$scheduledUnlockTime = '';
			}
		}

		if ( empty($intCatalogIdRaw) ) {
			$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
			$this->view->resultLock = $this->view->translate('LBL_API_LOCK_ERROR');
		} else {
			$objCatalog = new Bf_Catalog($this->_options);

			$objUserSessionData = new Zend_Session_Namespace('user');
			$objUserDetails = $objUserSessionData->userDetails;
			$currentUserId = $objUserDetails->{User_Model_Db_Users::COL_ID_USERS};

			$intCatalogIds = (array) json_decode($intCatalogIdRaw);
			foreach ( $intCatalogIds as $details ) {
				$intCatalogId = intval( $details->cat_id );
				// Check if lock exist allready
				$objItemRow = $objCatalog->getCatalogModel()
				->getObjCatalogTable()
				->find($intCatalogId)
				->current();

				if ( $objItemRow == null ) {
					$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
					$this->view->resultLock = $this->view->translate('LBL_API_LOCK_ERROR');
				} else {
					$objLocks = new Qstat_Db_Table_Lock();
					$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
					$defaultUnlockTime = $objApplicationOptions->defaultUnlockTime;

					if ( intval( $objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} ) ) {
						$objLockedByRow = Qstat_Db_Table_Lock::getLockUser ($intCatalogId);

						if ( is_object($objLockedByRow) && $currentUserId === $objLockedByRow->{User_Model_Db_Users::COL_ID_USERS} ) {
							$objLock = new Qstat_Db_Table_Lock();
							$objLockSelect = $objLock
							->select()
							->where( Qstat_Db_Table_Lock::COL_ID_CATALOG.' = '.intval($intCatalogId) )
							->where( Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END) . " IS NULL" );
							$objLockRow = $objLock->fetchRow($objLockSelect);

							if ( empty($scheduledUnlockTime) ) {
								$scheduledUnlockTime = $defaultUnlockTime;
							}
							$objLockRow->{Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK} = date( Bf_Db_Table::MYSQL_DATETIME, $scheduledUnlockTime + time() );
							$objLockRow->save();

							$this->view->resultLock = $this->view->translate('LBL_API_LOCK_STARTED');
						} else {
							$objCatalogData = new Catalog_Model_CatalogData();

							$locked_by = '';
							if ( isset( $objLockedByRow->{User_Model_Db_Users::COL_FIRST_NAME} ) && isset( $objLockedByRow->{User_Model_Db_Users::COL_LAST_NAME} ) ) {
								$locked_by = " by ".$objLockedByRow->{User_Model_Db_Users::COL_FIRST_NAME}." ".$objLockedByRow->{User_Model_Db_Users::COL_LAST_NAME}.PHP_EOL;
							}
							$errors .=
							Ingot_JQuery_JqGrid::RETURN_CODE_ERROR.' '.
							'for Machine '.$objCatalogData->getDeviceName($intCatalogId).'. '.
							$this->view->translate('LBL_API_LOCK_EXIST').$locked_by;
							$this->view->resultLock = $this->view->translate('LBL_API_LOCK_EXIST').$locked_by;
						}
					} else {
						$result = $objLocks->createLock($intCatalogId, $currentUserId, $defaultUnlockTime, $scheduledUnlockTime);

						$objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} = 1;
						$objItemRow->save();

						$this->view->resultLock = $this->view->translate('LBL_API_LOCK_STARTED');
					}
				}
			}
		}

		$this->view->result = empty($errors) ? $this->view->result : $errors;

		if ( empty( $this->view->showNext ) ) {
			Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		} else {
			$this->_forward($this->view->showNext['action'], $this->view->showNext['controller']);
		}
	}

	public function createScheduledLockAction () {
		$intCatalogId = $this->getRequest()->getParam(Qstat_Db_Table_LockScheduled::COL_ID_CATALOG, 0);
		$strStartTime = $this->getRequest()->getParam(Qstat_Db_Table_LockScheduled::COL_LOCK_START, 0);
		$objStartTime = DateTime::createFromFormat('d/m/Y H:i', $strStartTime);

		if ( ! empty($intCatalogId) && ! empty($objStartTime)) {
			// Check if lock exist allready.
			$objCatalog = new Bf_Catalog($this->_options);
			$objItemRow = $objCatalog->getCatalogModel()
			->getObjCatalogTable()
			->find($intCatalogId)
			->current();

			if ( $objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} ) {
				$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
				$this->view->resultLock = $this->view->translate('LBL_API_LOCK_EXIST');
			} else {
				// Check that there is NO sceduled lock.
				$objScheduledLock = new Qstat_Db_Table_LockScheduled();
				$objScheduledLockSelect = $objScheduledLock->select(TRUE);
				$objScheduledLockSelect->where(Qstat_Db_Table_LockScheduled::TBL_NAME . '.' . Qstat_Db_Table_LockScheduled::COL_ID_CATALOG . " = ?", $intCatalogId);
				$objScheduledLockSelect->where(Qstat_Db_Table_LockScheduled::TBL_NAME . '.' . Qstat_Db_Table_LockScheduled::COL_IS_DELETED . " = ?", FALSE);
				$objScheduledLockRow = $objScheduledLock->fetchRow($objScheduledLockSelect);

				if ( empty($objScheduledLockRow) ) {
					$objScheduledLockRow = $objScheduledLock->createRow();
					$objScheduledLockRow->{Qstat_Db_Table_LockScheduled::COL_ID_CATALOG} = $intCatalogId;

					$objUserSessionData = new Zend_Session_Namespace('user');
					$objUserDetails = $objUserSessionData->userDetails;

					$objScheduledLockRow->{Qstat_Db_Table_LockScheduled::COL_ID_USER} = $objUserDetails->{User_Model_Db_Users::COL_ID_USERS};
					$objScheduledLockRow->{Qstat_Db_Table_LockScheduled::COL_LOCK_START} = $objStartTime->format(Bf_Db_Table::MYSQL_DATETIME);

					if ($objScheduledLockRow->save()) {
						$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
						$this->view->resultLock = $this->view->translate('LBL_API_LOCK_STARTED');
					} else {
						$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
						$this->view->resultLock = $this->view->translate('LBL_API_LOCK_ERROR');
					}
				} else {
					$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
					$this->view->resultLock = $this->view->translate('LBL_API_LOCK_ERROR');
				}
			}
		} else {
			$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
			$this->view->resultLock = $this->view->translate('LBL_API_LOCK_ERROR');
		}

		if ( empty($this->view->showNext) ) {
			Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
			return;
		}

		$this->_forward( $this->view->showNext['action'], $this->view->showNext['controller'] );
	}

	public function delScheduledLockAction () {
		$intLockId = $this->getRequest()->getParam(Qstat_Db_Table_LockScheduled::COL_ID_LOCK, 0);

		if (! empty($intLockId)) {

			// Now check that there is sceduled lock with this ID
			$objScheduledLock = new Qstat_Db_Table_LockScheduled();
			$objScheduledLockSelect = $objScheduledLock->select(TRUE);
			$objScheduledLockSelect->where(Qstat_Db_Table_LockScheduled::TBL_NAME . '.' . Qstat_Db_Table_LockScheduled::COL_ID_LOCK . " = ?", $intLockId);

			$objScheduledLockRow = $objScheduledLock->fetchRow($objScheduledLockSelect);

			if (!empty($objScheduledLockRow)) {
				if ($objScheduledLockRow->delete()) {
					$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
					$this->view->resultLock = $this->view->translate('LBL_API_LOCK_STARTED');
				} else {
					$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
					$this->view->resultLock = $this->view->translate('LBL_API_LOCK_ERROR');
				}
			} else {
				$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
				$this->view->resultLock = $this->view->translate('LBL_API_LOCK_ERROR');
			}
		}

		if (! empty($this->view->showNext)) {
			$this->_forward($this->view->showNext['action'], $this->view->showNext['controller']);
		} else {
			Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		}
	}

	public function releaseLockAction () {
		$params = $this->getRequest()->getParam('params', 0);
		$params = (array) json_decode($params);
		$errors = '';

		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;

		$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_OK;

		if ( is_array($params) ) {
			foreach ( $params as $details) {
				$intCatalogId = intval( $details->cat_id );
				$intLockId = intval( $details->{Qstat_Db_Table_Lock::COL_ID_LOCK} );

				if ( $intCatalogId && $intLockId ) {
					$objLocks = new Qstat_Db_Table_Lock();
					$result = $objLocks->releaseLock($intLockId, $intCatalogId, $objUserDetails);

					if ( $result === true ) {
						$objCatalog = new Bf_Catalog($this->_options);
						$objItemRow = $objCatalog->getCatalogModel()
						->getObjCatalogTable()
						->find($intCatalogId)
						->current();
						$objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} = 0;
						$objItemRow->save();

						$this->view->resultLock = $this->view->translate('LBL_API_UNLOCK_OK');
					} else {
						if ( ! empty($result) ) {
							$errors .= $result;
						}
					}
				} else {
					$this->view->result = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
					$this->view->resultLock = $this->view->translate('LBL_API_UNLOCK_ERROR');
				}
			}
		}

		$this->view->result = empty($errors) ? $this->view->result : $errors;

		if ( empty( $this->view->showNext ) ) {
			Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		} else {
			$this->_forward($this->view->showNext['action'], $this->view->showNext['controller']);
		}
	}

	public function eventsAction () {
		$arrOptions = array('caption' => '');
		//$arrOptions['sortorder'] = Ingot_JQuery_JqGrid::SORT_ASC;
		$arrOptions['rowNum'] = 100;

		$objGrid = new Ingot_JQuery_JqGrid('LockEvents', "Qstat_Db_Table_LockEvents", $arrOptions);
		$objGrid->setIdCol(Qstat_Db_Table_LockEvents::COL_ID_LOCK_EVENTS);
		$objGrid->setLocalEdit();

		//Group Column
		$objGroups = new Qstat_Db_Table_Groups();
		$objGroupsSelect = $objGroups->getPairSelect();
		$arrGroupsPairs = $objGroups->getAdapter()->fetchPairs($objGroupsSelect);

		$arrGroupsPairs[0] = '';
		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($objGrid, $arrGroupsPairs,
			array('index' => Catalog_Model_CatalogData::COL_ID_GROUPS, 'useHaving' => true,"width"=>"100"),false);

		$arrGroupsPairs=array();
		for ($i=0;$i<24;$i++){
			$arrGroupsPairs[str_pad($i, 2,0,STR_PAD_LEFT)]=str_pad($i, 2,0,STR_PAD_LEFT);
		}

		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($objGrid, $arrGroupsPairs,
			array('index' => Qstat_Db_Table_LockEvents::COL_TIME_UNLOCK, "width"=>"100"),true);

		$objGridPager = $objGrid->getPager();
		$objGridPager->setDefaultAdd();
		$objGridPager->setDefaultEdit();
		$objGridPager->setDefaultDel();

		$arrToolbarFilter = array('triggerReload' => true);
		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter($arrToolbarFilter));

		$this->view->objGrid = $objGrid->render();
	}
}
