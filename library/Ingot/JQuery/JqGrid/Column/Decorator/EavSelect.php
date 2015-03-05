<?php

/**
 * @see Ingot_JQuery_JqGrid_Column_Decorator_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Column/Decorator/Abstract.php';

/**
 * Decorate a column which contains a currency
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Alex (Shurf) Frenkel
 */

class Ingot_JQuery_JqGrid_Column_Decorator_EavSelect extends Ingot_JQuery_JqGrid_Column_Decorator_Eav
{
     
	public function unformatValue($strValue, $strRule) {
	    
	    $arrOperators = Ingot_JQuery_JqGrid::$searchOperators;
	    $arrSearchPrefixes = Ingot_JQuery_JqGrid_Adapter_DbSelect::$arrOperator;
	    
	    $mixValue = $this->createSelectValue($this->getIntAttrId(),$strValue);
    
		return $mixValue;
	}

}