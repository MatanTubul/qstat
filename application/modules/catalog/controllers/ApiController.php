<?php

/**
* ApiController
*
* @author
* @version
*/

require_once 'Zend/Controller/Action.php';

class Catalog_ApiController extends Zend_Controller_Action
{
	const KEY_FLAG = 'flag';
	CONST KEY_DATA = 'data';
	CONST VALUE_FLAG_PASSED = 'true';
	CONST VALUE_FLAG_FAILED = 'false';

	/**
	* The default action - show the home page
	*/
	public function indexAction ()
	{
		// TODO Auto-generated ApiController::indexAction() default action
	}

	public function helpAction ()
	{
		$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA => $this->view->translate('LBL_API_TEXT_HELP'));
	}

	public function setLockAction ()
	{
		$arrData = array();
		$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;

		// First check, if it needs password.
		$username = $this->getRequest()->getParam(User_Model_Db_Users::COL_LOGIN);

		$objUserDb = new User_Model_Db_Users();
		$objUserDbSelect = $objUserDb->select(TRUE);
		$objUserDbSelect->where(User_Model_Db_Users::COL_LOGIN . " = ?", $username);
		$objUserDbRow = $objUserDb->fetchRow($objUserDbSelect);

		if ( ! empty($objUserDbRow) && $objUserDbRow->{User_Model_Db_Users::COL_IS_PERMIT_LOCK} === "yes" ) {
			$boolAuth = $this->authenticateByName();
		} else {
			$boolAuth = $this->authenticate();
		}

		if ($boolAuth) {
			// The User is Valid.
			$arrData[self::KEY_FLAG] = self::VALUE_FLAG_PASSED;
			$strServerName = $this->getRequest()->getParam('ServerName');

			if (empty($strServerName)) {
				$strServerName = $this->getRequest()->getParam('Title');
			}
			if (empty($strServerName)) {
				$strServerName = $this->getRequest()->getParam('title');
			}

			$strIP = $this->getRequest()->getParam('IP_Address');
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('IPAddress');
			}
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('IP');
			}
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('Ip');
			}
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('ip');
			}

			if ( ! empty($strServerName) || ! empty($strIP) ) {
				// Get Catalog Id From Name
				if (!empty($strServerName)){
					$objCatalogDataRow = $this->getCatlIdByName($strServerName);
				}

				if (!empty($strIP)){
					$objCatalogDataRow = $this->getCatlIdByIp($strIP);
				}

				if ( ! empty($objCatalogDataRow) ) {
					$lockParameters = array(
						array(
							'cat_id' => $objCatalogDataRow->{Catalog_Model_CatalogData::COL_ID_CATALOG},
						),
					);
					$this->getRequest()->setParam( Qstat_Db_Table_Lock::COL_ID_CATALOG, json_encode($lockParameters) );

					if ( User_Model_Acl::getInstance()->checkPermissions('catalog', 'lock', 'create-lock') ) {
						$this->_forward('create-lock', 'lock');
					} else {
						$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
						$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TXT_NOT_AUTH');
					}
				} else {
					$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
					$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TEXT_SERVER_NAME_NOT_FOUND');
				}
			} else {
				$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
				$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TEXT_NO_SERVER_NAME');
			}
		} else {
			$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
			$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TXT_NOT_AUTH');
		}

		$this->view->showNext = array( 'module' => 'catalog', 'controller' => 'api', 'action' => 'lock-reply', );
		$this->view->arrData = $arrData;
	}

	public function lockReplyAction ()
	{
		$this->view->arrData[self::KEY_DATA] = $this->view->resultLock;
	}

	public function listReplyAction ()
	{
		$objGrid = $this->view->objGrid;
		$arrColumns = $objGrid->getColumns();
		$strParams = $this->getRequest()->getParam("Groups");
		$arrColumnNames = array();

		$intSearch = 0;

		try {
			$arrColumnCodeNames=array();
			foreach ($arrColumns as $objColumn) {

				$boolHidden = $objColumn->getOption('hidden');

				if (!empty($strField)&&$objColumn->getName()!=$strField){
					$boolHidden=true;
				}

				if ($boolHidden) {
					$objGrid->removeColumn($objColumn->getName());
				} else {

					$strLabel = $objColumn->getOption('label');
					$arrColumnNames[] = $strLabel;
					$arrColumnCode = $objColumn->getName();
					$arrColumnCodeNames[$objColumn->getName()]=$strLabel;

					$arrValues = array();
					$objDecorator = clone $objColumn;
					while ($objDecorator instanceof Ingot_JQuery_JqGrid_Column_Decorator_Abstract) {
						if ($objDecorator instanceof Ingot_JQuery_JqGrid_Column_Decorator_Search_Select) {
							$arrValues = $objDecorator->getValues();
							break;
						}
						$objDecorator = $objDecorator->getColumn();
					}

					//echo $objColumn->getWidth();
					$strParamData = $this->getRequest()->getParam($arrColumnCode);
					if (empty($strParamData)) {
						$strParamData = $this->getRequest()->getParam($strLabel);
					}
					//                    Zend_Debug::dump($strParamData, $arrColumnCode);
					if (! empty($strParamData)) {
						//                        Zend_Debug::dump($arrValues, $arrColumnCode);
						$strParam = "";
						if (! empty($arrValues)) {
							$strParam = array_search($strParamData, $arrValues);
						}

						if (empty($strParam)) {
							$strParam = $strParamData;
						}
						//                        Zend_Debug::dump($strParam, $arrColumnCode);
						if (! empty($strParam)) {
							$arrSearchParams[$intSearch] = '';
							if ($objColumn->isSetOption('index')) {
								$arrSearchParams[$intSearch]['field'] = $objColumn->getOption('index');
							} else {
								$arrSearchParams[$intSearch]['field'] = $arrColumnCode;
							}
							$arrSearchParams[$intSearch]['op'] = 'bw';
							$arrSearchParams[$intSearch]['data'] = $strParam;

							$intSearch ++;
						}
					}
				}
			}
			//
			//        Zend_Debug::dump($arrSearchParams);
			//        exit();

			$objGrid->removeColumn(Bf_Catalog_Models_Catalog::COL_PARENT_FLAG . 'icon');

			if ($intSearch) {
				$this->getRequest()->setParam('grid', 'Catalog');
				$this->getRequest()->setParam('_search', 'true');


				$arrSearchFilters['groupOp'] = 'AND';
				$arrSearchFilters['rules'] = $arrSearchParams;

				$this->getRequest()->setParam('filters', Zend_Json::encode($arrSearchFilters));
			}

			$strResponce = $objGrid->response($this->getRequest());
			$arrGridData = Zend_Json::decode($strResponce);
			$arrRows = $arrGridData['rows'];

			$intRowsNum =  count($arrRows);
			//if there is more than 2 result sort it by title
			if ($intRowsNum>2){

				//sort by title
				if (empty($strParams)){
					$this->getRequest()->setParam("sidx", "title");
				}

				$this->getRequest()->setParam("sord", "asc");
				$strResponce = $objGrid->response($this->getRequest());
				$arrGridData = Zend_Json::decode($strResponce);
				$arrRows = $arrGridData['rows'];
			}

			//delete server name
			foreach ($arrRows as $intCountRow => $arrRow){
				if ($arrRow['id']==5){
					unset($arrRows[$intCountRow]);
				}
			}

		} catch (Exception $objE) {
			Zend_Debug::dump($objE);
		}


		if (empty($strParams)){
			unset($arrRows[0]);
		}
		unset($arrColumnNames[0]);


		$arrColumnNames = array_values($arrColumnNames);
		$arrFinaleRows = array();
		$arrFinaleRows[] = $arrColumnNames;

		foreach ($arrRows as $arrRow) {

			$intLockedFlag = end($arrRow['cell']);
			$strLocked = "";
			if (!empty($intLockedFlag)){
				$objLockedByRow = Qstat_Db_Table_Lock::getLockUser ($arrRow['id']);
				//            Zend_Debug::dump($objLockedByRow);
				if (!empty($objLockedByRow)){
					$strLocked = "Locked by ".$objLockedByRow->{User_Model_Db_Users::COL_FIRST_NAME}." ".$objLockedByRow->{User_Model_Db_Users::COL_LAST_NAME}." User: ".$objLockedByRow->{User_Model_Db_Users::COL_LOGIN};
				}else{
					$strLocked="";
				}
			}

			$arrRow['cell'][count($arrRow['cell'])-1] = $strLocked;


			//:TO DO understand why it brings rows with onother groups name
			if (!empty($strParams)&&$arrFinaleRows[0][2]=="Groups"){
				if ($arrRow['cell'][2]!=$strParams){
					//filtering by group name
					continue;
				}
			}

			//Zend_Debug::dump($arrRow);
			$arrFinaleRows[] = $arrRow['cell'];

		}

		//check if field is required
		$strField = $this->getRequest()->getParam('field', "all");
		if ($strField=="all"){
			$strField="";
		}

		if (!empty($strField)&&(!in_array($strField, array_keys($arrColumnCodeNames))||$strField=="list")){
			$arrFinaleRows=$this->view->translate("Please choose one field from the list").":\n";
			$arrFinaleRows.="all\n";
			foreach ($arrColumnCodeNames as $strColumnCodeName => $strValue){

				if ($strColumnCodeName=="isParenticon"){
					continue;
				}

				$arrFinaleRows.=$strColumnCodeName."\n";
			}
			$this->view->arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
			$this->view->arrData[self::KEY_DATA] = $arrFinaleRows;
			return;
		}

		if (!empty($strField)){
			$arrHeader = $arrFinaleRows[0];
			foreach ($arrHeader as $intIndex => $strValue){
				if ($arrColumnCodeNames[$strField]==$strValue){
					break;
				}
			}

			$arrFinelFilteredRows=array();
			$arrFinelFilteredRows[]=array($arrFinaleRows[0][$intIndex]);
			unset($arrFinaleRows[0]);
			foreach ($arrFinaleRows as $arrResult){
				$arrFinelFilteredRows[]=array($arrResult[$intIndex]);
			}
			$arrFinaleRows=$arrFinelFilteredRows;
		}

		$arrTemp=array();

		$this->view->arrData[self::KEY_DATA] = $arrFinaleRows;
	}

	public function setUnlockAction ()
	{
		$arrData = array();
		$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;

		// First check, if it needs password.
		$username = $this->getRequest()->getParam(User_Model_Db_Users::COL_LOGIN);

		$objUserDb = new User_Model_Db_Users();
		$objUserDbSelect = $objUserDb->select(TRUE);
		$objUserDbSelect->where(User_Model_Db_Users::COL_LOGIN . " = ?", $username);
		$objUserDbRow = $objUserDb->fetchRow($objUserDbSelect);

		if ( ! empty($objUserDbRow) && $objUserDbRow->{User_Model_Db_Users::COL_IS_PERMIT_LOCK} === "yes" ) {
			$boolAuth = $this->authenticateByName();
		} else {
			$boolAuth = $this->authenticate();
		}

		if ($boolAuth) {
			// The User is Valid.
			$arrData[self::KEY_FLAG] = self::VALUE_FLAG_PASSED;
			$strServerName = $this->getRequest()->getParam('ServerName');

			// Unlock all by current user.
			if ( ! empty($strServerName) && ( $strServerName === "all" ) ) {
				$session = new Zend_Session_Namespace("user");
				$userId=$session->userDetails->id_users;
				$objLocksRowSet = $this->getLocksByUser($userId);
				// Initialize action controller here.
				$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
				$objCatalog = new Bf_Catalog($objApplicationOptions->catalog);

				$strResult = $this->view->translate('Start Unlock')."\n";
				foreach ($objLocksRowSet as $objLocksRow){
					if ( empty( $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_END} ) ) {
						$strResult.= "----- ".$objLocksRow->{Catalog_Model_CatalogData::COL_TITLE}." ". $this->view->translate('is unlocked')."\n" ;
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
				$strResult.= $this->view->translate('End');
				$arrData[self::KEY_DATA] = $strResult;
				$this->view->showNext = array('module' => 'catalog', 'controller' => 'api', 'action' => 'lock-reply');
				$this->view->arrData = $arrData;
				return;
			}

			if (empty($strServerName)) {
				$strServerName = $this->getRequest()->getParam('Title');
			}
			if (empty($strServerName)) {
				$strServerName = $this->getRequest()->getParam('title');
			}
			$strIP = $this->getRequest()->getParam('IP_Address');
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('IPAddress');
			}
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('IP');
			}
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('Ip');
			}
			if (empty($strIP)) {
				$strIP = $this->getRequest()->getParam('ip');
			}

			if ( ! empty($strServerName) || ! empty($strIP) ) {
				// Get Catalog Id From Name
				if (!empty($strServerName)){
					$objCatalogDataRow = $this->getCatlIdByName($strServerName);
				}

				if (!empty($strIP)){
					$objCatalogDataRow = $this->getCatlIdByIp($strIP);
				}

				if ( ! empty($objCatalogDataRow) ) {
					$intLockId = $this->getOpenLockForCatId($objCatalogDataRow->{Catalog_Model_CatalogData::COL_ID_CATALOG});

					if ( ! empty($intLockId) ) {
						$unlockParams =	array(
							array(
								'cat_id' => $objCatalogDataRow->{Catalog_Model_CatalogData::COL_ID_CATALOG},
								Qstat_Db_Table_Lock::COL_ID_LOCK =>	$intLockId,
							),
						);

						$this->getRequest()->setParam( 'params', json_encode( $unlockParams) );

						if ( User_Model_Acl::getInstance()->checkPermissions('catalog', 'lock', 'release-lock') ) {
							$this->_forward('release-lock', 'lock');
						} else {
							$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
							$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TXT_NOT_AUTH');
						}
					} else {
						$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
						$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TEXT_LOCK_NOT_FOUND');
					}
				} else {
					$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
					$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TEXT_SERVER_NAME_NOT_FOUND');
				}
			} else {
				$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
				$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TEXT_NO_SERVER_NAME');
			}
		} else {
			$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
			$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TXT_NOT_AUTH');
		}

		$this->view->showNext = array('module' => 'catalog', 'controller' => 'api', 'action' => 'lock-reply');
		$this->view->arrData = $arrData;
	}

	protected function authenticate()
	{
		$auth = Zend_Auth::getInstance();

		$username = $this->getRequest()->getParam(User_Model_Db_Users::COL_LOGIN);
		$password = $this->getRequest()->getParam('password');

		if (! empty($username) ) {
			// Remove it from Array...
			$result = User_Model_User::makeLogin($username, $password);
			//            Zend_Debug::dump($result);
			if ($result->isValid()) {
				// check the permissions to make the action (for set/unset)
				return true;
			}
		}
		return false;
	}

	protected function authenticateByName (){
		$auth = Zend_Auth::getInstance();

		$username = $this->getRequest()->getParam(User_Model_Db_Users::COL_LOGIN);

		if (! empty($username) ) {
			// Remove it from Array...
			$result = User_Model_User::makeLoginByName($username);
			//            Zend_Debug::dump($result);
			if ($result->isValid()) {
				// check the permissions to make the action (for set/unset)
				return true;
			}
		}
		return false;
	}

	protected function getCatlIdByName ($strServerName)
	{
		$objCatalogData = new Catalog_Model_CatalogData();
		$objCatalogDataSelect = $objCatalogData->select(TRUE)->setIntegrityCheck(FALSE);
		$objCatalogDataSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
			Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . " = " . Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG);
		$objCatalogDataSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED . " = ?", FALSE);
		$objCatalogDataSelect->where(Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_TITLE . " = ?", $strServerName);
		$objCatalogDataRow = $objCatalogData->fetchRow($objCatalogDataSelect);

		return $objCatalogDataRow;
	}

	protected function getCatlIdByIp ($strIp){

		$objCatalogData = new Catalog_Model_CatalogData();
		$objCatalogDataSelect = $objCatalogData->select(TRUE)->setIntegrityCheck(FALSE);
		$objCatalogDataSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
			Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . " = " . Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG);
		$objCatalogDataSelect->join(Bf_Eav_Db_EntitiesValues::TBL_NAME,
			Bf_Eav_Db_EntitiesValues::TBL_NAME.'.'.Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES." = ".Bf_Catalog_Models_Db_Catalog::TBL_NAME.'.'.Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES." AND ".Bf_Eav_Db_EntitiesValues::TBL_NAME.'.'.Bf_Eav_Db_EntitiesValues::COL_ID_ATTR." = 10");
		$objCatalogDataSelect->join(Bf_Eav_Db_Values_Varchar::TBL_NAME, Bf_Eav_Db_Values_Varchar::TBL_NAME.'.'.Bf_Eav_Db_Values_Varchar::COL_ID_VALUES." = ".Bf_Eav_Db_EntitiesValues::TBL_NAME.'.'.Bf_Eav_Db_EntitiesValues::COL_ID_VALUES);

		$objCatalogDataSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED . " = ?", FALSE);
		$objCatalogDataSelect->where(Bf_Eav_Db_Values_Varchar::TBL_NAME . '.' . Bf_Eav_Db_Values_Varchar::COL_VALUE . " = ?", $strIp);
		$objCatalogDataRow = $objCatalogData->fetchRow($objCatalogDataSelect);

		return $objCatalogDataRow;
	}

	protected function getLocksByUser ($intUserId)
	{
		$objLocks = new Qstat_Db_Table_Lock();
		$objLocksSelect = $objLocks->select(TRUE)->setIntegrityCheck(FALSE);
		$objLocksSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
			Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . " = " . Qstat_Db_Table_Lock::TBL_NAME . '.' . Qstat_Db_Table_Lock::COL_ID_CATALOG. " AND ".Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED."='' AND ".Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED."=".true );
		$objLocksSelect->join(Catalog_Model_CatalogData::TBL_NAME ,Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG . " = ".Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG);;
		$objLocksSelect->where(Qstat_Db_Table_Lock::TBL_NAME . '.' . Qstat_Db_Table_Lock::COL_IS_DELETED . " = ?", FALSE);
		$objLocksSelect->where(Qstat_Db_Table_Lock::TBL_NAME . '.' . Qstat_Db_Table_Lock::COL_ID_USER . " = ?", $intUserId);

		$objLocksRowSet = $objLocks->fetchAll($objLocksSelect);

		return $objLocksRowSet;
	}

	protected function getOpenLockForCatId ($intCatId)
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

	public function listAction ()
	{
		$arrData = array();

		if ($this->authenticateByName ()) {

			$arrData[self::KEY_FLAG] = self::VALUE_FLAG_PASSED;

			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

			$this->getRequest()->setParam('grid', 'Catalog');

			if (! ($this->getRequest()->getParam('rows', 0))) {
				$this->getRequest()->setParam('rows', '101');
			} else {
				$this->getRequest()->setParam('rows', $this->getRequest()
					->getParam('rows') + 1);
			}

			//sort by title
			//          $this->getRequest()->setParam("sidx", "title");
			//          $this->getRequest()->setParam("sord", "asc");

			$this->getRequest()->setParam('CliList', true);

			$this->_forward('index', 'index');

		} else {
			$arrData[self::KEY_FLAG] = self::VALUE_FLAG_FAILED;
			$arrData[self::KEY_DATA] = $this->view->translate('LBL_API_TXT_NOT_AUTH');
		}


		$this->view->showNext = array('module' => 'catalog', 'controller' => 'api', 'action' => 'list-reply');

		$this->view->arrData = $arrData;
	}

	public function locksAction ()
	{
		$this->_forward('list');
	}


	public function qfreeAction (){

		//	    	$userId=5;
		//	    	$objLocksRowSet  =  $this->getLocksByUser(5);

		/* Initialize action controller here */
		//			$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
		//	    	$objCatalog = new Bf_Catalog($objApplicationOptions->catalog);
		//
		//	   		$strResult=$this->view->translate('Start Unlock')."\n";
		//	        foreach ($objLocksRowSet as $objLocksRow){
		//			            if (empty($objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_END})) {
		//			            	$strResult.= "----- ".$objLocksRow->{Catalog_Model_CatalogData::COL_TITLE}." ". $this->view->translate('is unlocked')."\n" ;
		//			                $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_END} = date(Qstat_Db_Table_Lock::MYSQL_DATETIME);
		//			                $objLocksRow->save();
		//
		//							$objItemRow = $objCatalog->getCatalogModel()
		//								->getObjCatalogTable()
		//								->find($objLocksRow->{Catalog_Model_CatalogData::COL_ID_CATALOG})
		//								->current();
		//							$objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} = 0;
		//							$objItemRow->save();
		//			            }
		//	         }
		$strResult.= $this->view->translate('End');
		$arrData[self::KEY_DATA] = $strResult;
		$this->view->arrData = $arrData;
	}

	public function updateAction ()
	{

		if ($this->authenticate()) {

			$session = new Zend_Session_Namespace("user");
			$objUserDetails = $session->userDetails;
			$arrExtraData = unserialize($objUserDetails->{User_Model_Db_Users::COL_EXTRA_DATA});

			$strTitle = $this->getRequest()->getParam('title');
			$strIp = $this->getRequest()->getParam('ip');
			$strColumn = $this->getRequest()->getParam('column');
			$strValue = $this->getRequest()->getParam('value');

			if (empty($strColumn)||empty($strValue)){
				$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA =>'Error input parameters, please insert parameters title [server_name] column "[column name]" value "[new value]"' );
				return;
			}

			$intCatalogId=0;
			if (!empty($strTitle)){
				$objCatalogData = new Catalog_Model_CatalogData();
				$objCatalogDataSelect = $objCatalogData->select(TRUE)->setIntegrityCheck(FALSE);
				$objCatalogDataSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
					Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . " = " . Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG);
				$objCatalogDataSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED . " = ?", FALSE);
				$objCatalogDataSelect->where(Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_TITLE . " = ?", $strTitle);
				$objCatalogDataRow = $objCatalogData->fetchRow($objCatalogDataSelect);

				$intIdEntityData=$objCatalogDataRow->{Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES};

				if (empty($objCatalogDataRow->{Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG})) {
					$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA =>$strTitle." is not exist in the system" );
					return;
				}

			}else if (!empty($strIp)){

				$intAtributeId = 10;
				$objEntValues =  new Bf_Eav_Db_EntitiesValues();
				$objEntValuesSelect= $objEntValues->select(true)->setIntegrityCheck(false);
				$objEntValuesSelect->join(Bf_Eav_Db_Values_Varchar::TBL_NAME,Bf_Eav_Db_Values_Varchar::TBL_NAME.".id_values=".Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_VALUES);
				$objEntValuesSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES."=".Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES);
				$objEntValuesSelect->where(Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_IS_DELETED."=?",false);
				$objEntValuesSelect->where(Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_VALUE."=?",$strIp);
				$objEntValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_IS_DELETED."=?",false);
				$objEntValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ATTR."=?",$intAtributeId);
				$objEntValuesSelect->reset(Zend_Db_Select::COLUMNS);
				$objEntValuesSelect->columns(array(Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES =>Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES ));
				$objEntValuesSelect->columns(array(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG =>Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG ));
				$objEntValuesSelect->columns(array(Bf_Eav_Db_Values_Varchar::COL_VALUE =>Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_VALUE ));
				$objEntValuesRow=$objEntValues->fetchRow($objEntValuesSelect);

				if (empty($objEntValuesRow)){
					$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA =>$strIp." is not exist in the system" );
					return;
				}
				$intIdEntityData=$objEntValuesRow->{Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES};
			}else{
				$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA =>'Error input parameters, please insert parameters title [server_name] column "[column name]" value "[new value]"' );
				return;

			}

			$objGrop =  new Qstat_Db_Table_Groups();
			$objGropSelect= $objGrop->select();
			$objGropSelect->where(Qstat_Db_Table_Groups::TBL_NAME.".".Qstat_Db_Table_Groups::COL_ID_GROUPS."=?",$arrExtraData['groups']);
			$objGropSelect->where(Qstat_Db_Table_Groups::TBL_NAME.".".Qstat_Db_Table_Groups::COL_IS_DELETED."=?",false);
			$objGropRow = $objGrop->fetchRow($objGropSelect);

			$arrGroupData=unserialize($objGropRow->{Qstat_Db_Table_Groups::COL_CUSTOM_COLUMNS});

			$objEntAttributesValues = new Bf_Eav_Db_Attributes();
			$objEntAttributesValuesSelect= $objEntAttributesValues->select();
			$objEntAttributesValuesSelect->where(Bf_Eav_Db_Attributes::COL_ID_ATTR." in (?)",$arrGroupData);
			$objEntAttributesValuesSelect->where(Bf_Eav_Db_Attributes::COL_IS_USER_CAN_EDIT." = ?",true);
			$objEntAttributesValuesRowSet = $objEntAttributesValues->fetchAll($objEntAttributesValuesSelect);

			$arrValues=array();
			$strAttributesName="";
			foreach ($objEntAttributesValuesRowSet as $objEntAttributesValuesRow){
				if ($objEntAttributesValuesRow->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}==$strColumn){
					$arrValues=$objEntAttributesValuesRow->toArray();
					break;
				}
				$strAttributesName.=$objEntAttributesValuesRow->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}.PHP_EOL;
			}

			if (empty($arrValues)){
				$strErrorMassage="Column Name is error, list of columns: ".PHP_EOL;
				$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA =>$strErrorMassage.PHP_EOL.$strAttributesName );
				return;
			}

			$objValueTable = Bf_Eav_Value::factory($arrValues[Bf_Eav_Db_Attributes::COL_VALUE_TYPE]);
			$strTableName=$objValueTable::VALUES_DB_CLASS;

			$objEntValAtr =  new Bf_Eav_Db_EntitiesValues();
			$objEntValAtrSelect = $objEntValAtr->select();
			$objEntValAtrSelect-> where(Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES."=?",$intIdEntityData);
			$objEntValAtrSelect-> where(Bf_Eav_Db_EntitiesValues::COL_ID_ATTR."=?",$arrValues[Bf_Eav_Db_Attributes::COL_ID_ATTR]);
			$objEntValAtrRow= $objEntValAtr->fetchRow($objEntValAtrSelect);

			if (empty($objEntValAtrRow)){
				$objEntValAtrRow=$objEntValAtr->createRow();
				$objEntValAtrRow->{Bf_Eav_Db_EntitiesValues::COL_ID_ATTR}=$arrValues[Bf_Eav_Db_Attributes::COL_ID_ATTR];
				$objEntValAtrRow->{Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES}=$intIdEntityData;
				$objEntValAtrRow->{Bf_Eav_Db_EntitiesValues::COL_ID_LANGUAGES}=0;
			}

			$objValue=new $strTableName();
			$objValueSelect = $objValue->select();
			$objValueSelect->where(Bf_Eav_Db_Values_Abstract::COL_VALUE."= ?",$strValue);
			$objValueRow=$objValue->fetchRow($objValueSelect);

			if (empty($objValueRow)){
				$objValueRow= $objValue->createRow();
				$objValueRow->{Bf_Eav_Db_Values_Abstract::COL_VALUE}=$strValue;
				$intNewValue= $objValueRow->save();
				$objEntValAtrRow->{Bf_Eav_Db_EntitiesValues::COL_ID_VALUES}=$intNewValue;
			}else{
				$objEntValAtrRow->{Bf_Eav_Db_EntitiesValues::COL_ID_VALUES}=$objValueRow->{Bf_Eav_Db_Values_Abstract::COL_ID_VALUES};
			}

			$objEntValAtrRow->save();
			$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA =>"Successful Updated" );
		}else{
			$this->view->arrData = array(self::KEY_FLAG => self::VALUE_FLAG_PASSED, self::KEY_DATA =>"Wrong Username or Password" );
		}
	}
}
