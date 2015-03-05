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

class Ingot_JQuery_JqGrid_Column_Decorator_CustomIcons extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract
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
    	$arrEntValues= $this->_options["values"];
    	
    	if (!empty($row[Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER])){
    		return "";
    	}
	    	if (empty($arrEntValues[$row[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES]])){
	    		ob_start();
	    		/*
    			?><dev style='width:100px; text-align:center;'><a href="/catalog/index/view?id_catalog=<?php echo $row[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG]?>"><span class="ui-icon ui-icon-pencil" style="float:left;"></span></a></div><?php
    			*/
	    		return ob_get_clean();
	    	}
    	ob_start();
    	?><div style='width:100px; text-align:center;'><a target="_blink" href="http://<?php echo $arrEntValues[$row[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES]];?>"><span class="ui-icon ui-icon-zoomin" style="float:left;" title="Open Ip"></span></a><a href="javascript:void(0)" onclick="power('<?php echo $row[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES];?>','<?php echo $row[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG];?>')"><span class="ui-icon ui-icon-power" style="float:left;"  title="Power Off"></span></a><input onclick="installOs('<?php echo $arrEntValues[$row[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES]];?>',<?php echo $row[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG];?>)" type="button" value="install" class="custom_button"></div><?php 
    	return ob_get_clean();
    }
}