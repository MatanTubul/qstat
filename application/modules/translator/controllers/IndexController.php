<?php
/**
 * LabelsController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class Translator_IndexController extends Zend_Controller_Action {
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$objSystemMessages = new Translator_Model_Db_Translation ();
		$objSystemMessagesSelect = $objSystemMessages->select ();
		
		$strUrl = $this->view->url ( array ('module' => 'translator', 'controller' => 'index', 'action' => 'edit' ), null, false, true );
		
		$arrOptions = array ("hiddengrid" => false, "editurl" => $strUrl );

//		$arrOptions ['plugin'] ['pager'] ['edit'] ['beforeShowForm'] = " function(form) { $('#".Labels_Model_Db_SystemLabels::COL_ID_SYSTEM."', form).attr('disabled','disabled');  } ";
		
		$grid = new Ingot_JQuery_JqGrid ( 'translator', new Ingot_JQuery_JqGrid_Adapter_DbTableSelect ( $objSystemMessagesSelect ), $arrOptions );
		$grid->setIdCol ( Translator_Model_Db_Translation::COL_ID_SYSTEM );
				
		$objPlugin = $grid->getPager ();
		$objPlugin->setDefaultAdd ();
		$objPlugin->setDefaultEdit ();
		
		$grid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Translator_Model_Db_Translation::COL_ID_SYSTEM, array ("editable" => true ) ) );
		$grid->addColumn ( new Ingot_JQuery_JqGrid_Column ( Translator_Model_Db_Translation::COL_CONTENT, array ("editable" => true ) ) );
		
		$grid->registerPlugin ( new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter () );		
		$this->view->grid = $grid->render (null, array('DblClkEdit'=>TRUE));
		
		$objForm = new Labels_Form_UploadBkp();
		$this->view->objForm = $objForm;
		
		// Buttons set
		$arrActions = array ();
		
		$this->view->arrActions = $arrActions;
	}
	
	public function editAction() {
		
		$intId = $this->_request->getParam ( 'id' );
		
		// Get model object
		$objSystemSettings = new Translator_Model_Db_Translation ();
		
		if (! empty ( $intId ) && !($intId === '_empty')) {
		    $objSelect = $objSystemSettings->select(TRUE);
		    $objSelect->where(Translator_Model_Db_Translation::COL_ID_SYSTEM." = ?",$intId);
			$objRowSet = $objSystemSettings->fetchAll($objSelect);
			
			if ($objRowSet->count() > 0){
				$objRow = $objRowSet->current();
			} else {
				$this->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_ERROR_UNAUTHORIZED" ) );
				return;
			}			
		} else {
		    $objRow = $objSystemSettings->createRow();			
		}
		
		if ("del" == $this->_request->getParam ( "oper" )) {
			if ($objRow->delete ()) {
				// Deleted 
				$this->view->data = array ("code" => "ok", "msg" => "" );
			} else {
				// Delete failed
				$this->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_DEL_FAIL" ) );
			}
		} else {
			if ($this->_request->isPost ()) {
				$arrData = $this->_request->getPost ();
				$objRow->setFromArray ( $arrData );
				$intId = $objRow->save ();
				if (! empty ( $intId )) {
					$this->view->data = array ("code" => "ok", "msg" => "" );
				} else {
					$this->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_UPDATE_FAIL" ) );
				}
			} else {
				$this->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_UPDATE_FAIL" ) );
			
			}
		}
	}
	
}