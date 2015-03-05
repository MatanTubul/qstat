<?php
/**
 * LabelsController
 *
 * @author
 * @version
 */

require_once 'Zend/Controller/Action.php';

class Labels_IndexController extends Zend_Controller_Action
{

	public function init(){

		$this->view->headScript ()->appendFile ( '/js/ckeditor/ckeditor.js', 'text/javascript' );
	}


    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $objSystemMessages = new Labels_Model_Db_SystemLabels();
        $objSystemMessagesSelect = $objSystemMessages->select();

        $strUrl = $this->view->url(array('module' => 'labels', 'controller' => 'index', 'action' => 'edit'), null, false, true);

        $arrOptions = array("hiddengrid" => false, "editurl" => $strUrl);

        $arrOptions['plugin']['pager']['edit']['beforeShowForm'] = " function(form) { $('#" . Labels_Model_Db_SystemLabels::COL_ID_SYSTEM . "', form).attr('disabled','disabled');  } ";
        $arrOptions['rowNum'] = 100;

        $grid = new Ingot_JQuery_JqGrid('systemmessages', new Ingot_JQuery_JqGrid_Adapter_DbTableSelect($objSystemMessagesSelect), $arrOptions);
        $grid->setIdCol(Labels_Model_Db_SystemLabels::COL_ID_SYSTEM);

        $objPlugin = $grid->getPager();
        $objPlugin->setDefaultAdd();
        $objPlugin->setDefaultEdit();

        $grid->addColumn(new Ingot_JQuery_JqGrid_Column(Labels_Model_Db_SystemLabels::COL_ID_SYSTEM, array("editable" => true)));
        $grid->addColumn(new Ingot_JQuery_JqGrid_Column(Labels_Model_Db_SystemLabels::COL_CONTENT, array("editable" => true)));

        $grid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());
        $this->view->grid = $grid->render(null, array('DblClkEdit' => TRUE));

        $objForm = new Labels_Form_UploadBkp();
        $this->view->objForm = $objForm;

        // Buttons set
        $arrActions = array();

        $arrActions[] = array('module' => 'labels', 'controller' => 'index', "action" => "savebackup", "name" => 'LBL_SAVE_BKP_FILE');
        $arrActions[] = array('module' => 'labels', 'controller' => 'index', "action" => "loadbackup", "name" => 'LBL_LOAD_BKP_FILE', 'onClick' => '$("#uploader").dialog("open")');

        $this->view->arrActions = $arrActions;
    }

    public function editAction ()
    {

        $intId = $this->_request->getParam('id');

        // Get model object
        $objSystemSettings = new Labels_Model_Db_SystemLabels();

        if (! empty($intId)) {
            $objRowSet = $objSystemSettings->find($intId);

            if ($objRowSet->count() > 0) {
                $objRow = $objRowSet->current();
            } else {
                $this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
                return;
            }
        } else {
            $this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
            return;
        }

        if ("del" == $this->_request->getParam("oper")) {
            if ($objRow->delete()) {
                // Deleted
                $this->view->data = array("code" => "ok", "msg" => "");
            } else {
                // Delete failed
                $this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_DEL_FAIL"));
            }
        } else {
            if ($this->_request->isPost()) {
                $arrData = $this->_request->getPost();
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
        Labels_Model_SystemLabels::initLables(TRUE);
    }

    public function savebackupAction ()
    {
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender();
        Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
        $objTable = new Labels_Model_Db_SystemLabels();
        $objSelect = $objTable->select(TRUE);
        $objConfig = new Zend_Config($objTable->fetchAll($objSelect)->toArray());
        $writer = new Zend_Config_Writer_Array(array('config' => $objConfig, 'filename' => APPLICATION_PATH . '/configs/backup/labels.php'));
        try {
            $writer->write();
            Labels_Model_SystemLabels::setJgrowlMessage('LBL_MENU_SAVE_OK');
        } catch (Exception $objEx) {
            Labels_Model_SystemLabels::setJgrowlMessage('LBL_MENU_SAVE_FAILED');
        }
        $strUrl = $this->view->url(array('module' => 'labels', 'controller' => 'index', 'action' => 'index'), null, true);
        $this->_redirect($strUrl);
    }

    public function loadbackupAction ()
    {
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender();
        Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();

        $form = new Menu_Form_UploadMenu();

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {

                // success - do something with the uploaded file
                if ($form->menuFile->isUploaded()) {
                    // foo file given... do something
                    $fullFilePath = $form->getValues();
                    $fullFilePath = $form->menuFile->getFileName();
                    Zend_Debug::dump($fullFilePath);

                    $objConfig = new Zend_Config(require $fullFilePath);

                    Zend_Debug::dump($objConfig->toArray());
                    $objMenuTable = new Labels_Model_Db_SystemLabels();

                    foreach ($objConfig as $objConfigMenuRow) {
                        $objMenuRowSet = $objMenuTable->find($objConfigMenuRow->{Labels_Model_Db_SystemLabels::COL_ID_SYSTEM});

                        if ($objMenuRowSet->count() > 0) {
                            $objMenuRow = $objMenuRowSet->current();
                        } else {
                            // Add New
                            $objMenuRow = $objMenuTable->createRow();
                        }
                        $objMenuRow->setFromArray($objConfigMenuRow->toArray());
                        $objMenuRow->save();
                    }
                }

            } else {
                $form->populate($formData);
            }
        }

        $strUrl = $this->view->url(array('module' => 'labels', 'controller' => 'index', 'action' => 'index'), null, true);
        $this->_redirect($strUrl);
    }

    public function editMailAction ()
    {

        $intSubjCode = $this->_request->getParam('SubjCode');
        $intTextCode = $this->_request->getParam('TextCode');

        $arrData = array();
        $objSystemSettings = new Labels_Model_Db_SystemLabels();

        $objSubjectRowSet = $objSystemSettings->find($intSubjCode);
        if ($objSubjectRowSet->count() > 0) {
            $objSubjectRow = $objSubjectRowSet->current();
            $arrData['Subject'] = $objSubjectRow->{Labels_Model_Db_SystemLabels::COL_CONTENT};
        } else {
            $objSubjectRow = $objSystemSettings->createRow();
            $objSubjectRow->{Labels_Model_Db_SystemLabels::COL_ID_SYSTEM} = $intSubjCode;
        }

        $objContentRowSet = $objSystemSettings->find($intTextCode);
        if ($objContentRowSet->count() > 0) {
            $objContentRow = $objContentRowSet->current();
            $arrData['Content'] = $objContentRow->{Labels_Model_Db_SystemLabels::COL_CONTENT};
        } else {
            $objContentRow = $objSystemSettings->createRow();
            $objContentRow->{Labels_Model_Db_SystemLabels::COL_ID_SYSTEM} = $intTextCode;
        }

        $form = new Labels_Form_Mail();
        $form->populate($arrData);

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {

                $objSubjectRow->{Labels_Model_Db_SystemLabels::COL_CONTENT} = $formData['Subject'];
                $objSubjectRow->save();

                $objContentRow->{Labels_Model_Db_SystemLabels::COL_CONTENT} = $formData['Content'];
                $objContentRow->save();

            } else {
                $form->populate($formData);
            }
        }
        $this->view->form = $form;

        $arrButtons[] = array('module' => 'user', 'controller' => 'authentication', "action" => "login", "onClick" => '$("#' . $form->getAttrib('id') . '").submit();', "name" => 'LBL_BUTTON_SAVE');
        $this->view->arrActions = $arrButtons;

    }

    public function passwordMailAction ()
    {
        $this->getRequest()->setParam('SubjCode', 'LBL_SUBJECT_EMAIL_NEW_PASSWORD');
        $this->getRequest()->setParam('TextCode', 'LBL_TEXT_EMAIL_NEW_PASSWORD');

        $this->_forward('edit-mail');
    }

    public function unlockNotifyMailAction ()
    {
        $this->getRequest()->setParam('SubjCode', 'LBL_SUBJECT_EMAIL_LOCK_END_NOTIFICATION');
        $this->getRequest()->setParam('TextCode', 'LBL_TEXT_EMAIL_LOCK_END_NOTIFICATION');

        $this->_forward('edit-mail');
    }

    public function unlockMailAction ()
    {
        $this->getRequest()->setParam('SubjCode', 'LBL_SUBJECT_EMAIL_LOCK_ENDED');
        $this->getRequest()->setParam('TextCode', 'LBL_TEXT_EMAIL_LOCK_ENDED');

        $this->_forward('edit-mail');
    }

    public function createdScheduledLockAction ()
    {

        $this->getRequest()->setParam('SubjCode', 'LBL_SUBJECT_EMAIL_CREATED_SCHEDULED_LOCK');
        $this->getRequest()->setParam('TextCode', 'LBL_TEXT_EMAIL_CREATED_SCHEDULED_LOCK');

        $this->_forward('edit-mail');
    }

    public function startedScheduledLockAction ()
    {
        $this->getRequest()->setParam('SubjCode', 'LBL_SUBJECT_EMAIL_STARTED_SCHEDULED_LOCK');
        $this->getRequest()->setParam('TextCode', 'LBL_TEXT_EMAIL_STARTED_SCHEDULED_LOCK');

        $this->_forward('edit-mail');
    }

}