<?php

/**
 * @see Ingot_JQuery_JqGrid_Column_Decorator_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Column/Decorator/Abstract.php';

/**
 * Decorate a column which contains a date
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Column_Decorator_Date extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract {
	protected $_options = array ();
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct($column, $options = array()) {
		parent::__construct ( $column, $options );
	}
	
	/**
	 * Decorate column to display URL links
	 * 
	 * @return void
	 */
	public function decorate() {
		
		if (count ( $this->_options ) == 0) {
			$this->_options ['srcformat'] = 'Y-m-d H:i:s';
			$this->_options ['newformat'] = 'l, F d, Y';
		}
		
		$arrSearchOptions = array ();
		
		if (! empty ( $this->_options ['datepicker'] )) {
			$this->_options ['dateFormat'] = $this->_options ['datepicker'];
			
			$arrSearchOptions = array (); //
			$arrSearchOptions ["dataInit"] = new Zend_Json_Expr ( "function(el){\$(el).datepicker({dateFormat:' " . $this->_options ['newformat'] . " ',  onClose: function(dateText, inst) { var sgrid = $('#" . $this->getGridId () . "')[0]; sgrid.triggerToolbar(); }});}" );
			
			// 	add to plugin...
			$this->_objGrid->getPager ()->addOnInitialiseForm ( 'jQuery("#' . $this->_column->getName () . "\",formid).datepicker(" . ZendX_JQuery::encodeJson ( $this->_options ) . "); jQuery('#" . $this->_column->getName () . "',formid).attr('readOnly', true); " );
			
			unset ( $this->_options ['datepicker'] );
		}
		
		$this->_column->setOption ( 'formatter', 'date' );
		$this->_column->setOption ( 'formatoptions', $this->_options );
		$this->_column->setOption ( 'searchoptions', $arrSearchOptions );
	
	}
	
	public function cellValue($row) {
		$strRawCellValue = parent::cellValue ( $row );
		
		$strReturnDate = "";
		if (! empty ( $strRawCellValue )) {
			$strTime = $strRawCellValue;
			
			if (!empty($this->_options ['dataformat']) && ('DateTime' == $this->_options ['dataformat'])) {
				
				$date = new DateTime ( $strTime );
			
			} else {
				$date = new DateTime ();
				$date->setTimestamp ( $strTime );
			
			}
			
			if (! empty ( $this->_options ['srcformat'] )) {
				$strDateFormat = $this->_options ['srcformat'];
			} else {
				$strDateFormat = 'Y-m-d';
			}
			
			$strReturnDate = $date->format ( $strDateFormat );
		}
		$strReturnDate = trim ( $strReturnDate );
		return $strReturnDate;
	}
	
	public function unformatValue($strValue) {
		$strValue = trim ( $strValue );
		switch ($this->_options ['datepicker']) {
			case "dd/mm/yy" :
			default :
				$strTimestamp = mktime ( 0, 0, 1, substr ( $strValue, 3, 2 ), substr ( $strValue, 0, 2 ), substr ( $strValue, 6, 4 ) );
				
				break;
		}
		return date ( $this->_options ['informat'], $strTimestamp );
	}

}