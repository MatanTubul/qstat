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

class Ingot_JQuery_JqGrid_Column_Decorator_StripHtml extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract {
	protected $_options = array ();
	
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
		$strRawCellValue = html_entity_decode($strRawCellValue , ENT_COMPAT, "UTF-8");
		
		$strReturnValue = "";
		
		$allowedTags = array ();
		
		// allow only the href attribute to be used in the above tags 
		// (which should only be within the <a> tag anyway)
		$allowedAttributes = array ();
		
		// create an instance of Zend_Filter_StripTags to use
		$stripTags = new Zend_Filter_StripTags ( $allowedTags, $allowedAttributes );
		
		// now filter the string
		$strReturnValue = $stripTags->filter ( $strRawCellValue );
		$strReturnValue = trim( preg_replace( '/\s+/', "\n", $strReturnValue ) );  
		
		return $strReturnValue;
	}

}