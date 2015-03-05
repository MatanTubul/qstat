<?php

/**
 * @see Ingot_JQuery_JqGrid_Column_Decorator_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Column/Decorator/Abstract.php';

/**
 * Decorate a column which contains HTML links
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Column_Decorator_ColorTitle extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract
{
    protected $_options = array();

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct($column, $options = array())
    {
        $this->_column = $column;
        $this->_options = $options;
        
        $this->decorate();
    }

    /*
	 * Decorate column to display URL links
	 * 
	 * @return void
	 */
    public function decorate()
    {
    	if (!isset($this->_options['column']))  {
        	$this->_options['column'] = array();
        }	elseif ( (isset($this->_options['column']) && ! is_array($this->_options['column']))) {
            $this->_options['column'] = array(
                
                $this->_options['column']
            );
        }
    }

    /**
     * Build a link contain column values using a string composed of zero or more 
     * directives as per vsprintf().
     * 
     * Additional columns can be supplied, if the link needs to access different
     * column values.
     * 
     * @param array $row
     */
    public function cellValue($row)
    {	
    	
    	$arrResult=$row[$this->getName()];
    	$strValue = $arrResult['value'];
    	
    	if (!empty($row[Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER])){
    		return "";
    	}
    	
    	if (!$row[Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED]){
    		$strValueColor="\033[32m{$arrResult}\033[0m";
    	}else{
    		$strValueColor="\033[31m{$arrResult}\033[0m";
    	}         
    	
    	return $strValueColor;
    }
}