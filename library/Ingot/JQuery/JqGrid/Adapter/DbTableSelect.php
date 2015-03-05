<?php

/**
 * @see Zend_Paginator_Adapter_DbSelect
 */
require_once 'Zend/Paginator/Adapter/DbSelect.php';

/**
 * JqGrid DbTableSelect Adapter
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Adapter_DbTableSelect extends Ingot_JQuery_JqGrid_Adapter_DbSelect {
	public function getSelect() {
		return $this->_select;
	}
	
	public function gridSave(Zend_Controller_Request_Abstract $objRequest, Zend_View $objView) {
		
		$objDbTable = $this->getSelect ()->getTable ();
		$boolError = false;
		
		$intId = ( int ) $objRequest->getParam ( 'id' );
		
		if (! empty ( $intId )) {
			$objRows = $objDbTable->find ( $intId );
			if (! empty ( $objRows )) {
				$objRow = $objDbTable->find ( $intId )->current ();
			} else {
				$objRow = array ();
			}
		} else {
			if ("add" == $objRequest->getParam ( "oper" )) {
				$objRow = $objDbTable->createRow ();
			} else {
				$arrData = array ("code" => "error", "msg" => $this->view->translate ( "LBL_ERROR_UNAUTHORIZED" ) );
				$boolError = true;
			}
		}
		
		if (empty ( $objRow )) {
			$arrData = array ("code" => "error", "msg" => $this->view->translate ( "LBL_ERROR_UNAUTHORIZED" ) );
			$boolError = true;
		}
		
		if (! $boolError) {
			if ("del" == $objRequest->getParam ( "oper" )) {
				if ($objRow->delete ()) {
					// Deleted 
					$arrData = array ("code" => "ok", "msg" => "" );
				} else {
					// Delete failed
					$arrData = array ("code" => "error", "msg" => $this->view->translate ( "LBL_DEL_FAIL" ) );
					$boolError = true;
				}
			} else {
				if ($objRequest->isPost ()) {
					$arrData = $objRequest->getPost ();
					$objRow->setFromArray ( $arrData );
					
					$intId = $objRow->save ();
					
					if (! empty ( $intId )) {
						$arrData = array ("code" => "ok", "msg" => "" );
					} else {
						$arrData = array ("code" => "error", "msg" => $this->view->translate ( "LBL_UPDATE_FAIL" ) );
						$boolError = true;
					}
				} else {
					$arrData = array ("code" => "error", "msg" => $this->view->translate ( "LBL_UPDATE_FAIL" ) );
					$boolError = true;
				
				}
			}
		}
		
		Zend_Controller_Action_HelperBroker::getStaticHelper ( 'viewRenderer' )->setNoRender ();
		Zend_Controller_Action_HelperBroker::getStaticHelper ( 'layout' )->disableLayout ();
		
		echo $objView->json ( $arrData );
	
	}

}