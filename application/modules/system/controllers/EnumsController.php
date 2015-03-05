<?php
/**
 * EnumsController
 *
 * @author
 * @version
 */
class System_EnumsController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		//NOOP
	}


	public function sitesAction() {

		$arrOptions = array ('caption'=>'' );
		$arrOptions['sortname'] =  Qstat_Db_Table_Sites::COL_SITE_TITLE;
		$arrOptions['sortorder'] =  Ingot_JQuery_JqGrid::SORT_ASC;

		$objGrid = new Ingot_JQuery_JqGrid ( 'Sites', "Qstat_Db_Table_Sites", $arrOptions );
		$objGrid->setIdCol ( Qstat_Db_Table_Sites::COL_ID_SITES );
		$objGrid->setDblClkEdit(TRUE);
		$objGrid->setLocalEdit();

		$objGrid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_Sites::COL_ID_SITES ) );
		$objGrid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_Sites::COL_SITE_TITLE, array ('editable' => true ) ) );

		$objGridPager = $objGrid->getPager ();

		$objGridPager->setDefaultAdd ();
		$objGridPager->setDefaultEdit ();
		$objGridPager->setDefaultDel ();

		$objGrid->registerPlugin(
			new Ingot_JQuery_JqGrid_Plugin_CustomButton(
				array("caption" => "", "title" => $this->view->translate('LBL_BUTTON_SITE_CATALOG_COLUMNS'), "buttonicon" => "ui-icon-bookmark", "onClickButton" => "function(){ editColumns(); }", "position" => "first")));


		$objGrid->registerPlugin ( new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter () );
		$this->view->objGrid = $objGrid->render ();
	}

	public function groupsAction() {

		$arrOptions = array ('caption'=>'' );
		//		$arrOptions['sortname'] =  Bf_Db_Table_Sites::COL_SITE_TITLE;
		//		$arrOptions['sortorder'] =  Ingot_JQuery_JqGrid::SORT_ASC;

		$objGrid = new Ingot_JQuery_JqGrid ( 'Sites', "Qstat_Db_Table_Groups", $arrOptions );
		$objGrid->setIdCol ( Qstat_Db_Table_Groups::COL_ID_GROUPS );
		$objGrid->setDblClkEdit(TRUE);
		$objGrid->setLocalEdit();

		$objGrid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_Groups::COL_ID_GROUPS ) );
		$objGrid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_Groups::COL_GROUP_NAME, array ('editable' => true ) ) );

		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($objGrid, 'Sites', array(), FALSE );

		$arrValues=array("servers"=>"servers","switch"=>"switch","orca"=>"orca");

		$objParentEditDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_Edit_Select(new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_Groups::COL_DEFAULT_SCREEN, array ('editable' => true ) ), array('value' => $arrValues), array( 'edithidden' => true));

		$objGrid->addColumn ( $objParentEditDecorator );
		$objGridPager = $objGrid->getPager ();

		$objGridPager->setDefaultAdd ();
		$objGridPager->setDefaultEdit ();
		$objGridPager->setDefaultDel ();

		$objGrid->registerPlugin(
			new Ingot_JQuery_JqGrid_Plugin_CustomButton(
				array("caption" => "", "title" => $this->view->translate('LBL_BUTTON_SITE_CATALOG_COLUMNS'), "buttonicon" => "ui-icon-bookmark", "onClickButton" => "function(){ editColumns(); }", "position" => "first")));

		$objGrid->registerPlugin ( new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter () );
		$this->view->objGrid = $objGrid->render ();
	}


	public function importsettingsAction() {
		$arrOptions = array ('caption'=>'' );

		$objImportSettings = new Qstat_Db_Table_ImportSettings();
		$objSelect = $objImportSettings->select();

		$objGrid = new Ingot_JQuery_JqGrid ( 'Sites', $objSelect, $arrOptions );
		$objGrid->setIdCol ( Qstat_Db_Table_ImportSettings::COL_ID_IMPORT_SETTINGS );
		$objGrid->setDblClkEdit(TRUE);
		$objGrid->setLocalEdit();

		$objGrid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_ImportSettings::COL_ID_IMPORT_SETTINGS ) );
		$objGrid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_ImportSettings::COL_NAME, array ('editable' => true ) ) );

		$objColumn = new Ingot_JQuery_JqGrid_Column(
			Qstat_Db_Table_ImportSettings::COL_IS_ACTIVE,
			array(
				"width" => "70",
				'editable' => true,
				"edittype" => "checkbox",
				"editoptions" => array("value" => "1:0"),
				'useHaving' => true,
				'unionPart' => 1,
			)
		);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Checkbox($objColumn);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select($column, array("value" => array("" => "LBL_SELECT_ANY", "0" => "LBL_SELECT_INACTIVE", "1" => "LBL_SELECT_ACTIVE")));
		$objGrid->addColumn($column);

		$objColumn = new Ingot_JQuery_JqGrid_Column(
			Qstat_Db_Table_ImportSettings::COL_IS_UPDATED,
			array(
				"width" => "70",
				'editable' => true,
				"edittype" => "checkbox",
				"editoptions" => array("value" => "1:0"),
				'useHaving' => true,
				'unionPart' => 1,
			)
		);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Checkbox($objColumn);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select($column, array("value" => array("" => "LBL_SELECT_ANY", "0" => "LBL_SELECT_INACTIVE", "1" => "LBL_SELECT_ACTIVE")));
		$objGrid->addColumn($column);

		$objGrid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_ImportSettings::COL_PATH, array ('editable' => true ) ) );

		$objCatalog = new Bf_Catalog_Models_Db_Catalog();
		$objCatalog->setModuleCode(1);
		$objSelect  = $objCatalog->select(true)->setIntegrityCheck(false);
		$objSelect->join(Catalog_Model_CatalogData::TBL_NAME,Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."=".Catalog_Model_CatalogData::TBL_NAME.".".Catalog_Model_CatalogData::COL_ID_CATALOG);

		$objSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT."=?",0);
		$objSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED."!=?",true);
		$objCatalogRowSets = $objCatalog->fetchAll($objSelect);

		$arrValues=array();
		foreach ($objCatalogRowSets as $objCatalogRow){
			$arrValues[$objCatalogRow->{Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG}]=$objCatalogRow->{Catalog_Model_CatalogData::COL_TITLE};
		}

		$objParentEditDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_Edit_Select(new Ingot_JQuery_JqGrid_Column ( Qstat_Db_Table_ImportSettings::COL_PARENT_ID, array ('editable' => true ,'useHaving' => true) ), array('value' => $arrValues), array( 'edithidden' => true));
		$objGrid->addColumn ( $objParentEditDecorator );

		$objGridPager = $objGrid->getPager ();

		$objGridPager->setDefaultAdd ();
		$objGridPager->setDefaultEdit ();
		$objGridPager->setDefaultDel ();

		$objGrid->registerPlugin ( new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter () );
		$this->view->objGrid = $objGrid->render ();
	}
}
