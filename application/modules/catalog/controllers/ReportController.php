<?php
/**
* ReportController
*
* @author BelleRon
* @version 1.0.0
*/
require_once 'Zend/Controller/Action.php';
class Catalog_ReportController extends Zend_Controller_Action
{
	protected $_options;

	public function init ()
	{
		/* Initialize action controller here */
		$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
		$this->_options = $objApplicationOptions->catalog;
	}

	/**
	* The default action - show the home page
	*/
	public function indexAction ()
	{
		// TODO Auto-generated ReportController::indexAction() default action
	}

	public function idleLocksAction() {
		$arrConf = $this->getInvokeArg('bootstrap')->getOptions();

		$objCatalog = new Bf_Catalog($this->_options);
		$this->view->isReport = true;

		$objForm = new Catalog_Form_IdleLocksFilter(array(),$this->getInvokeArg('bootstrap')->getOptions());
		$objForm->populate($this->getRequest()->getParams());

		$arrValues = $objForm->getValues();

		$objFromDate = DateTime::createFromFormat($arrConf['dateformat']['php']['shortdate2'],$arrValues['fromDate']);
		$objToDate = DateTime::createFromFormat($arrConf['dateformat']['php']['shortdate2'],$arrValues['toDate']);
		$objToDate->add(new DateInterval('P1D')); //Fix end of the day :)

		//TODO populate form
		$this->view->filterForm = $objForm;

		$objLocks = new Qstat_Db_Table_Lock();

		$objSelect = $objLocks->select(TRUE)->setIntegrityCheck(FALSE);
		$objSelect->reset(Zend_Db_Select::COLUMNS);
		$objSelect->columns(Qstat_Db_Table_Lock::COL_ID_CATALOG);
		$objSelect->distinct();

		$objTmpSelect = $objLocks->select();
		$objTmpSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END)." >= ?",$objFromDate->format($arrConf['dateformat']['php']['mysqldate']) );
		$objTmpSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END)." <= ?",$objToDate->format($arrConf['dateformat']['php']['mysqldate']) );

		$objTmpSelect3 = $objLocks->select();
		$objTmpSelect3->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_START)." >= ?",$objFromDate->format($arrConf['dateformat']['php']['mysqldate']) );
		$objTmpSelect3->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_START)." <= ?",$objToDate->format($arrConf['dateformat']['php']['mysqldate']) );

		$objTmpSelect2 = $objLocks->select();
		$objTmpSelect2->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_START)." < ?",$objFromDate->format($arrConf['dateformat']['php']['mysqldate']) );
		$objTmpSelect2->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END).' IS NULL');

		$objSelect->orWhere(new Zend_Db_Expr(implode(' ',$objTmpSelect->getPart(Zend_Db_Select::WHERE))));
		$objSelect->orWhere(new Zend_Db_Expr(implode(' ',$objTmpSelect2->getPart(Zend_Db_Select::WHERE))));
		$objSelect->orWhere(new Zend_Db_Expr(implode(' ',$objTmpSelect3->getPart(Zend_Db_Select::WHERE))));

		$intParent = (int) $this->_options->report->idleLocks->rootParent;

		$objCatalogSelect = $objCatalog->getItems($intParent,TRUE,FALSE,TRUE, TRUE);
		$strWhere = Bf_Catalog_Models_Db_Catalog::getColumnName(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG).' NOT IN ('.$objSelect->assemble().')';
		// $strWhere = Bf_Catalog_Models_Db_Catalog::getColumnName(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG).' NOT IN (246,15)';
		$objCatalogSelect->where($strWhere);

		$this->view->intParent = $intParent;
		$this->view->reportSelect = $objCatalogSelect;
		$this->view->extraJsCode = "function exportLocks() {
		document.location.href = \"".$this->view->url(array('module' => 'catalog', 'controller' => 'report', 'action' => 'idle-locks','export'=>1), null, false, false)."\";
		}";

		if ($this->getRequest()->getParam('export',0)) {
			$this->_forward('export','index','catalog');
		} else {
			$this->_forward('index','index','catalog');
		}
	}

	public function fileImportLogAction() {
		$arrFilesName = glob(APPLICATION_PATH . '/../files/tmp/*.*');
		$arrFilesData=array();
		foreach ($arrFilesName as $strFile){
			$arrFileExp = explode('.',basename($strFile));
			if (!empty($arrFileExp[1])&&$arrFileExp[1]=='log'){
				continue;
			}
			$arrTemp['datetime']=fileatime($strFile);
			$arrTemp['name']=basename($strFile);
			$arrTemp['path']=$strFile;
			$arrTemp['dir_path']=dirname($strFile);
			$arrFilesData[]=$arrTemp;
		}

		$this->view->arrConf=$this->getInvokeArg('bootstrap')->getOptions();
		$this->view->arrFiles=$arrFilesData;
	}

	public function delfileAction(){

		$strFileName = $this->getRequest()->getParam('filename');
		$strType = $this->getRequest()->getParam('type');

		if (empty($strType)){
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ERROR_FILE_ACTION_TYPE');
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
		}

		$arrFilesName = glob(APPLICATION_PATH . '/../files/tmp/*.*');

		if (empty($arrFilesName)){
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('MSG_NO_FILES_TO_DELETE');
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
		}

		switch ($strType){
			case "all":
				foreach ($arrFilesName as $strFile){

					$arrFileExp = explode('.',basename($strFile));
					if (!empty($arrFileExp[1])&&$arrFileExp[1]=='log'){
						continue;
					}
					unlink($strFile);
				}
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('MSG_FILE_DELETED_SUCCESSFUL');
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
				break;
			default:
				foreach ($arrFilesName as $strFile){
					if (basename($strFile)==$strFileName){
						unlink($strFile);
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('MSG_FILE_DELETED_SUCCESSFUL');
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
					}
				}
		}
		$this->view->arrData = $arrResponse;
	}

	public function listLocksAction(){

		$strFilter = $this->getRequest()->getParam('filter_locks_report',"ALL");

		$objLocks = new Qstat_Db_Table_Lock();
		$objLocksSelect = $objLocks->select(true);
		$objLocksSelect->where(Qstat_Db_Table_Lock::COL_IS_DELETED."=?",false);
		$objLocksSelect->reset(Zend_Db_Select::COLUMNS);
		$objLocksSelect->columns(array(Qstat_Db_Table_Lock::COL_LOCK_START));
		$objLocksRowSet=$objLocks->fetchAll($objLocksSelect);

		$arrCountLocks=array();
		foreach ($objLocksRowSet as $objLocksRow){
			if (!empty( $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_START})){
				$arrResult=explode(" ", $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_START});
				if (!empty($arrResult[0])){
					$arrExplodedTime=explode("-", $arrResult[0]);
					if (!empty($arrExplodedTime)&&!empty($arrExplodedTime[0])&&!empty($arrExplodedTime[1])){
						$strYear=$arrExplodedTime[0];
						$strMounth=$arrExplodedTime[1];
						if (empty($arrCountLocks[$strYear][$strMounth])){
							$arrCountLocks[$strYear][$strMounth]=0;
						}
						$arrCountLocks[$strYear][$strMounth]++;
					}
				}
			}
		}

		$this->view->Locks=$arrCountLocks;
	}
	public function clicksAction(){

		$objTableRowSet = Qstat_Db_Table_ClickCounter::getAll();
		$arrInstall=array();
		$arrRebbot=array();
		foreach ($objTableRowSet as $objTableRow){
			if (!empty( $objTableRow->{Qstat_Db_Table_ClickCounter::COL_CREATED_ON})){

				$arrResult=explode(" ", $objTableRow->{Qstat_Db_Table_ClickCounter::COL_CREATED_ON});

				if (!empty($arrResult[0])){
					$arrExplodedTime=explode("-", $arrResult[0]);
					if (!empty($arrExplodedTime)&&!empty($arrExplodedTime[0])&&!empty($arrExplodedTime[1])){
						$strYear=$arrExplodedTime[0];
						$strMounth=$arrExplodedTime[1];

						switch ($objTableRow->{Qstat_Db_Table_ClickCounter::COL_TYPE}){

							case Qstat_Db_Table_ClickCounter::TYPE_INSTALL:
								if (empty($arrInstall[$strYear][$strMounth])){
									$arrInstall[$strYear][$strMounth]=0;
								}
								$arrInstall[$strYear][$strMounth]++;
								break;

							case Qstat_Db_Table_ClickCounter::TYPE_REBBOT:
								if (empty($arrRebbot[$strYear][$strMounth])){
									$arrRebbot[$strYear][$strMounth]=0;
								}
								$arrRebbot[$strYear][$strMounth]++;
								break;
						}
					}
				}
			}
		}

		$this->view->objInstall=$arrInstall;
		$this->view->objReboot=$arrRebbot;
	}

	public function groupslocksAction(){

		$objUsers = new User_Model_Db_Users();
		$objSelect = $objUsers->select();
		$objSelect->where(User_Model_Db_Users::COL_IS_DELETED."=?",false);
		$objUserRows = $objUsers->fetchAll($objSelect);

		$arrUserGroup=array();
		foreach ($objUserRows as $objUserRow){
			$arrExtraData=unserialize($objUserRow->{User_Model_Db_Users::COL_EXTRA_DATA});
			if (!empty($arrExtraData['groups'])){
				$arrUserGroup[$objUserRow->{User_Model_Db_Users::COL_ID_USERS}]=$arrExtraData['groups'];
			}
		}

		$objGroups = new Qstat_Db_Table_Groups();
		$objGroupsSelect = $objGroups->select();
		$objGroupsSelect->where(Qstat_Db_Table_Groups::COL_IS_DELETED." = ?",false);
		$objGroupsRowSet=$objGroups->fetchAll($objGroupsSelect);

		$arrGroupsName=array();
		foreach ($objGroupsRowSet as $objGroupsRow){
			$arrGroupsName[$objGroupsRow->{Qstat_Db_Table_Groups::COL_ID_GROUPS}]=$objGroupsRow->{Qstat_Db_Table_Groups::COL_GROUP_NAME};
		}


		$objLocks = new Qstat_Db_Table_Lock();
		$objLocksSelect = $objLocks->select(true);
		$objLocksSelect->where(Qstat_Db_Table_Lock::COL_IS_DELETED."=?",false);
		$objLocksSelect->reset(Zend_Db_Select::COLUMNS);
		$objLocksSelect->columns(array(Qstat_Db_Table_Lock::COL_LOCK_START,Qstat_Db_Table_Lock::COL_ID_USER));
		$objLocksRowSet=$objLocks->fetchAll($objLocksSelect);

		$arrResultGroupsYearMountCount=array();
		foreach ($objLocksRowSet as $objLocksRow){

			$arrResult=explode(" ", $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_START});
			if (!empty($arrResult[0])){
				$arrExplodedTime=explode("-", $arrResult[0]);
				if (!empty($arrExplodedTime)&&!empty($arrExplodedTime[0])&&!empty($arrExplodedTime[1])){
					$strYear=$arrExplodedTime[0];
					$strMounth=$arrExplodedTime[1];
				}

				if (!empty($arrUserGroup[$objLocksRow->{Qstat_Db_Table_Lock::COL_ID_USER}])&&!empty($strYear)&&!empty($strMounth)){
					if (empty($arrResultGroupsYearMountCount[$strYear][$arrGroupsName[$arrUserGroup[$objLocksRow->{Qstat_Db_Table_Lock::COL_ID_USER}]]][$strMounth])){
						$arrResultGroupsYearMountCount[$strYear][$arrGroupsName[$arrUserGroup[$objLocksRow->{Qstat_Db_Table_Lock::COL_ID_USER}]]][$strMounth]=0;
					}
					$arrResultGroupsYearMountCount[$strYear][$arrGroupsName[$arrUserGroup[$objLocksRow->{Qstat_Db_Table_Lock::COL_ID_USER}]]][$strMounth]++;
				}
			}
		}

		$this->view->strCurrentYear=date("Y");
		$this->view->intCurrentMounth=date("m")+1;
		$this->view->strPrevYear=$this->view->strCurrentYear-1;
		$this->view->prevYear=$arrResultGroupsYearMountCount[$this->view->strPrevYear];
		$this->view->currentYear=$arrResultGroupsYearMountCount[$this->view->strCurrentYear];
	}

	public function locksbyusageAction() {
		$objGroups = new Qstat_Db_Table_Groups();
		$objGroupsSelect = $objGroups
		->select()
		->from($objGroups, array(Qstat_Db_Table_Groups::COL_ID_GROUPS, Qstat_Db_Table_Groups::COL_GROUP_NAME))
		->where(Qstat_Db_Table_Groups::COL_IS_DELETED." = ?", false);
		$groups = $objGroups->fetchAll($objGroupsSelect)->toArray();

		$this->view->groups = $groups;
		$this->view->usage_persents = array(10, 20, 30, 40, 50);
		$this->view->headScript()->appendFile('https://www.google.com/jsapi', 'text/javascript');
		$this->view->headScript()->appendFile(URL_JS . 'locks-by-usage-graph.js', 'text/javascript');
	}

	public function graphdataAction() {
		if (empty($_GET['graph-data-ajax']) || empty($_GET['id-groups'])) {
			$this->_helper->json(array('success' => 0,));
			return;
		}

		$fromDate = Application_Model_Helper::validateDate(empty($_GET['from-date']) ? '' : $_GET['from-date'], '(NOW() - INTERVAL 3 MONTH)');
		$toDate = Application_Model_Helper::validateDate(empty($_GET['to-date']) ? '' : $_GET['to-date'], 'NOW()');
		$usage_persent = empty($_GET['usage-persent']) ? 10 : intval($_GET['usage-persent']);

		$graph_query =
		"SELECT `temp`.`device_type`, `temp`.`device_title`, `temp`.`using_type`, COUNT(`temp`.`id`) AS `amount` ".PHP_EOL.
		"FROM( ".PHP_EOL.
		"SELECT ".PHP_EOL.
		"`".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT."` AS `device_type`, ".PHP_EOL.
		"`".Bf_Eav_Db_EntitiesTypes::TBL_NAME."`.`".Bf_Eav_Db_EntitiesTypes::COL_ENTITY_TYPE_TITLE."` AS `device_title`, ".PHP_EOL.
		"`".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` AS `id`, ".PHP_EOL.
		"(COUNT(`".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_ID_LOCK."`) > 0) AS `using_type` ".PHP_EOL.
		"FROM `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."` ".PHP_EOL.

		"INNER JOIN `".Catalog_Model_CatalogData::TBL_NAME."` ".PHP_EOL.
		"ON `".Catalog_Model_CatalogData::TBL_NAME."`.`".Catalog_Model_CatalogData::COL_ID_CATALOG."` = `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` ".PHP_EOL.

		"INNER JOIN `".Bf_Eav_Db_EntitiesTypes::TBL_NAME."` ".PHP_EOL.
		"ON (`".Bf_Eav_Db_EntitiesTypes::TBL_NAME."`.`".Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES."` + 1) = `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT."` ".PHP_EOL.

		"LEFT JOIN `".Qstat_Db_Table_Lock::TBL_NAME."` ".PHP_EOL.
		"ON `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` = `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` ".PHP_EOL.

		"WHERE `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`module_code` = ".intval($this->_options->moduleCode)." ".PHP_EOL.
		"AND `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED."` = 0 ".PHP_EOL.
		"AND `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT."` ".
		"IN (".Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::SERVER_DEVICES ).", ".Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::SWITCH_DEVICES ).") ".PHP_EOL.
		"AND `".Catalog_Model_CatalogData::TBL_NAME."`.`".Catalog_Model_CatalogData::COL_IS_DELETED."` = 0 ".PHP_EOL.
		"AND `".Bf_Eav_Db_EntitiesTypes::TBL_NAME."`.`".Bf_Eav_Db_EntitiesTypes::COL_IS_DELETED."` = 0 ".PHP_EOL.

		"AND `".Catalog_Model_CatalogData::TBL_NAME."`.`".Catalog_Model_CatalogData::COL_ID_GROUPS."` = ".intval($_GET['id-groups'])." ".PHP_EOL.
		"AND ( ".PHP_EOL.
		"(`".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_START."` > ".$fromDate." AND `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_START."` < ".$toDate.") ".PHP_EOL.
		"OR (`".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_END."` > ".$fromDate." AND `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_END."` < ".$toDate.") ".PHP_EOL.
		"OR `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_START."` IS NULL ".PHP_EOL.
		") ".PHP_EOL.

		"GROUP BY `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT."`, `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` ".PHP_EOL.
		") AS `temp` ".PHP_EOL.
		"GROUP BY `temp`.`device_type`, `temp`.`using_type`;";
		$graph_rows = Zend_Db_Table::getDefaultAdapter()->fetchAll($graph_query);
		$return = Application_Model_Helper::prepareDataForGraph($graph_rows);

		$table_query =
		"SELECT `device_type`, `usage_persent`, `model`, `ip`, `title` ".PHP_EOL.
		"FROM ( ".PHP_EOL.
		"SELECT ".PHP_EOL.
		"COALESCE(`ips_table`.`value`, '') AS `ip`, ".PHP_EOL.
		"COALESCE(`models_table`.`value`, '') AS `model`, ".PHP_EOL.
		"`".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT."` AS `device_type`, ".PHP_EOL.
		"COALESCE(`".Catalog_Model_CatalogData::TBL_NAME."`.`".Catalog_Model_CatalogData::COL_TITLE."`, '') AS `title`, ".PHP_EOL.
		"COALESCE( ROUND( ( ".PHP_EOL.
		"SUM( TIMESTAMPDIFF( HOUR, `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_START."`, `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_END."` ) ) / ".PHP_EOL.
		 "TIMESTAMPDIFF( HOUR, ".$fromDate.", ".$toDate." ) ".PHP_EOL.
		 ") * 100 ), 0 ) AS `usage_persent` ".PHP_EOL.
		"FROM `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."` ".PHP_EOL.

		"INNER JOIN `".Catalog_Model_CatalogData::TBL_NAME."` ".PHP_EOL.
		"ON `".Catalog_Model_CatalogData::TBL_NAME."`.`".Catalog_Model_CatalogData::COL_ID_CATALOG."` = `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` ".PHP_EOL.

		"INNER JOIN `".Bf_Eav_Db_EntitiesTypes::TBL_NAME."` ".PHP_EOL.
		"ON `".Bf_Eav_Db_EntitiesTypes::TBL_NAME."`.`".Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES."` = `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT."` ".PHP_EOL.

		"INNER JOIN `".Bf_Eav_Db_EntitiesValues::TBL_NAME."` ".PHP_EOL.
		"ON `".Bf_Eav_Db_EntitiesValues::TBL_NAME."`.`".Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES."` = `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES."` ".PHP_EOL.

		"LEFT JOIN `".Bf_Eav_Db_Values_Varchar::TBL_NAME."` AS `ips_table` ".PHP_EOL.
		"ON `ips_table`.`id_values` = `".Bf_Eav_Db_EntitiesValues::TBL_NAME."`.`".Bf_Eav_Db_EntitiesValues::COL_ID_VALUES."` ".PHP_EOL.
		"AND `".Bf_Eav_Db_EntitiesValues::TBL_NAME."`.`".Bf_Eav_Db_EntitiesValues::COL_ID_ATTR."` = 10 ".PHP_EOL.
		"AND `ips_table`.`is_deleted` = 0 ".PHP_EOL.

		"LEFT JOIN `".Bf_Eav_Db_Values_Varchar::TBL_NAME."` AS `models_table` ".PHP_EOL.
		"ON `models_table`.`id_values` = `".Bf_Eav_Db_EntitiesValues::TBL_NAME."`.`".Bf_Eav_Db_EntitiesValues::COL_ID_VALUES."` ".PHP_EOL.
		"AND `".Bf_Eav_Db_EntitiesValues::TBL_NAME."`.`".Bf_Eav_Db_EntitiesValues::COL_ID_ATTR."` = 11 ".PHP_EOL.
		"AND `models_table`.`is_deleted` = 0 ".PHP_EOL.

		"LEFT JOIN `".Qstat_Db_Table_Lock::TBL_NAME."` ".PHP_EOL.
		"ON `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` = `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` ".PHP_EOL.

		"WHERE `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`module_code` = ".intval($this->_options->moduleCode)." ".PHP_EOL.
		"AND `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED."` = 0 ".PHP_EOL.
		"AND `".Catalog_Model_CatalogData::TBL_NAME."`.`".Catalog_Model_CatalogData::COL_IS_DELETED."` = 0 ".PHP_EOL.
		"AND `".Bf_Eav_Db_EntitiesTypes::TBL_NAME."`.`".Bf_Eav_Db_EntitiesTypes::COL_IS_DELETED."` = 0 ".PHP_EOL.
		"AND `".Bf_Eav_Db_EntitiesValues::TBL_NAME."`.`".Bf_Eav_Db_EntitiesValues::COL_IS_DELETED."` = 0 ".PHP_EOL.

		"AND `".Catalog_Model_CatalogData::TBL_NAME."`.`".Catalog_Model_CatalogData::COL_ID_GROUPS."` = ".intval($_GET['id-groups'])." ".PHP_EOL.
		"AND ( ".PHP_EOL.
		"(`".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_START."` > ".$fromDate." AND `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_START."` < ".$toDate.") ".PHP_EOL.
		"OR (`".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_END."` > ".$fromDate." AND `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_END."` < ".$toDate.") ".PHP_EOL.
		"OR `".Qstat_Db_Table_Lock::TBL_NAME."`.`".Qstat_Db_Table_Lock::COL_LOCK_START."` IS NULL ".PHP_EOL.
		") ".PHP_EOL.

		"GROUP BY `".Bf_Catalog_Models_Db_Catalog::TBL_NAME."`.`".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."` ".PHP_EOL.
		") AS `temp` ".PHP_EOL.
		"WHERE `usage_persent` < ".$usage_persent;
		$table_rows = Zend_Db_Table::getDefaultAdapter()->fetchAll($table_query);
		$return = Application_Model_Helper::prepareDataForTable($return, $table_rows);

		$return['id_groups'] = intval($_GET['id-groups']);
		$this->_helper->json($return);
	}

	public function duringtimelocksAction(){

		$strFilter = $this->getRequest()->getParam('filter_locks_report',"ALL");

		$objLocks = new Qstat_Db_Table_Lock();
		$objLocksSelect = $objLocks->select(true);
		$objLocksSelect->where(Qstat_Db_Table_Lock::COL_IS_DELETED."=?",false);
		$arrConf = $this->getInvokeArg('bootstrap')->getOptions();
		$objFromDate = DateTime::createFromFormat("Y-d-m","2011-31-12");
		$objLocksSelect->where(Qstat_Db_Table_Lock::COL_LOCK_START.">=?",$objFromDate->format($arrConf['dateformat']['mysql']['shortdatetime']));
		$objLocksSelect->reset(Zend_Db_Select::COLUMNS);
		$objLocksSelect->columns(array(Qstat_Db_Table_Lock::COL_LOCK_START,Qstat_Db_Table_Lock::COL_LOCK_END));
		$objLocksRowSet=$objLocks->fetchAll($objLocksSelect);

		$arrCountLocks=array();
		$intCountNotEndedLocks=0;
		$intCountEndedLocks=0;
		foreach ($objLocksRowSet as $objLocksRow){
			if (!empty( $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_END})){
				if (!empty( $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_START})){
					$intCountEndedLocks++;
					$arrResult=explode(" ", $objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_START});
					if (!empty($arrResult[0])){
						$arrExplodedTime=explode("-", $arrResult[0]);
						if (!empty($arrExplodedTime)&&!empty($arrExplodedTime[0])&&!empty($arrExplodedTime[1])){
							$strYear=$arrExplodedTime[0];
							$strMounth=$arrExplodedTime[1];

							$objDateStart = DateTime::createFromFormat($arrConf['dateformat']['mysql']['longdatetime'],$objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_END});
							$objToDate   = DateTime::createFromFormat($arrConf['dateformat']['mysql']['longdatetime'],$objLocksRow->{Qstat_Db_Table_Lock::COL_LOCK_START});
							$since_start = $objDateStart->diff($objToDate);

							$intDays = floatval($since_start->format("%a")) ;
							if ($since_start->h){
								$intDays=(float)($since_start->h)/24+$intDays;
							}
							if ($since_start->m){
								$intDays=(float)($since_start->m)/(60*24)+$intDays;
							}

							if (empty($arrCountLocks[$strYear][$strMounth])){
								$arrCountLocks[$strYear][$strMounth]=$intDays;
							}else{
								$arrCountLocks[$strYear][$strMounth]=$arrCountLocks[$strYear][$strMounth]+$intDays;
							}
						}
					}
				}
			} else {
				$intCountNotEndedLocks++;
			}
		}

		$this->view->intCountNotEndedLocks=$intCountNotEndedLocks;
		$this->view->intCountEndedLocks=$intCountEndedLocks;
		$this->view->intTotalCountLocks=$objLocksRowSet->count();
		$this->view->Locks=$arrCountLocks;
	}
}
