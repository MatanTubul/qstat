<?php

class Catalog_IndexController extends Zend_Controller_Action
{
    //check

	const DISPLAY_MODE_VIEW = 'view';
	const DISPLAY_MODE_EDIT = 'edit';
	const DISPLAY_MODE_ADD = 'add';
	const COL_ATTR_CODE = 'attribute_code';
	const DISPLAY_MODE_EDIT_MULTY = 'multy';

	protected $_options;
	protected $_displayMode = null;
	protected $_displayData = array();

	public function init()
	{
		/* Initialize action controller here */
		$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
		$this->_options = $objApplicationOptions->catalog;
	}

	public function cronAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);
		$objLock = new Qstat_Db_Table_Lock();
		$objLock->runSceduledEndLocksNotification($objCatalog,  new Zend_Config($this->getInvokeArg('bootstrap')->getOptions()));
	}

	public function moveAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);
		// $intCatalogId = (int) ($this->_request->getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, 0));
		$strCatsId = $this->_request->getParam('cat_ids', 0);
		$intTargetId = (int) ($this->_request->getParam('target_cat_id', 0));
		$arrCatalogIds = explode(',', $strCatsId);

		foreach ($arrCatalogIds as $intCatalogId){
			// Check that item exists.
			$objCatalogItems = $objCatalog->getCatalogModel()
			->getObjCatalogTable()
			->find($intCatalogId);
			if ($objCatalogItems->count() > 0) {
				//Item OK
				$objCatalogItem = $objCatalogItems->current();

				//Root target hack
				if ($intTargetId < 0) {
					$intTargetId = 0;
				}
				$boolTargetOk = true;
				if (! empty($intTargetId)) {
					//Not root - check valid target
					$objTarget = $objCatalog->getItem($intTargetId);
					if (empty($objTarget)) {
						//Target not found
						$arrResponse['error_msg'] = '';
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_TARGET_NOT_FOUND');
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
						$boolTargetOk = FALSE;
					} elseif (! $objTarget->{Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER}) {
						$arrResponse['error_msg'] = '';
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_TARGET_MUST_BE_FOLDER');
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
						$boolTargetOk = FALSE;
					} elseif (false !== strpos($objTarget->{Bf_Catalog_Models_Db_Catalog::COL_CAT_PATH}, $objCatalogItem->{Bf_Catalog_Models_Db_Catalog::COL_CAT_PATH})) {
						$arrResponse['error_msg'] = '';
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ITEM_CANNOT_MOVE_TO_CHILD');
						$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
						$boolTargetOk = FALSE;
					}
				}

				if ($boolTargetOk) {
					$intSourceParent = $objCatalogItem->{Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT};

					$objCatalogItem->{Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT} = $intTargetId;
					$objCatalogItem->save();
					$objCatalog->buildPath($intCatalogId);

					if ($objCatalogItem->{Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER}) {
						$objCatalog->getCatalogModel()
						->getObjCatalogTable()
						->resetCatPath($intCatalogId);
					}
					$objCatalog->getCatalogModel()
					->getObjCatalogTable()
					->setHasChildrenFlags($intSourceParent);
					$objCatalog->getCatalogModel()
					->getObjCatalogTable()
					->setHasChildrenFlags($intCatalogId);

					$arrResponse['error_msg'] = '';
					$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = '';
					$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
				}
			} else {
				$arrResponse['error_msg'] = '';
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ITEM_NOT_FOUND');
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
			}
		}

		$arrResponse['error_msg'] = '';
		$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = '';
		$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
		$this->view->arrData = $arrResponse;
	}

	public function getTreeDataAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);

		$intParent = (int) ( $this->_request->getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, 0) );

		$arrData = array();

		if ( empty($intParent) ) {
			//Root level
			$arrRowData = array();
			$arrRowData['data'] = "/";
			$arrRowData['attr']['id'] = 'CAT_ROOT';
			$arrRowData['state'] = 'closed';
			$arrRowData['position'] = 'first';
			$arrData[] = $arrRowData;
		} else {
			if ($intParent == - 1) {
				//Root expand hack
				$intParent = 0;
			}
			$arrCatalogData = $objCatalog->getItems($intParent, FALSE, TRUE);

			foreach ($arrCatalogData as $arrCatalogRow) {
				$arrRowData = array();
				$arrRowData['data'] = $arrCatalogRow[Catalog_Model_CatalogData::COL_TITLE];
				if ( empty($arrRowData['data']) ) {
					$arrRowData['data'] = $arrCatalogRow[Catalog_Model_CatalogData::COL_ID_CATALOG];
				}
				$arrRowData['attr']['id'] = Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . "_" . $arrCatalogRow[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG];
				$arrRowData['state'] = 'closed';
				$arrData[] = $arrRowData;
			}
		}

		$this->view->arrData = $arrData;
	}

	public function moveFormAction()
	{
		$intCatalogId = (int) ($this->_request->getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, 0));
		$this->view->intCatalogId = $intCatalogId;
		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
	}

	public function editAction()
	{
		$this->_displayMode = self::DISPLAY_MODE_EDIT;
		$intCatalogId = (int) ($this->_getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, NULL));
		if (empty($intCatalogId)) {
			$this->_forward('add');
			return;
		}
		$this->displayAction();
	}

	public function editMultiAction()
	{
		$this->_displayMode = self::DISPLAY_MODE_EDIT_MULTY;
		$this->displayAction();
	}

	public function viewAction()
	{
		$strType = $this->_getParam("is_view", 0);
		$objAcl = User_Model_Acl::$objIntance;

		if ( $objAcl->checkPermissions("catalog", "index", "edit") ) {
			$this->_displayMode = self::DISPLAY_MODE_EDIT;
		} else {
			$this->_displayMode = self::DISPLAY_MODE_VIEW;
		}

		if ( ! empty($strType) && $strType == 1 ) {
			$this->_displayMode = self::DISPLAY_MODE_VIEW;
		}

		$this->displayAction();
	}

	public function addAction()
	{
		$this->_displayMode = self::DISPLAY_MODE_ADD;
		$this->displayAction();
	}

	public function saveitemAction()
	{
		// Empty Action becouse of Permissions need
	}

	public function cardsAction(){
		$this->getRequest()->setParam( Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::CARDS_DEVICES ) );
		$this->_forward('index');
	}

	public function cablesAction(){
		$this->getRequest()->setParam( Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::CABLES_DEVICES ) );
		$this->_forward('index');
	}

	public function switchAction(){
		$this->getRequest()->setParam( Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::SWITCH_DEVICES ) );
		$this->_forward('index');
	}

	public function orcaAction(){
		$this->getRequest()->setParam( Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::ORCA_DEVICES ) );
		$this->_forward('index');
	}

	public function indexAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);
		$this->view->options = $objCatalog->getOptions();

		// Init grid options.
		$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'del'), null, false, false);
		$arrOptions = array('caption' => '', 'editurl' => $strUrl);
		$arrOptions['ondblClickRow'] = 'function(rowId, iRow, iCol, e){ catalogCall(rowId); }';
		$arrOptions['loadComplete'] = 'function() { elemsForTip = $(".item-locked"); attach_tooltip(); adjustColumnsWidth(this); }';
		$arrOptions['afterInsertRow'] = 'function(rowid, rowdata, rowelem) { calculateColumnsWidth(this, rowid, rowdata, rowelem) }';
		$arrOptions['columnsWidths'] = new Zend_Json_Expr('{}');
		$arrOptions['treeGrid'] = false;
		$arrOptions['multiselect'] = TRUE;
		$arrOptions['shrinkToFit'] = FALSE;
		$arrOptions['autowidth'] = TRUE;
		$arrOptions['rowNum'] = 100;

		// For CLI list servers and switches.
		$CLIList = $this->_request->getParam('CliList', false);
		if ( ! empty( $this->view->isReport ) && ! empty( $this->view->reportSelect ) ) {
			$objCatalogSelect = $this->view->reportSelect;

			if( ! empty( $this->view->filterForm ) ) {
				$arrOptions['postData'] = $this->view->filterForm->getValues();
			}
		} else {
			$intParent = intval( $this->_request->getParam( Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, $this->view->options->defaultViewParent ) );
			// Pass it to view.
			$this->view->intParent = $intParent;

			// For Cli list servers and switches.
			if ($CLIList) {
				$intParent = array( 5, 6, 7, );
			}

			// Get All Attributs that should be shown here.
			$objCatalogSelect = $objCatalog->getItems($intParent);
		}

		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;
		$arrUserExtraData = $objUserDetails->extraArray;
		if ( ! empty($arrUserExtraData) && ! empty($arrUserExtraData['groups']) ) {
			$arrOptions['postData'] = array(
				"filters" => Zend_Json::encode(
					array(
						'groupOp' => 'AND',
						'rules' => array(
							array(
								'field' => Qstat_Db_Table_Groups::COL_ID_GROUPS,
								'op' => 'eq',
								'data' => $arrUserExtraData['groups'],
							),
						),
					)
				),
				'grid' => 'Catalog',
			);
			$arrOptions['search'] = 'true';
		}

		$objGrid = new Ingot_JQuery_JqGrid( 'Catalog', new Ingot_JQuery_JqGrid_Adapter_DbTableSelect($objCatalogSelect), $arrOptions );
		$objGrid->setIdCol(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG);

		$objGrid->addColumn(
			new Ingot_JQuery_JqGrid_Column(
				Bf_Catalog_Models_Catalog::COL_PARENT_FLAG.'icon',
				array(
					"width" => 40,
					'search' => FALSE,
					'title' => FALSE,
					'label' => ' ',
					'sortable' => FALSE,
					'index' => Bf_Catalog_Models_Catalog::COL_PARENT_FLAG,
					'formatter' => new Zend_Json_Expr('iconFormatter'),
				)
			)
		);

		if ( ! $CLIList ) {
			// Add manager ip columns.
			// Prepare ip's values by Atribute Ip Address.
			$intAtributeId = 48;

			$objEntValues =  new Bf_Eav_Db_EntitiesValues();
			$objEntValuesSelect = $objEntValues->select(true)->setIntegrityCheck(false)
			->join(Bf_Eav_Db_Values_Varchar::TBL_NAME,Bf_Eav_Db_Values_Varchar::TBL_NAME.".id_values=".Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_VALUES)
			->where(Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_IS_DELETED."=?",false)
			->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_IS_DELETED."=?",false)
			->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ATTR."=?",$intAtributeId)
			->reset(Zend_Db_Select::COLUMNS)
			->columns(array(Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES =>Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES ))
			->columns(array(Bf_Eav_Db_Values_Varchar::COL_VALUE =>Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_VALUE ));
			$objEntValuesRow = $objEntValues->fetchAll($objEntValuesSelect);
			$arrEntValues = array();
			foreach ( $objEntValuesRow->toArray() as $arrEntValRowSet ){
				$arrEntValues[ $arrEntValRowSet[ Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES ] ]= $arrEntValRowSet[ Bf_Eav_Db_Values_Varchar::COL_VALUE ];
			}

			$objGrid->addColumn(
				new Ingot_JQuery_JqGrid_Column_Decorator_CustomIcons(
					new Ingot_JQuery_JqGrid_Column(
						Catalog_Model_CatalogData::COL_CAT_ID,
						array(
							"width" => "100",
							'search' => FALSE,
							'title' => FALSE,
							'label' => "Manage",
							'sortable' => FALSE,
						)
					),
					array( "values" => $arrEntValues, )
				)
			);
		}

		// The "Title" column.
		if ($CLIList) {
			$objGrid->addColumn(
				new Ingot_JQuery_JqGrid_Column_Decorator_ColorTitle(
					new Ingot_JQuery_JqGrid_Column(
						Catalog_Model_CatalogData::COL_TITLE,
						array(
							"width" => "70",
							"label" => "\033[37mTitle\033[0m",
							'sortable' => true,
							'useHaving' => true,
							'unionPart' => 1,
						)
					),
					array()
				)
			);
		} else {
			$objGrid->addColumn(
				new Ingot_JQuery_JqGrid_Column(
					Catalog_Model_CatalogData::COL_TITLE,
					array(
						'autowidth' => true,
						'shrinkToFit' => false,
						'sortable' => true,
						'useHaving' => true,
						'unionPart' => 1,
					)
				)
			);
		}

		// The "Sites" column.
		$objSites = new Qstat_Db_Table_Sites();
		$objSitesSelect = $objSites->getPairSelect();
		$arrSitesPairs = $objSites->getAdapter()->fetchPairs($objSitesSelect);
		$arrSitesPairs[0] = '';
		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn(
			$objGrid,
			$arrSitesPairs,
			array(
				"width" => "70",
				'index' => Catalog_Model_CatalogData::COL_ID_SITES,
				'useHaving' => true,
				'unionPart' => 1,
				'sortable' => false,
			)
		);

		// The "Groups" column.
		$objGroups = new Qstat_Db_Table_Groups();
		$objGroupsSelect = $objGroups->getPairSelect();
		$arrGroupsPairs = $objGroups->getAdapter()->fetchPairs($objGroupsSelect);
		$arrGroupsPairs[0] = '';

		$arrSearchOptions = array();
		$arrToolbarFilter = array();
		if ( ! empty($arrUserExtraData) && ! empty($arrUserExtraData['groups']) ) {
			$arrSearchOptions = array( 'defaultValue' => $arrUserExtraData['groups'] );
			// $arrToolbarFilter = array( 'triggerReload' => true );
		}
		if ($CLIList) {
			$arrSearchOptions = array();
		}

		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn(
			$objGrid,
			$arrGroupsPairs,
			array(
				'autowidth' => true,
				'shrinkToFit' => false,
				'index' => Catalog_Model_CatalogData::COL_ID_GROUPS,
				'searchoptions' => $arrSearchOptions,
				'useHaving' => true,
				'unionPart' => 1,
				'sortable' => false,
			)
		);

		// Get all attributes Filtering.
		$objAttribFiltering = new Qstat_Db_Table_AttribFilter();
		$objAttribFilteringSelect = $objAttribFiltering->select()
		->where(Qstat_Db_Table_AttribFilter::COL_IS_DELETED."=?",false)
		->where(Qstat_Db_Table_AttribFilter::COL_ID_USER."=?",$objUserDetails->id_users);
		$objAttribFilteringRowSet = $objAttribFiltering->fetchAll($objAttribFilteringSelect);
		$arrUserFiltering = array();
		if ( ! empty($objAttribFilteringRowSet) ) {
			foreach ($objAttribFilteringRowSet as $objAttribFilteringRow) {
				$arrUserFiltering[$objAttribFilteringRow->{$objAttribFiltering::COL_ATRIBUTE_ID}] = $objAttribFilteringRow->{$objAttribFiltering::COL_FILTER_BY};
			}
		}

		$arrUsersExtraDetails = unserialize($objUserDetails->extra);
		if ( ! empty($arrUsersExtraDetails['groups']) ) {
			// Get all group Filtering.
			$objAttribFiltering = new Qstat_Db_Table_AttribFilter();
			$objAttribFilteringSelect = $objAttribFiltering->select();
			$objAttribFilteringSelect->where(Qstat_Db_Table_AttribFilter::COL_IS_DELETED."=?",false);
			$objAttribFilteringSelect->where(Qstat_Db_Table_AttribFilter::COL_ID_GROUPS."=?",$arrUsersExtraDetails['groups']);
			$objAttribFilteringRowSet=$objAttribFiltering->fetchAll($objAttribFilteringSelect);

			$arrGroupsFiltering=array();
			if ( ! empty($objAttribFilteringRowSet) ) {
				foreach ($objAttribFilteringRowSet as $objAttribFilteringRow) {
					$arrGroupsFiltering[ $objAttribFilteringRow->{$objAttribFiltering::COL_ATRIBUTE_ID} ] = $objAttribFilteringRow->{$objAttribFiltering::COL_FILTER_BY};
				}
			}
		}

		if ( $objUserDetails->{User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS} ) {
			$objAttrListRowSet = Bf_Eav_Db_Attributes::getAttribList(FALSE,TRUE);
			$arrAttribsList = array();
			foreach ($objAttrListRowSet as $objAttrListRow) {
				$arrAttribsList[ $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ] = $objAttrListRow;
			}

			$objDbTable = new Qstat_Db_Table_Groups();
			$objAttribFilteringSelect = $objDbTable
			->select()
			->where(Qstat_Db_Table_AttribFilter::COL_ID_GROUPS."=?",$arrUsersExtraDetails['groups']);
			$objAttribFilteringRowSet = $objDbTable->fetchAll($objAttribFilteringSelect);
			$allIds = unserialize($objAttribFilteringRowSet[0]->custom_fields_in_table);
			if ( ! is_array($allIds) ) {
				$allIds = array();
			}
			foreach ( @$allIds as $intAttribId ) {
				if ( ! empty($intAttribId) ) {
					$objAttrib = $arrAttribsList[$intAttribId];
					$objEav = Bf_Eav_Value::factory($objAttrib->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE});

					$strDefaultValue="";
					if ( ! $CLIList) {
						// Check if have default value.
						if ( ! empty( $arrUserFiltering[$objAttrib->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ] ) ) {
							$strDefaultValue = $arrUserFiltering[ $objAttrib->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ];
						} elseif ( ! empty( $arrGroupsFiltering[$objAttrib->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ] ) ) {
							$strDefaultValue = $arrGroupsFiltering[ $objAttrib->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ];
						}
					}

					$objEav->setGridCol($objAttrib, $objGrid, $strDefaultValue);
				}
			}
		} else {
			// System default columns.
			$objAttrListRowSet = $objCatalog->getCatalogModel()->getArrAttriList();
			foreach ($objAttrListRowSet as $objAttrListRow) {
				$objEav = Bf_Eav_Value::factory($objAttrListRow->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE});

				$strDefaultValue = '';
				if ( ! $CLIList) {
					// Check if have default value.
					if ( ! empty( $arrUserFiltering[ $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ] ) ) {
						$strDefaultValue = $arrUserFiltering[ $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ];
					} elseif ( ! empty( $arrGroupsFiltering[ $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ] ) ) {
						$strDefaultValue = $arrGroupsFiltering[ $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR} ];
					}
				}

				$objEav->setGridCol($objAttrListRow, $objGrid, $strDefaultValue);
			}
		}

		$objGrid->registerPlugin( new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter( array() ) );
		// $objGrid->registerPlugin( new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter( array( 'triggerReload' => true, ) ) );

		// "Is Locked" column.
		$objColumn = new Ingot_JQuery_JqGrid_Column(
			Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED,
			array(
				"width" => "70",
				'editable' => true,
				"edittype" => "checkbox",
				"editoptions" => array( "value" => "1:0", ),
				'useHaving' => true,
				'unionPart' => 1,
				'sortable' => false,
			)
		);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select(
			new Ingot_JQuery_JqGrid_Column_Decorator_Checkbox($objColumn),
			array(
				"value" => array("" => "LBL_SELECT_ANY", "0" => "LBL_SELECT_INACTIVE", "1" => "LBL_SELECT_ACTIVE",
				)
			)
		);
		$objGrid->addColumn($column);

		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Catalog_Models_Catalog::COL_PARENT_FLAG, array('hidden' => TRUE, 'title' => '')));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, array('hidden' => TRUE)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, array('hidden' => TRUE)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER, array('hidden' => TRUE)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Catalog_Models_Db_Catalog::COL_HAS_CHILDREN, array('hidden' => TRUE)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES, array('hidden' => TRUE)));

		$objGridPager = $objGrid->getPager();

		$aclInstance = User_Model_Acl::getInstance();
		if ( $aclInstance->checkPermissions('catalog', 'index', 'add') ) {
			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array("caption" => "", "title" => "Delete Selected Rows", "buttonicon" => "ui-icon-trash", "onClickButton" => "function(){ delMulti(); }", "position" => "first")));

			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array("caption" => "", "title" => "Multi Edit Selected", "buttonicon" => "ui-icon-cart", "onClickButton" => "function(){ editMulti(); }", "position" => "first")));

			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array("caption" => "", "title" => "Edit Selected", "buttonicon" => "ui-icon-pencil", "onClickButton" => "function(){ getForm(false,null,null); }", "position" => "first")));

			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array("caption" => "", "title" => "Move", "buttonicon" => "ui-icon-arrowthick-1-ne", "onClickButton" => "function(){ moveItem(); }", "position" => "first")));

			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array("caption" => "", "title" => "Add Item", "buttonicon" => "ui-icon-plus", "onClickButton" => "function(){ addRow(false); }", "position" => "first")));

			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array("caption" => "", "title" => "Add Folder", "buttonicon" => "ui-icon-circle-plus", "onClickButton" => "function(){ getForm(true,true,0); }", //Just get form, all folders have ent_type = 0 here
						"position" => "first")));
		}

		if ($aclInstance->checkPermissions('catalog', 'index', 'get-lock-details')){
			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array("caption" => "", "title" => "Multi Lock", "buttonicon" => "ui-icon-locked", "onClickButton" => "function(){ multiLock(); }", "position" => "first")));
		}

		if (empty($this->view->isReport)) {
			$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter($arrToolbarFilter));
		}

		if ( count( $objGrid->getColumns() ) < 20 ) {
			$objGrid->setOption("shrinkToFit",true);
		}

		if ( ! empty( $this->view->showNext ) ) {
			$this->view->objGrid = $objGrid;
			$this->_forward( $this->view->showNext['action'], $this->view->showNext['controller'] );
		} else {
			$this->view->objGrid = $objGrid->render();
		}

		$this->view->arrFormNames = array("frmCatalog", "frmData", 'frmEav');
		$this->view->headScript()->appendFile(URL_JS . 'jquery.qtip.min.js', 'text/javascript');
		$this->view->headScript()->appendFile(URL_JS . 'jquery-ui-timepicker-addon.js', 'text/javascript');
	}

	public function getFormAction()
	{
		$arrRowData = array();
		$intCatalogId = $this->getRequest()->getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, 0);

		$arrForms = $this->getForms($intCatalogId);

		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		$this->view->arrForms = $arrForms;
	}

	public function getEntSelectorAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);

		$boolIsFolder = (bool) $this->getRequest()->getParam(Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER, 0);

		$objForm = new Catalog_Form_EntSelector();
		$objForm->initForm($boolIsFolder);

		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		$this->view->objForm = $objForm;
	}

	public function saveFormForUserAction(){

		$objCatalog = new Bf_Catalog($this->_options);

		$intCatalogId = (int) ($this->_getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, NULL));
		$arrForms = $this->getForms($intCatalogId);
		$arrData=array();
		foreach ($arrForms as $objForm){
			$arrData = array_merge($arrData,$objForm->getValues());
		}


		$arrGetData = $this->getRequest()->getParams();

		$arrResultData=array();
		foreach ($arrGetData as $strName => $strValue){
			$arrResultData[$strName]=$strValue;
		}

		$arrData = array_merge($arrData,$arrResultData);


		$mixSaveResult = $objCatalog->save($arrForms, $arrData);
		if (FALSE !== $mixSaveResult) {
			// Save OK
			$arrResponse['error_msg'] = '';
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $mixSaveResult;
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
		} else {
			// Save Failed. Get all error messages.
			$arrErrors = array();
			foreach ($arrForms as $objForm) {
				$arrErrors = array_merge($arrErrors, $objForm->getMessages());
			}

			$arrResponse['error_msg'] = $arrErrors;
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ERROR_CATALOG_SAVE');
			$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
		}
		$this->view->arrData = $arrResponse;
	}

	public function saveAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);

		$boolIsFolder = (bool) $this->getRequest()->getParam(Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER, 0);
		if ($boolIsFolder) {
			$arrForms = $objCatalog->getFolderForm();
		} else {
			$intEntityTypeId = (int) $this->getRequest()->getParam(Bf_Eav_Db_Entities::COL_ID_ENTITIES_TYPES, 0);
			$arrForms = $objCatalog->getItemForm($intEntityTypeId);
		}

		if ( $this->getRequest() ) {
			$arrData = $this->getRequest()->getParams();

			//Valid data
			$mixSaveResult = $objCatalog->save($arrForms, $arrData);

			if ( false !== $mixSaveResult ) {
				// Save OK
				$arrResponse['error_msg'] = '';
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $mixSaveResult;
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
			} else {
				// Save Failed. Get all error messages.
				$arrErrors = array();
				foreach ($arrForms as $objForm) {
					$arrErrors = array_merge($arrErrors, $objForm->getMessages());
				}

				$arrResponse['error_msg'] = $arrErrors;
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ERROR_CATALOG_SAVE');
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
			}
		}

		$this->view->arrData = $arrResponse;
	}

	public function delAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);
		$arrCategoriesIds= $this->getRequest()->getParam("cat_ids", 0);

		$arrResult['code']="OK";

		if (!empty($arrCategoriesIds)){
			foreach ($arrCategoriesIds as $intCatId){
				$objCatalog->delete($intCatId);
			}
			$arrResult['code']="OK";
		}else{
			$arrResult['code']="ERROR";

		}
		$this->view->arrData = $arrResult;
	}

	private function getEntitiyType($intEntityId)
	{
		$objEnt = new Bf_Eav_Db_Entities();
		$objRows = $objEnt->find($intEntityId);
		if (! empty($objRows) && $objRows->count() > 0) {
			return $objRows->current()->{Bf_Eav_Db_Entities::COL_ID_ENTITIES_TYPES};
		}

		return 0;
	}

	private function getForms($intCatalogId = null)
	{
		$objCatalog = new Bf_Catalog($this->_options);

		if ( ! empty($intCatalogId) ) {
			// Existing catalog item
			$arrRowData = $objCatalog->getItemArray($intCatalogId);
			if (empty($arrRowData)) {
				//TODO: handle error, hack attempt
				throw new Bf_Exception();
				exit;
			}

			// $arrRowData = $objRow->toArray();
			$boolIsFolder = (bool) $arrRowData[Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER];
			// :TODO MOVE TO get Item  ????
			$intEntType = (int) $this->getEntitiyType($arrRowData[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES]);
		} else {
			//New catalog Item
			$intParentId = $this->getRequest()->getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT, 0);
			$boolIsFolder = (bool) $this->getRequest()->getParam(Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER, 0);
			$intEntType = (int) $this->getRequest()->getParam(Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES, 0);
			$arrRowData[Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT] = $intParentId;
			$arrRowData[Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER] = $boolIsFolder;
			$arrRowData[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES] = 0;
			$arrRowData[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG] = 0;
		}

		if ($boolIsFolder) {
			$arrForms = $objCatalog->getFolderForm($intEntType, $arrRowData);
		} else {
			$arrForms = $objCatalog->getItemForm($intEntType, $arrRowData);
		}

		return $arrForms;
	}

	public function exportAction()
	{
		$this->_forwardExport('index');
	}

	public function exportOrcaAction()
	{
		$this->_forwardExport('orca');
	}

	public function exportSwitchAction()
	{
		$this->_forwardExport('switch');
	}

	public function exportReplyAction()
	{
		$objGrid = $this->view->objGrid;
		$arrColumns = $objGrid->getColumns();
		$arrColumnNames = array();
		$intSearch = 0;

		foreach ($arrColumns as $objColumn) {
			if ($objColumn->getOption('hidden')) {
				$objGrid->removeColumn($objColumn->getName());
				continue;
			}

			$arrColumnNames[] = $objColumn->getOption('label');
			$arrColumnCode = $objColumn->getName();
			$strParam = $this->getRequest()->getParam($arrColumnCode);

			if (! empty($strParam)) {
				$arrSearchParams[$intSearch] = '';
				$arrSearchParams[$intSearch]['field'] = $arrColumnCode;
				$arrSearchParams[$intSearch]['op'] = 'bw';
				$arrSearchParams[$intSearch]['data'] = $strParam;

				$intSearch ++;
			}
		}

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

		unset($arrRows[0]);
		unset($arrColumnNames[0]);

		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=file.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo implode(',', $arrColumnNames).PHP_EOL;

		foreach ($arrRows as $arrRow) {
			echo implode(',', $arrRow['cell']).PHP_EOL;
		}

		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}

	/**
	* @var User_Model_Db_Users|Qstat_Db_Table_Groups|Qstat_Db_Table_Sites $objDbTable
	*/
	public function viewSettingsAction() {
		$objDbTable = null;

		if ( $this->getRequest()->isPost() ) {
			$intRowId = (int)$this->getRequest()->getParam('rowId');
			if (empty($intRowId)) {
				throw new Bf_Exception(Bf_Exception::EX_NOT_VALID_PARAMS);
			}

			switch ($this->getRequest()->getParam('object')) {
				case Qstat_Db_Table_Groups::TBL_NAME:
					$objDbTable = new Qstat_Db_Table_Groups();
					break;
				case Qstat_Db_Table_Sites::TBL_NAME:
					$objDbTable = new Qstat_Db_Table_Sites();
					break;
				case User_Model_Db_Users::TBL_NAME:
					$objDbTable = new User_Model_Db_Users();
					break;
				default:
					throw new Bf_Exception(Bf_Exception::EX_NOT_VALID_PARAMS);
			}

			$objDataRow = $objDbTable->find($intRowId)->current();
			$objDataRow->{$objDbTable::COL_USE_CUSTOM_COLUMNS} = (int)$this->getRequest()->getParam($objDbTable::COL_USE_CUSTOM_COLUMNS);
			$objDataRow->{$objDbTable::COL_CUSTOM_COLUMNS} = serialize((array)$this->getRequest()->getParam($objDbTable::COL_CUSTOM_COLUMNS));
			$objDataRow->custom_fields_in_table = serialize((array)$this->getRequest()->getParam("custom_fields_in_table"));

			$objDataRow->save();

			// Refresh user session.
			$objSession = new Zend_Session_Namespace('user');
			$arrCustomColumns = User_Model_User::loadCustomColumns($objSession->userDetails->{User_Model_Db_Users::COL_ID_USERS});
			$objSession->userDetails->{User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS} = $arrCustomColumns[User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS];
			$objSession->userDetails->{User_Model_Db_Users::COL_CUSTOM_COLUMNS} = $arrCustomColumns[User_Model_Db_Users::COL_CUSTOM_COLUMNS];
		} else {
			$intSiteId = (int)$this->getRequest()->getParam(Qstat_Db_Table_Sites::COL_ID_SITES);
			$intGroupId = (int)$this->getRequest()->getParam(Qstat_Db_Table_Groups::COL_ID_GROUPS);
			$intUserId = (int)$this->getRequest()->getParam(User_Model_Db_Users::COL_ID_USERS);

			$intTestSum = $intSiteId + $intGroupId + $intUserId;

			if ( $intUserId > 0 && $intUserId == $intTestSum ) {
				// User settings.
				$intRowId = $intUserId;
				$objDbTable = new User_Model_Db_Users();
				$objDataRow = $objDbTable->find($intRowId)->current();

				$this->view->strTitle = $this->view->translate('LBL_CAT_VIEW_SETTINGS_USER').": ".$objDataRow->{User_Model_Db_Users::COL_FIRST_NAME}." ".$objDataRow->{User_Model_Db_Users::COL_LAST_NAME};
			} elseif ( $intGroupId > 0 && $intGroupId == $intTestSum ) {
				// Group settings.
				$intRowId = $intGroupId;
				$objDbTable = new Qstat_Db_Table_Groups();
				$objDataRow = $objDbTable->find($intRowId)->current();

				$this->view->strTitle = $this->view->translate('LBL_CAT_VIEW_SETTINGS_GROUP').": ".$objDataRow->{Qstat_Db_Table_Groups::COL_GROUP_NAME};
			} elseif ( $intSiteId > 0 && $intSiteId == $intTestSum ) {
				// Site settings.
				$intRowId = $intSiteId;
				$objDbTable = new Qstat_Db_Table_Sites();
				$objDataRow = $objDbTable->find($intRowId)->current();

				$this->view->strTitle = $this->view->translate('LBL_CAT_VIEW_SETTINGS_SITE').": ".$objDataRow->{Qstat_Db_Table_Sites::COL_SITE_TITLE};
			} else {
				// Rule violation, more than one parameter.
				throw new Bf_Exception(Bf_Exception::EX_NOT_VALID_PARAMS);
			}
		}

		$this->view->objDbTable = $objDbTable;
		$this->view->objDataRow = $objDataRow;
		$this->view->intRowId = $intRowId;
		$s = new Zend_Session_Namespace('user');
		$arrSelectedAttributes = @unserialize($objDataRow->{$objDbTable::COL_CUSTOM_COLUMNS});
		$arrViewSelectedAttributes = @unserialize($objDataRow->custom_fields_in_table);

		if ( false === $arrSelectedAttributes || ! is_array($arrSelectedAttributes) ) {
			$arrSelectedAttributes = array();
		}
		$this->view->arrSelectedAttributes = $arrSelectedAttributes;

		$objAttributes = new Bf_Eav_Db_Attributes();
		$objAttributesSelect = $objAttributes->select(TRUE)->setIntegrityCheck(FALSE);
		$objAttributesSelect->joinLeft(
			Bf_Eav_Db_GroupAttributes::TBL_NAME,
			Bf_Eav_Db_GroupAttributes::getColumnName(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR)."=".Bf_Eav_Db_Attributes::getColumnName(Bf_Eav_Db_Attributes::COL_ID_ATTR)." ".
			"AND `".Bf_Eav_Db_GroupAttributes::TBL_NAME."`.`".Bf_Eav_Db_GroupAttributes::COL_IS_DELETED."` = 0"
		);
		$objAttributesSelect->joinLeft(
			Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME,
			Bf_Eav_Db_EntitiesTypesGroups::getColumnName( Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES_GRP )."=".Bf_Eav_Db_GroupAttributes::getColumnName(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP)." ".
			"AND `".Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME."`.`".Bf_Eav_Db_EntitiesTypesGroups::COL_IS_DELETED."` = 0"
		);
		$objAttributesSelect->joinLeft(
			Bf_Eav_Db_EntitiesTypes::TBL_NAME,
			Bf_Eav_Db_EntitiesTypes::getColumnName( Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES)."=".Bf_Eav_Db_EntitiesTypesGroups::getColumnName(Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES )." ".
			"AND `".Bf_Eav_Db_EntitiesTypes::TBL_NAME."`.`".Bf_Eav_Db_EntitiesTypes::COL_IS_DELETED."` = 0"
		);

		$objAttributesSelect->reset(Zend_Db_Select::COLUMNS);
		$arrColumns[Bf_Eav_Db_Attributes::COL_ID_ATTR] =  Bf_Eav_Db_Attributes::getColumnName(Bf_Eav_Db_Attributes::COL_ID_ATTR);
		$arrColumns[Bf_Eav_Db_Attributes::COL_ATTR_CODE] =  Bf_Eav_Db_Attributes::getColumnName(Bf_Eav_Db_Attributes::COL_ATTR_CODE);
		$arrColumns[Bf_Eav_Db_Attributes::COL_DESCRIPTION] =  Bf_Eav_Db_Attributes::getColumnName(Bf_Eav_Db_Attributes::COL_DESCRIPTION);
		$arrColumns['ent_types'] = new Zend_Db_Expr("group_concat(DISTINCT ".Bf_Eav_Db_EntitiesTypes::getColumnName(Bf_Eav_Db_EntitiesTypes::COL_ENTITY_TYPE_TITLE)." SEPARATOR '|')");
		$objAttributesSelect->columns($arrColumns);

		$objAttributesSelect->where(Bf_Eav_Db_Attributes::getColumnName(Bf_Eav_Db_Attributes::COL_IS_DELETED)."=?", 0);
		$objAttributesSelect->group(Bf_Eav_Db_Attributes::getColumnName(Bf_Eav_Db_Attributes::COL_ID_ATTR));
		$objAttributesSelect->order(Bf_Eav_Db_Attributes::getColumnName(Bf_Eav_Db_Attributes::COL_IS_SHOW_LIST). " DESC");
		$objAttributesSelect->order(Bf_Eav_Db_GroupAttributes::getColumnName(Bf_Eav_Db_GroupAttributes::COL_ORDER). " ASC");

		$arrAttributesRows =
		$objAttributes
		->fetchAll($objAttributesSelect)
		->toArray();

		foreach ($arrAttributesRows as $arrAttributeRow) {
			$checked= "";
			if ( in_array( $arrAttributeRow[Bf_Eav_Db_Attributes::COL_ID_ATTR], $arrViewSelectedAttributes ) ) {
				$checked = "checked";
			}

			$arrAttributes[$arrAttributeRow[Bf_Eav_Db_Attributes::COL_ID_ATTR]] = $arrAttributeRow;
			$arrAttributes[$arrAttributeRow[Bf_Eav_Db_Attributes::COL_ID_ATTR]]['ent_types'] = explode('|',$arrAttributeRow['ent_types']);
			$arrAttributes[$arrAttributeRow[Bf_Eav_Db_Attributes::COL_ID_ATTR]]['ent_types'][] =
			"</li><br><label><input name='custom_fields_in_table[]' value='".$arrAttributeRow[Bf_Eav_Db_Attributes::COL_ID_ATTR]."' type='checkbox' ".$checked.">Show in table</label>";
		}

		$this->view->arrAttributes = $arrAttributes;
	}

	public function getLockDetailsAction() {
		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();

		$arrCatIds = $this->getRequest()->getParam('cat_ids',0);
		if ( empty($arrCatIds) ) {
			return;
		}

		$objCatalog = new Catalog_Model_CatalogData();
		$objCatalogLocksSelect = $objCatalog->select(true)->setIntegrityCheck(FALSE)
		->joinLeft(
			Qstat_Db_Table_Lock::TBL_NAME,
			Qstat_Db_Table_Lock::TBL_NAME.".".Qstat_Db_Table_Lock::COL_ID_CATALOG." = ".Catalog_Model_CatalogData::TBL_NAME.".".Catalog_Model_CatalogData::COL_ID_CATALOG.' '.
			"AND ". Qstat_Db_Table_Lock::TBL_NAME.".".Qstat_Db_Table_Lock::COL_IS_DELETED." = 0 ".
			"AND ".Qstat_Db_Table_Lock::TBL_NAME.".".Qstat_Db_Table_Lock::COL_LOCK_END." IS NULL"
		)
		->joinLeft(
			User_Model_Db_Users::TBL_NAME, User_Model_Db_Users::TBL_NAME . "." . User_Model_Db_Users::COL_ID_USERS . " = " . Qstat_Db_Table_Lock::TBL_NAME . "." . Qstat_Db_Table_Lock::COL_ID_USER
		)
		->where(Catalog_Model_CatalogData::TBL_NAME.".".Catalog_Model_CatalogData::COL_ID_CATALOG . " in (?)", $arrCatIds)
		->where(Catalog_Model_CatalogData::TBL_NAME.".".Catalog_Model_CatalogData::COL_IS_DELETED . "=?", FALSE)
		->columns( array( 'display_name' => new Zend_Db_Expr("CONCAT(" . User_Model_Db_Users::COL_FIRST_NAME . ",' '," . User_Model_Db_Users::COL_LAST_NAME . ")" ) ) )
		->columns( array( 'cat_id' => Catalog_Model_CatalogData::COL_ID_CATALOG ) );

		$this->view->objData = $objCatalog->fetchAll($objCatalogLocksSelect);
	}

	public function getIpsToInstallAction() {
		$strCatIds = $this->getRequest()->getParam('cat_ids',0);
		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		$intAtributeId=48;
		// This attribute is text value using class Bf_Eav_Db_Values_Varchar.

		$objCatalog =  new Bf_Catalog_Models_Db_Catalog();
		$objCatalogSelect= $objCatalog->select(true)->setIntegrityCheck(false);
		$objCatalogSelect->join(Catalog_Model_CatalogData::TBL_NAME, Catalog_Model_CatalogData::TBL_NAME.".".Catalog_Model_CatalogData::COL_ID_CATALOG."=".Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG);
		$objCatalogSelect->join(Bf_Eav_Db_EntitiesValues::TBL_NAME, Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES."=".Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES);
		$objCatalogSelect->join(Bf_Eav_Db_Values_Varchar::TBL_NAME, Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_ID_VALUES."=".Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_VALUES);
		$objCatalogSelect->reset(Zend_Db_Select::WHERE);
		$objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_MODULE_CODE."=?",1);
		$objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED."=?",false);
		$objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED."=?",false);
		$objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER."=?",false);
		$objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG." in ({$strCatIds})");
		$objCatalogSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ATTR."=?",$intAtributeId);
		$this->view->objCatalog=$objCatalog->fetchAll($objCatalogSelect);
	}

	public function redirectToServerInstallAction(){

		$arrCatIds = $this->getRequest()->getParam("cat_ids");
		$arrTitles = $this->getRequest()->getParam("cat_titles");
		$arrIps = $this->getRequest()->getParam("ips");
		$strUrl = $this->getRequest()->getParam("url_os");

		$arrConfig=Zend_Registry::get("config");
		$strUserName=$arrConfig["install"]["server"]["username"];
		$strPassword=$arrConfig["install"]["server"]["password"];
		$objUserData=User_Model_User::getUserData();
		$strPortalUser=$objUserData->username;

		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		if (empty($arrCatIds)||empty($strUrl)){
			$this->view->translate("LBL_ERROR_INPUT_PARAMS");
			exit;
		}

		Qstat_Db_Table_ClickCounter::count(Qstat_Db_Table_ClickCounter::TYPE_INSTALL);

		$strUrl.="?ipmi={$arrIps[$arrCatIds[0]]}&machine={$arrTitles[$arrCatIds[0]]}&user={$strUserName}&password={$strPassword}&qstat_user={$strPortalUser}";
		if (count($arrCatIds)>1){
			$strUrl.="&multiselect=";
			foreach ($arrCatIds as $cta_id){
				$strUrl.=$arrIps[$cta_id].":".$arrTitles[$cta_id].",";
			}
		}

		$this->view->result="";
		//lock all servers
		$objCatalog = new Bf_Catalog($this->_options);
		foreach ($arrCatIds as $intCatalogId){

			$objItemRow = $objCatalog->getCatalogModel()
			->getObjCatalogTable()
			->find($intCatalogId)
			->current();

			$objLocks = new Qstat_Db_Table_Lock();
			$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
			$objUserSessionData = new Zend_Session_Namespace('user');
			$objUserDetails = $objUserSessionData->userDetails;
			$objLocks->createLock($intCatalogId, $objUserDetails->{User_Model_Db_Users::COL_ID_USERS}, $objApplicationOptions->defaultUnlockTimeForInstallServer);

			$objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} = 1;
			$objItemRow->save();
			$this->view->result.= "Catalog :".$intCatalogId." Title: ".$arrTitles[$intCatalogId]." is locked for ".$objApplicationOptions->defaultUnlockTimeForInstallServer." minutes.<br>";
		}

		$this->view->strUrl= $strUrl;
	}

	public function redirectToServerRebootAction(){

		$intCatalogId = $this->getRequest()->getParam("cat_id");
		$intEntityId = $this->getRequest()->getParam("ip");

		$intAtributeId = 10;

		$objEntValues =  new Bf_Eav_Db_EntitiesValues();
		$objEntValuesSelect= $objEntValues->select(true)->setIntegrityCheck(false);
		$objEntValuesSelect->join(Bf_Eav_Db_Values_Varchar::TBL_NAME,Bf_Eav_Db_Values_Varchar::TBL_NAME.".id_values=".Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_VALUES);
		$objEntValuesSelect->where(Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_IS_DELETED."=?",false);
		$objEntValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_IS_DELETED."=?",false);
		$objEntValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ATTR."=?",$intAtributeId);
		$objEntValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES."=?",$intEntityId);
		$objEntValuesSelect->reset(Zend_Db_Select::COLUMNS);
		$objEntValuesSelect->columns(array(Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES =>Bf_Eav_Db_EntitiesValues::TBL_NAME.".".Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES ));
		$objEntValuesSelect->columns(array(Bf_Eav_Db_Values_Varchar::COL_VALUE =>Bf_Eav_Db_Values_Varchar::TBL_NAME.".".Bf_Eav_Db_Values_Varchar::COL_VALUE ));
		$objEntValuesRow=$objEntValues->fetchRow($objEntValuesSelect);

		if (empty($objEntValuesRow->{Bf_Eav_Db_Values_Varchar::COL_VALUE})){
			$this->view->translate("LBL_ERROR_NO_IP");
			exit;
		}

		Qstat_Db_Table_ClickCounter::count(Qstat_Db_Table_ClickCounter::TYPE_REBBOT);

		$strIp = $objEntValuesRow->{Bf_Eav_Db_Values_Varchar::COL_VALUE};

		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		if (empty($intCatalogId)||empty($strIp)){
			$this->view->translate("LBL_ERROR_INPUT_PARAMS");
			exit;
		}

		//lock all servers
		$objCatalog = new Bf_Catalog($this->_options);

		$objItemRow = $objCatalog->getCatalogModel()
		->getObjCatalogTable()
		->find($intCatalogId)
		->current();
		if ($objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED}==1){
			$this->view->error="This Server is Locked, Please unlock it than try again!";
			return;
		}

		$objLocks = new Qstat_Db_Table_Lock();
		$objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;
		$objLocks->createLock($intCatalogId, $objUserDetails->{User_Model_Db_Users::COL_ID_USERS}, $objApplicationOptions->defaultUnlockTimeForRebootServer);
		$objItemRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED} = 1;
		$objItemRow->save();
		$this->view->result.= "The server was locked for  ".$objApplicationOptions->defaultUnlockTimeForRebootServer." minutes.<br>";
		$this->view->result.= '<br> execute: /mswg/utils/bin/rreboot '.$strIp."  ". exec('/mswg/utils/bin/rreboot '.$strIp);
	}

	private function _forwardExport($device_type) {
		$this->getRequest()->setParam('grid', 'Catalog');

		if ($this->getRequest()->getParam('rows', 0)) {
			$this->getRequest()->setParam('rows', $this->getRequest()->getParam('rows') + 1);
		} else {
			$this->getRequest()->setParam('rows', '10000');
		}

		$this->_forward($device_type);
		$this->view->showNext = array('module' => 'catalog', 'controller' => 'index', 'action' => 'export-reply');
	}

	private function disableElement($objElement, $ids, $groupNname = "")
	{
		if ($objElement instanceof Zend_Form_DisplayGroup) {
			foreach ($objElement->getElements() as $objSubElement) {
				$this->disableElement($objSubElement,$ids,$objElement->getName());
			}
		} else {
			$objId = Bf_Eav_Db_Attributes::getId($objElement->getLabel());

			/*
			$aclInstance = User_Model_Acl::getInstance();
			if ($aclInstance->checkPermissions('catalog', 'index', 'save-form-for-user')) {
			$boolCanEdit = Bf_Eav_Db_Attributes::getIsUserCanEdit($objElement->getName());

			if ($boolCanEdit) {
			$objElement->setAttrib("class", "can_edit");

			if ($objElement->helper =="formSelect")
			{
			$objElement->setDescription('<a href="javascript:getForm(0,0,'.$objId.')" edit="'.$objId.'">Edit</a>');
			$objElement->getDecorator('Description')->setOption('escape', false);
			}
			} else {
			$objElement->setAttrib('disabled', 'disabled')->setAttrib('readonly', 'readonly');
			}

			} else {
			*/
			$objElement->setAttrib('disabled', 'disabled')->setAttrib('readonly', 'readonly');
			// }

			if ( ! in_array($objId, $ids) && $groupNname !== "frmDataGrp" )
			{
				$objElement->setDecorators(
					array(
						array(
							'HtmlTag',
							array('tag' => 'dd', 'style' => 'display:none', ),
						),
					)
				);
			}
		}
	}

	private function addEditBtn($objElement, $ids, $groupNname = "")
	{
		if ($objElement instanceof Zend_Form_DisplayGroup) {
			foreach ( $objElement->getElements() as $objSubElement ) {
				$this->addEditBtn( $objSubElement, $ids, $objElement->getName() );
			}

			return;
		}

		$objId = Bf_Eav_Db_Attributes::getId( $objElement->getLabel() );

		if ( $objElement->helper == "formSelect" ) {
			if ( ! $objId ) {
				$objId = Bf_Eav_Db_Attributes::getId($objElement->getName());
			}

			if ( @$objId ) {
				$objElement->setDescription('<a href="javascript:getForm(0,0,'.$objId.')" edit="'.$objId.'">Edit</a>');
				$objElement->getDecorator('Description')->setOption('escape', false);
			}
		}

		if ( ! in_array($objId, $ids) && $groupNname != "frmDataGrp" && $objElement->getName() !== 'id_entities_types' ) {
			$objElement->setDecorators(
				array(
					array(
						'HtmlTag',
						array( 'tag' => 'dd', 'style' => 'display:none', ),
					),
				)
			);
		}
	}

	private function displayAction()
	{
		$objCatalog = new Bf_Catalog($this->_options);
		$intCatalogId = (int) ( $this->_getParam( Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, 0 ) );
		$arrForms = $this->getForms($intCatalogId);
		$objItem = $objCatalog->getItem($intCatalogId);
		$catalogItemDataIdGroup = empty( $objItem->id_groups ) ? '' : $objItem->id_groups;

		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;
		$arrUserExtraData = $objUserDetails->extraArray;
		$userRole = empty( $objUserDetails->{User_Model_Db_Users::COL_ID_ROLE} ) ? '1' : $objUserDetails->{User_Model_Db_Users::COL_ID_ROLE};

		$allIds = array();
		$groupToUse = '';
		if ( in_array( $userRole, array( '7', '3', ) ) && ! empty($catalogItemDataIdGroup ) ) {
			// User is "Global Manager" or "Sys Admin". Use the original machine group.
			$groupToUse = $catalogItemDataIdGroup;
		} elseif ( ! empty( $arrUserExtraData['groups'] ) ) {
			// Use the User group.
			$groupToUse = $arrUserExtraData['groups'];
			if ( $groupToUse !== $catalogItemDataIdGroup && is_array( $arrUserExtraData['subgroups'] ) && in_array( $catalogItemDataIdGroup, $arrUserExtraData['subgroups'] ) ) {
				// Concurrency into SubGroup found.
				$groupToUse = $catalogItemDataIdGroup;
			}
		}

		switch ($this->_displayMode) {
			case self::DISPLAY_MODE_VIEW:
				if (empty($objItem)) {
					$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'index'), null, true);
					$this->_redirect($strUrl);
					return;
				}

				if ( $objUserDetails->{User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS} ) {
					$objDbTable = new Qstat_Db_Table_Groups();
					$objAttribFilteringSelect = $objDbTable->select();
					$objAttribFilteringSelect->where(Qstat_Db_Table_AttribFilter::COL_ID_GROUPS."=?", empty( $arrUserExtraData['groups'] ) ? '' : $arrUserExtraData['groups'] );
					$objAttribFilteringRowSet = $objDbTable->fetchAll($objAttribFilteringSelect);

					if ( ! empty($objAttribFilteringRowSet[0]->custom_fields) ) {
						$allIds = unserialize($objAttribFilteringRowSet[0]->custom_fields);
						if ( ! is_array($allIds) ) {
							$allIds = array();
						}
					}
				}

				foreach ($arrForms as $objForm) {
					foreach ($objForm as $k => $objElement) {
						$this->disableElement($objElement,$allIds);
					}
				}

				$arrButtons[] = array('onClick' => 'saveFormForUser()', 'module' => 'catalog', 'controller' => 'index', "action" => "save-form-for-user", "name" => "LBL_BUTTON_CATALOG_SAVE");
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "edit", "name" => "LBL_BUTTON_CATALOG_EDIT",
					"params" => array(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG => $intCatalogId));
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "index", "name" => "LBL_BUTTON_CATALOG_LIST",
					"params" => array(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT => $objItem->{Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT}));
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "move", 'onClick' => 'move();', "name" => "LBL_BUTTON_CATALOG_MOVE");

				break;
			case self::DISPLAY_MODE_EDIT:
				if (empty($objItem)) {
					$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'index'), null, true);
					$this->_redirect($strUrl);

					return;
				}

				if ( $objUserDetails->{User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS} ) {
					$objDbTable = new Qstat_Db_Table_Groups();
					$objAttribFilteringSelect = $objDbTable->select();
					$objAttribFilteringSelect->where( Qstat_Db_Table_AttribFilter::COL_ID_GROUPS."=?", $groupToUse );
					$objAttribFilteringRowSet = $objDbTable->fetchAll($objAttribFilteringSelect);

					if ( ! empty($objAttribFilteringRowSet[0]->custom_fields) ) {
						$allIds = unserialize($objAttribFilteringRowSet[0]->custom_fields);
						if ( ! is_array($allIds) ) {
							$allIds = array();
						}
					}
				}

				foreach ($arrForms as $objForm) {
					foreach ($objForm as $objElement) {
						if ($objForm->getId() === 'frmCatalog') {
							continue;
						}

						$this->addEditBtn($objElement, $allIds);
					}
				}

				$arrButtons[] = array('onClick' => 'saveForm(0)', 'module' => 'catalog', 'controller' => 'index', "action" => "saveitem", "name" => "LBL_BUTTON_CATALOG_SAVE");
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "view", "name" => "LBL_BUTTON_CATALOG_BACK_TO_VIEW",
					"params" => array(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG => $intCatalogId, "is_view" => 1));
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "index", "name" => "LBL_BUTTON_CATALOG_LIST",
					"params" => array(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT => $objItem->{Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT}));

				break;
			case self::DISPLAY_MODE_ADD:
				$arrButtons[] = array('onClick' => 'saveForm(0)', 'module' => 'catalog', 'controller' => 'index', "action" => "save", "name" => "LBL_BUTTON_CATALOG_SAVE");
				$arrButtons[] = array('onClick' => 'saveForm(1)', 'module' => 'catalog', 'controller' => 'index', "action" => "save", "name" => "LBL_BUTTON_CATALOG_SAVE_AND_ADD_NEW");
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "index", "name" => "LBL_BUTTON_CATALOG_LIST",
					"params" => array(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT => $this->_getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT)));

				break;
			case self::DISPLAY_MODE_EDIT_MULTY:
				$objForm = new Zend_Form();
				$objForm->addElement(new Zend_Form_Element_Hidden('catalog_ids'));
				$arrForms['frmData']->removeElement('title');
				$objElCatIds = new Zend_Form_Element_Hidden('catalog_ids');
				$objElCatIds->setValue($this->_getParam('catalog_ids', NULL));
				$arrForms['frmData']->addElement($objElCatIds);
				$arrButtons[] = array('onClick' => 'saveForm(0)', 'module' => 'catalog', 'controller' => 'index', "action" => "save", "name" => "LBL_BUTTON_CATALOG_SAVE");
				$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "index", "name" => "LBL_BUTTON_CATALOG_LIST",
					"params" => array(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT => $this->_getParam(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT)));

				break;
		}

		$this->view->intCatalogId = $intCatalogId;
		$this->view->intCatalogIds = $this->_getParam('catalog_ids', NULL);
		$this->view->displayMode = $this->_displayMode;
		$this->view->options = $objCatalog->getOptions();
		$this->view->arrForms = $arrForms;
		$this->view->arrActions = $arrButtons;
		$this->view->arrFormNames = array("frmCatalog", "frmData", 'frmEav');

		$this->_helper->viewRenderer->setRender('display');
		$this->view->headScript()->appendFile(URL_JS . 'jquery-ui-timepicker-addon.js', 'text/javascript');
	}
}
