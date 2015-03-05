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

class Ingot_JQuery_JqGrid_Column_Decorator_DateTime extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract {
	protected $_options = array ();
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct($column, $options = array()) {
		$this->_options = $options;
		parent::__construct ( $column );
	
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
			$arrSearchOptions = array (); //
			$this->_options ['dateFormat'] =  $this->_options ['datepicker'];
			
			$strBaseUrl = $this->_objGrid->getView ()->baseUrl ();
			$strJsUrl = $strBaseUrl . '/js/';
						
			$this->_objGrid->getView ()->headScript ()->appendFile ( $strJsUrl . 'jquery-ui-timepicker-addon.js', 'text/javascript' );
			
			$arrSearchOptions ["dataInit"] = new Zend_Json_Expr ( "function(el){\$(el).datetimepicker({dateFormat:' " . $this->_options ['datepicker'] . " ',  onClose: function(dateText, inst) { var sgrid = $('#" . $this->getGridId () . "')[0]; sgrid.triggerToolbar(); }});}" );
			
			//dateFormat:' " . $this->_options ['datepicker'] . " ', clickInput:true, createButton:false
			
			// 	add to plugin...
			$this->_objGrid->getPager ()->addOnInitialiseForm (  'jQuery("#' . $this->_column->getName () . "\",formid).datetimepicker(".ZendX_JQuery::encodeJson($this->_options )."); jQuery('#" . $this->_column->getName () . "',formid).attr('readOnly', true); " );

			unset ( $this->_options ['datepicker'] );
		}
		
		$this->_column->setOption ( 'formatter', 'date' );
		$this->_column->setOption ( 'formatoptions', $this->_options );
		$this->_column->setOption ( 'searchoptions', $arrSearchOptions );
	
		// add to plugin...
	

	// Load View onload... ???
	

	/* css for timepicker */
	/*
.ui-timepicker-div .ui-widget-header{ margin-bottom: 8px; }
.ui-timepicker-div dl{ text-align: left; }
.ui-timepicker-div dl dt{ height: 25px; }
.ui-timepicker-div dl dd{ margin: -25px 0 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }*/
	
	}
	
	public function cellValue($row) {
		$strRawCellValue = parent::cellValue ( $row );
		
		$strReturnDate = "";
		if (! empty ( $strRawCellValue )) {
			$strTime = $strRawCellValue;
			
			if (! empty ( $this->_options ['srcformat'] )) {
				$strDateFormat = $this->_options ['srcformat'];
			} else {
				$strDateFormat = 'Y-m-d H:i:s';
			}

			
			$date = new DateTime();
			$date->setTimestamp($strTime);
			$strReturnDate =  $date->format($strDateFormat);
			//$strReturnDate = date ( $strDateFormat, $strTime );
		}
		
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