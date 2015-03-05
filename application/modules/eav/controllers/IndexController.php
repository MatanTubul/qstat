<?php
/**
* Eav IndexController
*
* @author
* @version
*/
class Eav_IndexController extends Zend_Controller_Action
{

	/**
	* The default action - show the home page
	*/
	public function indexAction () {
		// NOOP
	}

	public function entityTypesAction () {
		$arrOptions = array('caption' => '');
		$arrOptions['sortname'] = Bf_Eav_Db_EntitiesTypes::COL_SORT_ORDER;
		$arrOptions['sortorder'] = Ingot_JQuery_JqGrid::SORT_ASC;
		$arrOptions['rowNum'] = 100;

		$objGrid = new Ingot_JQuery_JqGrid('EntitiesTypes', "Bf_Eav_Db_EntitiesTypes", $arrOptions);
		$objGrid->setIdCol(Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES);
		$objGrid->setLocalEdit();

		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypes::COL_ENTITY_TYPE_TITLE, array('editable' => true)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypes::COL_SORT_ORDER, array('editable' => true, '')));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypes::COL_IS_FOLDER, array('editable' => true, 'edittype' => 'select', 'editoptions' => array('defaultValue' => 0, 'value' => array('1' => 'LBL_ENT_TYPE_FOLDER', 0 => 'LBL_ENT_TYPE_ITEM')))));

		$objGridPager = $objGrid->getPager();
		$objGridPager->setDefaultAdd();
		$objGridPager->setDefaultEdit();
		$objGridPager->setDefaultDel();

		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_Row_DblClkRedirect('eav', 'index', 'entity-types-attr', 'EntTypeId'));
		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());
		$this->view->objGrid = $objGrid->render();
	}

	public function entityTypesAttrAction () {
		$intEntityTypeId = $this->getRequest()->getParam('EntTypeId');

		if (empty($intEntityTypeId)) {
			// :TODO ERROR...
		}

		// :TODO Maybe check that the Ent Type Valid...

		$objEntTypeAttr = new Bf_Eav_Db_Attributes();
		$objEntTypeAttrSelect = $objEntTypeAttr->select(TRUE)->setIntegrityCheck(FALSE);
		$objEntTypeAttrSelect->join(Bf_Eav_Db_GroupAttributes::TBL_NAME, Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR . ' = ' . Bf_Eav_Db_Attributes::TBL_NAME . '.' . Bf_Eav_Db_Attributes::COL_ID_ATTR);
		$objEntTypeAttrSelect->join(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME, Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . '.' . Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES_GRP . ' = ' . Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP);

		$objEntTypeAttrSelect->where(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . '.' . Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES . " = ?", $intEntityTypeId);
		$objEntTypeAttrSelect->where(Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_IS_DELETED . " = ?", FALSE);
		$objEntTypeAttrSelect->where(Bf_Eav_Db_Attributes::TBL_NAME . '.' . Bf_Eav_Db_Attributes::COL_IS_DELETED . " = ?", FALSE);

		$objEntTypeAttrSelect->reset(Zend_Db_Select::COLUMNS);
		$objEntTypeAttrSelect->columns(array(Bf_Eav_Db_Attributes::COL_ATTR_CODE), Bf_Eav_Db_Attributes::TBL_NAME);
		$objEntTypeAttrSelect->columns(array(Bf_Eav_Db_EntitiesTypesGroups::COL_GRP_LEGEND_CODE), Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME);
		$objEntTypeAttrSelect->columns(array(Bf_Eav_Db_GroupAttributes::COL_ORDER, Bf_Eav_Db_GroupAttributes::COL_IS_REQUIERED), Bf_Eav_Db_GroupAttributes::TBL_NAME);

		$objEntTypeAttrSelect->columns(array('id' => new Zend_Db_Expr("CONCAT_WS('_'," . Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP . "," . Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR . ")")));

		$objEntTypeAttrSelect->order(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . '.' . Bf_Eav_Db_EntitiesTypesGroups::COL_ORDER . " " . Zend_Db_Select::SQL_ASC);

		$arrOptions = array('caption' => '');

		$strUrl = $this->view->url(array('module' => 'eav', 'controller' => 'index', 'action' => 'save-group-attrib'), null, TRUE);
		$arrOptions['editurl'] = $strUrl;

		$arrOptions['grouping'] = true;
		$arrOptions['groupingView'] = array('groupField' => array(Bf_Eav_Db_EntitiesTypesGroups::COL_GRP_LEGEND_CODE), 'groupDataSorted' => TRUE, 'groupColumnShow' => array(FALSE), "groupText" => array('<b>{0} - {1} Item(s)</b>'));

		$arrOptions['sortname'] = Bf_Eav_Db_EntitiesTypes::COL_SORT_ORDER;
		//		$arrOptions['sortorder'] =  Ingot_JQuery_JqGrid::SORT_ASC;

		$objGrid = new Ingot_JQuery_JqGrid('Attributes', $objEntTypeAttrSelect, $arrOptions);
		$objGrid->setIdCol('id');
		$objGrid->setDblClkEdit(TRUE);

		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_Attributes::COL_ATTR_CODE));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypesGroups::COL_GRP_LEGEND_CODE));

		$objColumn = new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_GroupAttributes::COL_IS_REQUIERED, array('editable' => true, "edittype" => "checkbox", "editoptions" => array("value" => "1:0")));
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Checkbox($objColumn);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select($column, array("value" => array("" => "LBL_SELECT_ANY", "0" => "LBL_SELECT_INACTIVE", "1" => "LBL_SELECT_ACTIVE")));
		$objGrid->addColumn($column);

		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_GroupAttributes::COL_ORDER, array('editable' => true)));

		$objGridPager = $objGrid->getPager();

		$objGridPager->setDefaultEdit();
		$objGridPager->setDefaultDel();

		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_CustomButton(array("caption" => "", "title" => "Add Item", "buttonicon" => "ui-icon-plus", "onClickButton" => "function(){ $('#allAttribGrid').dialog('open'); }", "position" => "first")));

		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());
		$this->view->objGrid = $objGrid->render();

		// All Atrribute Grid...
		$this->view->objAllAttribGrid = $this->getAllAttribGrid('function(rowId, iRow, iCol, e){ addAttrib(rowId, 1); }');

		//Get Group Select


		$this->view->objGroupForm = new Eav_Form_EntTypeGroupForm($intEntityTypeId);

		$arrButtons[] = array('module' => 'eav', 'controller' => 'index', "action" => "entity-types-grp", "name" => 'LBL_BUTTON_EAV_MNG_GRP', 'params' => array('EntTypeId' => $intEntityTypeId));
		$arrButtons[] = array('module' => 'eav', 'controller' => 'index', "action" => "entity-types", "name" => 'LBL_BUTTON_EAV_MNG_ENT_TYPES');
		$this->view->arrActions = $arrButtons;
	}

	protected function getAllAttribGrid ($strDoubleClick = NULL, $boolCanEdit = FALSE) {
		$arrOptions = array('caption' => '');

		if ( ! empty($strDoubleClick) ) {
			$arrOptions['ondblClickRow'] = $strDoubleClick;
		}

		$objGrid = new Ingot_JQuery_JqGrid('AllAttributes', 'Bf_Eav_Db_Attributes', $arrOptions);
		$objGrid->setIdCol(Bf_Eav_Db_Attributes::COL_ID_ATTR);
		$objGrid->setLocalEdit();

		$objGrid->addColumn( new Ingot_JQuery_JqGrid_Column( Bf_Eav_Db_Attributes::COL_ATTR_CODE, array( 'editable' => true, 'editrules' => array( 'custom' => TRUE, 'custom_func' => new Zend_Json_Expr('checkUniqueAttr'), ), ) ) );
		$objGrid->addColumn( new Ingot_JQuery_JqGrid_Column( Bf_Eav_Db_Attributes::COL_LANSWEEPER_CODE, array( 'editable' => true, 'editrules' => array( 'custom' => TRUE, 'custom_func' => new Zend_Json_Expr('checkUniqueAttr'), ), ) ) );
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_Attributes::COL_DESCRIPTION, array('editable' => true)));
		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($objGrid, Bf_Eav_Db_Attributes::$arrAttrValType, array('index' => Bf_Eav_Db_Attributes::COL_VALUE_TYPE, 'translate' => true));

		$objColumn = new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_Attributes::COL_IS_SHOW_LIST, array('editable' => true, "edittype" => "checkbox", "editoptions" => array("value" => "1:0")));
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Checkbox($objColumn);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select($column, array("value" => array("" => "LBL_SELECT_ANY", "0" => "LBL_SELECT_DONT_SHOW_VIEW", "1" => "LBL_SELECT_SHOW_VIEW")));
		$objGrid->addColumn($column);

		$objColumn = new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_Attributes::COL_IS_USER_CAN_EDIT, array('editable' => true, "edittype" => "checkbox", "editoptions" => array("value" => "1:0")));
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Checkbox($objColumn);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select($column, array("value" => array("" => "LBL_SELECT_ANY", "0" => "LBL_SELECT_CANT_EDIT", "1" => "LBL_SELECT_CAN_EDIT")));
		$objGrid->addColumn($column);

		/*
		$objColumn = new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_Attributes::COL_IS_MULTI_VALUE, array('editable' => true, "edittype" => "checkbox", "editoptions" => array("value" => "1:0")));
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Checkbox($objColumn);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select($column, array("value" => array("" => "LBL_SELECT_ANY", "0" => "LBL_SELECT_INACTIVE", "1" => "LBL_SELECT_ACTIVE")));
		$objGrid->addColumn($column);
		*/

		$objColumn = new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_Attributes::COL_UNITS, array('editable' => true, "edittype" => "text"));
		$objGrid->addColumn($objColumn);

		$objGridPager = $objGrid->getPager();

		if ($boolCanEdit) {
			$objGridPager->setDefaultDel();
			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(array(
					"caption" => "", "title" => "Edit Selected", "buttonicon" => "ui-icon-pencil", "onClickButton" => "function(){ getForm(false, null, null); }", "position" => "first",
				))
			);
			$objGrid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(array(
					"caption" => "", "title" => "Add Item", "buttonicon" => "ui-icon-plus", "onClickButton" => "function(){ addAttrib(); }", "position" => "first",
				))
			);
		}

		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());

		return $objGrid->render();
	}

	public function checkUniqueAttrCodeAction () {

		$strAttrCode = $this->getRequest()->getParam('AttrCode');

		$objAttr = new Bf_Eav_Db_Attributes();
		$objAttrSelect = $objAttr->select(TRUE);
		$objAttrSelect->where(Bf_Eav_Db_Attributes::COL_ATTR_CODE . " = ?", $strAttrCode);
		$objAttrRow = $objAttr->fetchRow($objAttrSelect);

		$boolRetData = FALSE;

		if (empty($objAttrRow)) {
			$boolRetData = TRUE;
		}

		$this->view->data = $boolRetData;

	}

	public function checkUniqueAttrGroupCodeAction () {

		$strAttrCode = $this->getRequest()->getParam(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR);
		$strAttrGrpCode = $this->getRequest()->getParam(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP);

		$objAttr = new Bf_Eav_Db_GroupAttributes();
		$objAttrSelect = $objAttr->select(TRUE);
		$objAttrSelect->where(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR . " = ?", $strAttrCode);
		$objAttrSelect->where(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP . " = ?", $strAttrGrpCode);
		$objAttrSelect->where(Bf_Eav_Db_GroupAttributes::COL_IS_DELETED . " = ?", FALSE);

		$objAttrRow = $objAttr->fetchRow($objAttrSelect);

		$boolRetData = FALSE;

		if (empty($objAttrRow)) {
			$boolRetData = TRUE;
		}

		$this->view->data = $boolRetData;

	}

	public function entityTypesGrpAction () {

		$intEntityTypeId = (int) $this->getRequest()->getParam('EntTypeId');

		if (empty($intEntityTypeId)) {
			// :TODO ERROR...
		}

		// :TODO Maybe check that the Ent Type Valid...

		$objEntTypeGrp = new Bf_Eav_Db_EntitiesTypesGroups();
		$objEntTypeGrpSelect = $objEntTypeGrp->select(TRUE);

		$objEntTypeGrpSelect->where(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . '.' . Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES . " = ?", $intEntityTypeId);
		$objEntTypeGrpSelect->where(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . '.' . Bf_Eav_Db_EntitiesTypesGroups::COL_IS_DELETED . " = ?", FALSE);

		$arrOptions = array('caption' => '');
		$arrOptions['sortname'] = Bf_Eav_Db_EntitiesTypesGroups::COL_ORDER;
		$arrOptions['sortorder'] = Ingot_JQuery_JqGrid::SORT_ASC;

		$objGrid = new Ingot_JQuery_JqGrid('Groups', $objEntTypeGrpSelect, $arrOptions);
		$objGrid->setIdCol(Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES_GRP);
		$objGrid->setDblClkEdit(TRUE);
		$objGrid->setLocalEdit();

		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES_GRP));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES, array('editable' => true, 'editoptions' => array('defaultValue' => $intEntityTypeId), 'hidden' => true)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypesGroups::COL_GRP_LEGEND_CODE, array('editable' => true)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_EntitiesTypesGroups::COL_ORDER, array('editable' => true, '')));

		$objGridPager = $objGrid->getPager();
		$objGridPager->setDefaultAdd();
		$objGridPager->setDefaultEdit();
		$objGridPager->setDefaultDel();

		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());
		$this->view->objGrid = $objGrid->render();

		$arrButtons[] = array('module' => 'eav', 'controller' => 'index', "action" => "entity-types-attr", "name" => 'LBL_BUTTON_EAV_MNG_ATTR', 'params' => array('EntTypeId' => $intEntityTypeId));
		$arrButtons[] = array('module' => 'eav', 'controller' => 'index', "action" => "entity-types", "name" => 'LBL_BUTTON_EAV_MNG_ENT_TYPES');
		$this->view->arrActions = $arrButtons;
	}

	public function saveGroupAttribAction () {

		$textId = $this->getRequest()->getParam('id');

		$objDbTable = new Bf_Eav_Db_GroupAttributes();

		if (! empty($textId)) {
			$arrId = explode('_', $textId);
			$objRows = $objDbTable->find($arrId[0], $arrId[1]);
			if (! empty($objRows)) {
				$objRow = $objRows->current();
			} else {
				$objRow = array();
			}
		} else {
			if ("add" == $this->getRequest()->getParam("oper")) {

				$strAttrCode = $this->getRequest()->getParam(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR);
				$strAttrGrpCode = $this->getRequest()->getParam(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP);

				$objAttr = new Bf_Eav_Db_GroupAttributes();
				$objAttrSelect = $objAttr->select(TRUE);
				$objAttrSelect->where(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR . " = ?", $strAttrCode);
				$objAttrSelect->where(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP . " = ?", $strAttrGrpCode);
				$objRow = $objAttr->fetchRow($objAttrSelect);

				if (empty($objRow)) {
					$objRow = $objDbTable->createRow();
				}
				$objRow->{Bf_Eav_Db_GroupAttributes::COL_IS_DELETED} = 0;
			} else {
				$objController->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
				return;
			}
		}

		if (empty($objRow)) {
			$objController->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
			return;
		}

		if ("del" == $this->getRequest()->getParam("oper")) {
			if ($objRow->delete()) {
				// Deleted
				$this->view->data = array("code" => "ok", "msg" => "");
			} else {
				// Delete failed
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_DEL_FAIL"));
			}
		} else {
			if ($this->getRequest()->isPost()) {
				$arrData = $this->getRequest()->getPost();
				$objRow->setFromArray($arrData);

				$intId = $objRow->save();

				if (! empty($intId)) {
					$this->view->data = array("code" => "ok", "msg" => "");
				} else {
					$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
				}
			} else {
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));

			}
		}

		Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender();
		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();

		echo $this->view->json($this->view->data);
		exit();
	}

	public function attributesAction () {
		$this->view->objGrid = $this->getAllAttribGrid(NULL, TRUE);
	}

	public function getAttrSelectorAction () {
		$objForm = new Eav_Form_AttrSelector();
		$objForm->initForm();

		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		$this->view->objForm = $objForm;
	}

	public function getAttrFormAction () {
		$intAttrId = (int) $this->getRequest()->getParam(Bf_Eav_Db_Attributes::COL_ID_ATTR, 0);
		$mixAttrType = $this->getRequest()->getParam(Bf_Eav_Db_Attributes::COL_VALUE_TYPE);

		$objEav = new Bf_Eav();
		$objForm = $objEav->getAttrForm($intAttrId, $mixAttrType);

		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
		$this->view->objForm = $objForm;

		// Get Additional Information by jqGrid.
		$this->view->strAdditionalData = $objEav->getAttrExtraData($intAttrId, $mixAttrType);
	}

	public function saveAttrAction () {
		$intAttrId = (int) $this->getRequest()->getParam(Bf_Eav_Db_Attributes::COL_ID_ATTR, 0);
		$mixAttrType = $this->getRequest()->getParam(Bf_Eav_Db_Attributes::COL_VALUE_TYPE);

		$objEav = new Bf_Eav();
		$objForm = $objEav->getAttrForm($intAttrId, $mixAttrType);

		if ($this->getRequest()->isPost()) {
			$arrData = $this->getRequest()->getParams();

			if ($objForm->isValid($arrData)) {
				//Valid data
				if ($objEav->saveAttr($objForm->getValues())) {
					// Save OK
					$arrResponse['error_msg'] = '';
					$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = '';
					$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
				} else {
					// Save Failed
					// :TODO
				}
			} else {
				// Save Failed
				// Get all error messages...
				$arrErrors = array();
				$arrErrors = array_merge($arrErrors, $objForm->getMessages());

				$arrResponse['error_msg'] = $arrErrors;
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = $this->view->translate('LBL_ERROR_CATALOG_SAVE');
				$arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
			}
		}

		$this->view->arrData = $arrResponse;
	}

	public function saveAttribMultiValueAction () {
		$intAttrVal = (int) $this->getRequest()->getParam('id', 0);
		$intAttrId = (int) $this->getRequest()->getParam(Bf_Eav_Db_AttributesValues::COL_ID_ATTRIBUTES, 0);
		$intAttrValSortOrder = (int) $this->getRequest()->getParam(Bf_Eav_Db_AttributesValues::COL_SORT_ORDER, 99);
		$mixAttrVal = $this->getRequest()->getParam(Bf_Eav_Db_Values_Abstract::COL_VALUE);

		if (empty($intAttrId)) {
			$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
			return;
		}

		$objAttr = new Bf_Eav_Db_Attributes();
		$objAttrRowSet = $objAttr->find($intAttrId);

		if ($objAttrRowSet->count() == 0) {
			$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
			return;
		}
		$objAttrRow = $objAttrRowSet->current();

		if ( empty($intAttrVal) ) {
			if ( "add" == $this->getRequest()->getParam("oper") ) {
				if ( empty($mixAttrVal) ) {
					$this->view->data = array( "code" => "error", "msg" => $this->view->translate("Value is required and can't be empty"), );
					return;
				}

				// Find ID Value If Exists...
				$objEavValue = Bf_Eav_Value::factory($objAttrRow->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE});
				$strEavDbValueClassName = $objEavValue::getValuesDbClassName();
				$objEavDbValue = new $strEavDbValueClassName();
				$objValueSelect = $objEavDbValue->select(TRUE);
				$objValueSelect->where(Bf_Eav_Db_Values_Abstract::COL_VALUE . " = ?", $mixAttrVal);
				$objValueRow = $objEavDbValue->fetchRow($objValueSelect);

				if ( empty($objValueRow) ) {
					$objValueRow = $objEavDbValue->createRow();
					$objValueRow->{Bf_Eav_Db_Values_Abstract::COL_VALUE} = $mixAttrVal;
					if ( ! $objValueRow->save() ) {
						$objController->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
						return;
					}
				}

				$intAttrVal = $objValueRow->{Bf_Eav_Db_Values_Abstract::COL_ID_VALUES};

				// Now Find IF that kind of row allready exists...
				$objEavAttr = new Bf_Eav_Db_AttributesValues();
				$objEavAttrRowSet = $objEavAttr->find($intAttrId, $intAttrVal);

				if ($objEavAttrRowSet->count() > 0) {
					$objEavAttrRow = $objEavAttrRowSet->current();
				} else {
					// Create New
					$objEavAttrRow = $objEavAttr->createRow();
					$objEavAttrRow->{Bf_Eav_Db_AttributesValues::COL_ID_ATTRIBUTES} = $intAttrId;
					$objEavAttrRow->{Bf_Eav_Db_AttributesValues::COL_ID_VALUES} = $intAttrVal;
				}
				$objEavAttrRow->{Bf_Eav_Db_AttributesValues::COL_IS_DELETED} = 0;

				if ($objEavAttrRow->save()) {
					$this->view->data = array("code" => "ok", "msg" => "");
				} else {
					$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
				}
				return;

			} else {
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
				return;
			}
		}

		// Lets Get Attr Val Row
		$objEavAttr = new Bf_Eav_Db_AttributesValues();
		$objEavAttrRowSet = $objEavAttr->find($intAttrId, $intAttrVal);

		if ($objEavAttrRowSet->count() > 0) {
			$objRow = $objEavAttrRowSet->current();
		} else {
			$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
			return;
		}

		if ("del" == $this->getRequest()->getParam("oper")) {
			if ($objRow->delete()) {
				$data = array(Bf_Eav_Db_EntitiesValues::COL_IS_DELETED => 1,);
				$where[Bf_Eav_Db_EntitiesValues::COL_ID_VALUES.' = ?'] = $intAttrVal;
				$where[Bf_Eav_Db_EntitiesValues::COL_ID_ATTR.' = ?'] = $intAttrId;
				$n = Zend_Db_Table::getDefaultAdapter()->update(Bf_Eav_Db_EntitiesValues::TBL_NAME, $data, $where);

				// Deleted
				$this->view->data = array("code" => "ok", "msg" => "");
			} else {
				// Delete failed
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_DEL_FAIL"));
			}
		} else {
			if ($this->getRequest()->isPost()) {
				$arrData = $this->getRequest()->getPost();
				$objRow->setFromArray($arrData);

				$intId = $objRow->save();

				if (! empty($intId)) {
					$this->view->data = array("code" => "ok", "msg" => "");
				} else {
					$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
				}
			} else {
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
			}
		}
	}

	public function attribFilterAction () {

		$arrOptions = array('caption' => '');
		//$arrOptions['sortorder'] = Ingot_JQuery_JqGrid::SORT_ASC;
		$arrOptions['rowNum'] = 100;

		$objGrid = new Ingot_JQuery_JqGrid('AttribFilter', "Qstat_Db_Table_AttribFilter", $arrOptions);
		$objGrid->setIdCol(Qstat_Db_Table_AttribFilter::COL_ID_ATTRIB_FILTER);
		$objGrid->setLocalEdit();

		//User Column
		$objAttribute= new Bf_Eav_Db_Attributes();
		$objAttributeSelect=$objAttribute->getPairSelect();
		$arrAttriPairs = $objAttribute->getAdapter()->fetchPairs($objAttributeSelect);
		$arrGroupsPairs[0] = '';
		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($objGrid, $arrAttriPairs,
			array('index' => Qstat_Db_Table_AttribFilter::COL_ATRIBUTE_ID, 'useHaving' => true,"width"=>"100"));


		//User Column
		$objUsers= new User_Model_Db_Users();
		$objUsersSelect=$objUsers->getPairSelect();
		$arrUsersPairs = $objUsers->getAdapter()->fetchPairs($objUsersSelect);
		$arrGroupsPairs[0] = '';
		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($objGrid, $arrUsersPairs,
			array('index' => Qstat_Db_Table_AttribFilter::COL_ID_USER, 'useHaving' => true,"width"=>"100"),false);

		//Group Column
		$objGroups = new Qstat_Db_Table_Groups();
		$objGroupsSelect = $objGroups->getPairSelect();
		$arrGroupsPairs = $objGroups->getAdapter()->fetchPairs($objGroupsSelect);
		$arrGroupsPairs[0] = '';
		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($objGrid, $arrGroupsPairs,
			array('index' => Catalog_Model_CatalogData::COL_ID_GROUPS, 'useHaving' => true,"width"=>"100"),false);

		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_AttribFilter::COL_FILTER_BY, array('editable' => true)));

		$objGridPager = $objGrid->getPager();
		$objGridPager->setDefaultAdd();
		$objGridPager->setDefaultEdit();
		$objGridPager->setDefaultDel();
		//
		$arrToolbarFilter = array('triggerReload' => true);
		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter($arrToolbarFilter));

		$this->view->objGrid = $objGrid->render();
	}

}

