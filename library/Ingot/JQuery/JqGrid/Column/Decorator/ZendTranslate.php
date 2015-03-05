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

class Ingot_JQuery_JqGrid_Column_Decorator_ZendTranslate extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract {
	protected $_options = array ();
	
	/**
	 * 
	 * Zend Translate Object
	 * 
	 * @var Zend_Translate
	 */
	protected $_objZendTranslate;
	
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
	 * Empty
	 * 
	 * @return void
	 */
	public function decorate() {
	
	}
	
	public function cellValue($row) {
		$strRawCellValue = parent::cellValue ( $row );
		
		$strReturnValue = "";
		
		$strReturnValue = $this->getGrid ()->getView ()->translate ( $strRawCellValue );
		
				
		if ($this->getZendTranslateObject()) {
			$strValue = $this->getZendTranslateObject()->translate( $strValue );
		}		
		
		return $strReturnValue;
	}
	
	public function unformatValue($strValue) {
		$strValue = trim ( $strValue );		
		if ($this->getZendTranslateObject()) {
			$strValue = $this->getZendTranslateObject()->getMessageId ( $strValue );
		}		
		return $strValue;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return Zend_Translate_Adapter
	 */
	protected function getZendTranslateObject() {		
		if (! empty ( $this->_objZendTranslate )) {
			if (Zend_Registry::isRegistered ( 'Zend_Translate' )) {
				$this->_objZendTranslate = Zend_Registry::get ( 'Zend_Translate' );
			}
		}		
		return $this->_objZendTranslate;	
	}

}