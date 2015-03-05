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

class Ingot_JQuery_JqGrid_Column_Decorator_Eav extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract
{
    private $intAttrId;
    
    private $eavValueType;
    
    private $arrValuePairs = array();

    /**
     * @return string $eavValueType
     */
    public function getEavValueType ()
    {
        return $this->eavValueType;
    }

    /**
     * @param string $eavValueType
     */
    public function setEavValueType ($eavValueType)
    {
        $this->eavValueType = $eavValueType;
    }

    /**
     * @return int $intEntId
     */
    public function getIntAttrId ()
    {
        return $this->intAttrId;
    }

    /**
     * @param int $intEntId
     */
    public function setIntAttrId ($intAttrId)
    {
        $this->intAttrId = $intAttrId;
    }

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct ($column, $options = array())
    {
        $this->setIntAttrId($options['intAttrId']);
        $this->setEavValueType($options['eavValueType']);
        parent::__construct($column, $options);
    }

    /**
     * Decorate column
     * 
     * @return void
     */
    public function decorate ()
    {
        $this->_column->decorate ();
    }

    public function cellValue ($row)
    {
        $strReturnVal = "";
        $arrAttrValues = $this->prepereAttrValPairs($row[Bf_Catalog_Models_Catalog::COL_ATTR_DATA]);
        
        if (isset($arrAttrValues[$this->getIntAttrId()])) {
            $intValId = $arrAttrValues[$this->getIntAttrId()];
            if (!empty($intValId)){
                $arrValues = $this->getArrayValues(array(),$this->getIntAttrId());
                
                if (!empty($arrValues[$intValId])){
                	$strReturnVal = $arrValues[$intValId];
                }else{
                	$strReturnVal="";
                }
            }
        }
        
        return $strReturnVal;
    }
    
	public function unformatValue($strValue, $strRule) {

	    $arrOperators = Ingot_JQuery_JqGrid::$searchOperators;
	    $arrSearchPrefixes = Ingot_JQuery_JqGrid_Adapter_DbSelect::$arrOperator;
	    
	    $strPreperedValRule = Bf_Eav_Db_Values_Abstract::COL_VALUE . ' ' . $arrSearchPrefixes[$arrOperators[$strRule]];
	    $strPreperedVal = Ingot_JQuery_JqGrid_Adapter_DbSelect::prepereValue($arrOperators[$strRule], $strValue);
	    
	    $arrValuesMatchCreteria = $this->getArrayValuesWithWhere(array(array('criteria'=>$strPreperedValRule, 'value' => $strPreperedVal)),$this->getIntAttrId());

	    if (count($arrValuesMatchCreteria)>1){
	        // Create multiple selects...
	        $strPreperedValRule = Bf_Catalog_Models_Catalog::COL_ATTR_DATA . ' ' . $arrSearchPrefixes[$arrOperators[$strRule]];
	        $mixValue = $this->createMultipleValues($this->getIntAttrId(),$arrValuesMatchCreteria,$strPreperedValRule);
	    } elseif (count($arrValuesMatchCreteria)==1){	        
	        $mixValue = $this->createSelectValue($this->getIntAttrId(),array_pop(array_keys($arrValuesMatchCreteria)));
	    } else {
	        $mixValue = $this->createSelectValue($this->getIntAttrId(),0);
	    }	    
		return $mixValue;
	}
	
	protected function createMultipleValues($intAttrId,$arrValuesMatchCreteria,$strPreperedValRule ){
	    $arrWhereCond = array();
	    
	    $objAdapter = Zend_Db_Table::getDefaultAdapter();
	    $objSelect = new Zend_Db_Select($objAdapter);
	    
	    foreach ($arrValuesMatchCreteria as $intValId => $intValData){
	        $objSelect->orHaving($strPreperedValRule,$this->createSelectValue($intAttrId, $intValId));	            
	    } 

	    return $objSelect;
	}
	
	protected function createSelectValue($intAttrId, $strVal){
	    return "%,".$intAttrId.":".$strVal.",%";
	}

    protected function getArrayValues ()
    {
        if (empty($this->arrValuePairs)) {
            // Get New Pairs
            $objEavValue = Bf_Eav_Value::factory($this->eavValueType);
            $this->arrValuePairs = $objEavValue->getValuePairs(array(),$this->getIntAttrId());
        }
        return $this->arrValuePairs;
    }

    protected function getArrayValuesWithWhere ($arrWhereValue)
    {
        // Get New Pairs
        $objEavValue = Bf_Eav_Value::factory($this->eavValueType);
        return $objEavValue->getValuePairs($arrWhereValue,$this->getIntAttrId());
    }

    protected function prepereAttrValPairs ($strAttrVal)
    {
        $arrAttrVal = array();
        
        $arrAttrValuesPairs = explode(',', $strAttrVal);
        foreach ((array) $arrAttrValuesPairs as $strAttrValuesPair) {
            if (! empty($strAttrValuesPair)) {
                $arrAttrValExplode = explode(':', $strAttrValuesPair);
                if (count($arrAttrValExplode) > 1) {
                    //                    Zend_Debug::dump($strAttrValuesPair);
                    $arrAttrVal[$arrAttrValExplode[0]] = $arrAttrValExplode[1];
                }
            }
        }
        return $arrAttrVal;
    }
    

}