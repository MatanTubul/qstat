<?php
/**
 * ManagmentController
 * 
 * @author
 * @version 
 */
require_once 'Zend/Controller/Action.php';

class Catalog_LockscriptController extends Zend_Controller_Action
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
        $arrOptions = array('caption' => '');
        $arrOptions['rowNum'] = 100;
        $objGrid = new Ingot_JQuery_JqGrid('LockScriptsGrid', "Qstat_Db_Table_LockManagment", $arrOptions);
        $objGrid->setIdCol(Qstat_Db_Table_LockManagment::COL_ID_LOCK_MNG);
        $objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_LockManagment::COL_LOCK_NAME));
        $objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Qstat_Db_Table_LockManagment::COL_LOCK_EAV_TYPE));
        $objGridPager = $objGrid->getPager();
        $objGridPager->setDefaultDel();
        $objGrid->registerPlugin(
        new Ingot_JQuery_JqGrid_Plugin_CustomButton(
        array("caption" => "", "title" => "Edit Selected Lock", "buttonicon" => "ui-icon-pencil", "onClickButton" => "function(){ getForm(false,null); }", "position" => "first")));
        $objGrid->registerPlugin(
        new Ingot_JQuery_JqGrid_Plugin_CustomButton(array("caption" => "", "title" => "Add Lock", "buttonicon" => "ui-icon-plus", "onClickButton" => "function(){ addRow(); }", "position" => "first")));
        $objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());
        $this->view->objGrid = $objGrid->render();
    }

    public function getEntSelectorAction ()
    {
        $objForm = new Catalog_Form_EntSelector();
        $objForm->initForm();
        Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
        $this->view->objForm = $objForm;
    }

    public function getFormAction ()
    {
        $intLockId = $this->getRequest()->getParam(Qstat_Db_Table_LockManagment::COL_ID_LOCK_MNG, 0);
        $objCatalog = new Bf_Catalog($this->_options);
        if (! empty($intLockId)) {
            //Existing Lock item
            $objLock = new Qstat_Db_Table_LockManagment();
            $objRowSet = $objLock->find($intLockId);
            if ($objRowSet->count() > 0) {
                $objRow = $objRowSet->current();
                $intEntType = (int) $objRow->{Qstat_Db_Table_LockManagment::COL_LOCK_EAV_TYPE};
            } else {
                //TODO: handle error, hack attempt
                throw new Bf_Exception();
            }
            $strOper = 'edit';
            $objLockParams = new Qstat_Db_Table_LockManagmentParams();
            $objLockParamsSelect = $objLockParams->select(TRUE)->setIntegrityCheck(FALSE);
            $objLockParamsSelect->join(Bf_Eav_Db_Attributes::TBL_NAME, Bf_Eav_Db_Attributes::TBL_NAME.'.'.Bf_Eav_Db_Attributes::COL_ID_ATTR." = ".Qstat_Db_Table_LockManagmentParams::TBL_NAME.'.'.Qstat_Db_Table_LockManagmentParams::COL_LOCK_PARAM_ATTRIB_ID, array(Bf_Eav_Db_Attributes::COL_ATTR_CODE));
            $objLockParamsSelect->where(Qstat_Db_Table_LockManagmentParams::TBL_NAME.'.'.Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG . " = ?", $intLockId);
            $objLockParamsSelect->where(Qstat_Db_Table_LockManagmentParams::TBL_NAME.'.'.Qstat_Db_Table_LockManagmentParams::COL_IS_DELETED . " = ?", FALSE);
            $objLockParamsSelect->where(Bf_Eav_Db_Attributes::TBL_NAME.'.'.Bf_Eav_Db_Attributes::COL_IS_DELETED . " = ?", FALSE);
            $this->view->objLockParamsRowSet = $objLockParams->fetchAll($objLockParamsSelect);
//            $arrForms = $objCatalog->getItemForm($intEntType);
            $objParamLockForm = new Catalog_Form_LockParam();
            $objParamLockForm->setScriptId($intLockId);
            $objParamLockForm->setParamElements($intEntType);
            $this->view->objParamForm = $objParamLockForm;
        } else {
            //New Lock Item
            $intEntType = (int) $this->getRequest()->getParam(Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES, 0);
            $intLockId = 0;
            $strOper = 'add';
        }
        $objGeneralLockForm = new Catalog_Form_LockMng();
        $objGeneralLockForm->setOperator($strOper);
        $objGeneralLockForm->setEavType($intEntType);
        if (! empty($objRow)) {
            $objGeneralLockForm->populate($objRow->toArray());
        }
        $this->view->objForm = $objGeneralLockForm;
        $this->view->intLockId = $intLockId;
        Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
    }

    public function saveLockAction ()
    {
        $arrParams = $this->getRequest()->getParams();
        $intId = $arrParams[Qstat_Db_Table_LockManagment::COL_ID_LOCK_MNG];
        $objForm = new Catalog_Form_LockMng();
        $objDbTable = new Qstat_Db_Table_LockManagment();
        if (empty($intId)) {
            if ("add" == $this->getRequest()->getParam("oper")) {
                $objRow = $objDbTable->createRow();
                $objRow->setFromArray($arrParams);
                $objRow->{Bf_Eav_Db_GroupAttributes::COL_IS_DELETED} = FALSE;
            } else {
                $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
                return;
            }
        } else {
            $objRows = $objDbTable->find($intId);
            if (! empty($objRows)) {
                $objRow = $objRows->current();
            } else {
                $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
                return;
            }
        }
        if ("del" == $this->getRequest()->getParam("oper")) {
            if ($objRow->delete()) {
                // Deleted 
                $this->view->arrData = array("code" => "ok", "msg" => "");
            } else {
                // Delete failed
                $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_DEL_FAIL"));
            }
        } else {
            if ($this->getRequest()->isPost()) {
                $arrData = $this->getRequest()->getPost();
                $objRow->setFromArray($arrData);
                $intId = $objRow->save();
                if (! empty($intId)) {
                    $this->view->arrData = array("code" => "ok", "msg" => "");
                } else {
                    $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
                }
            } else {
                $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
            }
        }
    
     //$this->view->json($this->view->data);
    }

    public function saveLockParamAction ()
    {
        $arrParams = $this->getRequest()->getParams();
        if (! empty($arrParams[Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG])) {
            $objLockParams = new Qstat_Db_Table_LockManagmentParams();
            $objLockParamsRow = $objLockParams->createRow();
            $objLockParamsRow->setFromArray($arrParams);
            if ($objLockParamsRow->save()) {
                $this->view->arrData = array("code" => "ok", "msg" => "");
            } else {
                $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
            }
        } else {
            $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
        }
    }

    public function delLockParamAction ()
    {
        $arrParams = $this->getRequest()->getParams();
        if (! empty($arrParams[Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG_PARAM])) {
            $objLockParams = new Qstat_Db_Table_LockManagmentParams();
            $objLockParamsRowSet = $objLockParams->find($arrParams[Qstat_Db_Table_LockManagmentParams::COL_ID_LOCK_MNG_PARAM]);
            if ($objLockParamsRowSet->count() > 0) {
                $objLockParamsRow = $objLockParamsRowSet->current();
                if ($objLockParamsRow->delete()) {
                    $this->view->arrData = array("code" => "ok", "msg" => "");
                } else {
                    $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
                }
            } else {
                $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
            }
        } else {
            $this->view->arrData = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
        }
    }
}
