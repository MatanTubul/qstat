<?php

/**
 * @see Ingot_JQuery_JqGrid_Column_Decorator_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Column/Decorator/Abstract.php';

/**
 * Decorate a column which contains Search Select in search toolbar
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Alex Frenkel
 */

class Ingot_JQuery_JqGrid_Column_Decorator_Search_MultiSelect extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract {
	protected $_options = array ();
	
	//	protected $_name = 
	

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct($column, $options = array()) {
		if (empty ( $options ['value'] )) {
			throw new Ingot_JQuery_JqGrid_Exception ( "Value mast be set for select", - 3 );
		}
		$this->_column = $column;		
		parent::__construct ( $column, $options );	
	}
	
	private function _preDecorate() {
		$this->_column->setOption ( 'stype', 'select' );
		$this->_column->setOption ( 'search', TRUE );
		$this->_column->setOption ( 'attr', array ("multiple" => "multiple" ) );
		$this->_column->setOption ( 'sopt', array ('in' ) );
		
//		$strMultiSelectClose = '{close: function(){ $("#' . $this->_objGrid->getId () . '")[0].triggerToolbar();  $(this).trigger(\'change\'); }}';
		$strMultiSelectClose = '{close: function(){  $(this).trigger(\'change\'); }}';
		
		
		$this->setOptions ( array ('dataInit' => 'function(elem) { setTimeout(function(){$(elem).multiselect(' . $strMultiSelectClose . ');}); }' ) );
		$strBaseUrl = $this->_objGrid->getView ()->baseUrl ();
		$strJsUrl = $strBaseUrl . '/js/';
		$this->_objGrid->getView ()->headScript ()->appendFile ( $strJsUrl . 'ui.multiselect.js', 'text/javascript' );
		$this->_objGrid->getView ()->headLink ()->appendStylesheet ( $strJsUrl . 'ui.multiselect.css' );
		
		// Create On Grid Compleate
		$objOnGridComplete = $this->_objGrid->getOption ( 'gridComplete' );
		$strGridComplete = $this->_createJs ( $this->getOption ( 'name' ), $objOnGridComplete );
		
		$this->_objGrid->setOption ( 'gridComplete', $strGridComplete );
	}
	
	/**
	 * Decorate column to search select
	 * 
	 * @return void
	 */
	public function decorate() {
		
		$this->_preDecorate ();
		
		$this->_options ['sopt'] = array ('in' );
		
		$this->_options ['attr'] ['multiple'] = 'multiple';
				
		$strData['multiple'] = 'multiple';
		
		$strData ['value'] = "";
		
		foreach ( $this->_options ['value'] as $strKey => $strValue ) {
			if (! empty ( $strData ['value'] )) {
				$strData ['value'] .= ";";
			}
			$strData ['value'] .= $strKey . ":" . $strValue;
		}
		
		if (! empty ( $this->_options ['sopt'] )) {
			$strData ['sopt'] = $this->_options ['sopt'];
		}
		
		if (! empty ( $this->_options ['defaultValue'] )) {
			$strData ['defaultValue'] = $this->_options ['defaultValue'];
		}
		
		if (! empty ( $this->_options ['dataUrl'] )) {
			$strData ['dataUrl'] = $this->_options ['dataUrl'];
			if (! empty ( $this->_options ['defaultValue'] )) {
				$strData ['buildSelect'] = $this->_options ['buildSelect'];
			}
		
		}
		
		if (! empty ( $this->_options ['dataInit'] )) {
			$strData ['dataInit'] = $this->_options ['dataInit'];
		}
		
		if (! empty ( $this->_options ['dataEvents'] )) {
			$strData ['dataEvents'] = $this->_options ['dataEvents'];
		}
		
		if (! empty ( $this->_options ['attr'] )) {
			$strData ['attr'] = $this->_options ['attr'];
		}
		
		if (! empty ( $this->_options ['searchhidden'] )) {
			$strData ['searchhidden'] = $this->_options ['searchhidden'];
		}
		$this->_column->setOption ( 'searchoptions', $strData );
	
	}
	
	private function _createJs($strName, $objOnGridComplete) {
		
		if (! empty ( $objOnGridComplete )) {
			if ($objOnGridComplete instanceof Zend_Json_Expr) {
				$strGridComplete = $objOnGridComplete->__toString ();
			} else {
				$strGridComplete = $objOnGridComplete;
			}
		} else {
			$strGridComplete = 'function(){ }';
		}
		$strGridComplete = trim ( $strGridComplete );
		
		$strNewGridComplete = preg_replace_callback ( '/function\(\){(.*)}/', array ($this, 'pregCallback' ), $strGridComplete );
		
		return 'function(){ ' . $strNewGridComplete . ' }';
	}
	
	public function pregCallback(&$matches) {
		
		$strMultiSelectClose = '{close: function(){ $("#' . $this->_objGrid->getId () . '")[0].triggerToolbar(); }}';
		
		//		return $matches[1].'$("#gs_' . $this->getName() . '").multiselect('.$strMultiSelectClose.');	$("#gs_' . $this->getName() . '").parent().css("position","inherit");';
		return $matches [1] . '	$("#gs_' . $this->getName () . '").parent().css("position","inherit");';
	}
	
	public function unformatValue($strValue) {
		$strValue = trim ( $strValue );
		
		if (empty ( $strValue )) {
			return FALSE;
		}
		
		if (',' == substr ( $strValue, 0, 1 )) {
			$strValue = substr ( $strValue, 1 );
		}
		
		$arrValue = explode ( ',', $strValue );
		
		return $arrValue;
	}
}

