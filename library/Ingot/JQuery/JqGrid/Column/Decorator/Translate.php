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
class Ingot_JQuery_JqGrid_Column_Decorator_Translate extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract {
    protected $_options = array();
    /**
     * Decorate column to display URL links
     * Empty
     * 
     * @return void
     */
    public function decorate () {}
    public function cellValue ($row) {
        $strRawCellValue = parent::cellValue($row);
        if (Zend_Registry::isRegistered('Zend_Translate')) {
            $objTranslate = Zend_Registry::get('Zend_Translate');
            if (! empty($objTranslate)) {
                $strReturnValue = $objTranslate->translate($strReturnValue);
            }
        } else {
            $strReturnValue = $strReturnValue;
        }
        return $strReturnValue;
    }
}