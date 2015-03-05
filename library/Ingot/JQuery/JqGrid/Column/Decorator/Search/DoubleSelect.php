<?php

class Ingot_JQuery_JqGrid_Column_Decorator_Search_DoubleSelect extends Ingot_JQuery_JqGrid_Column_Decorator_Search_Select
{

    /**
     * Get value of the column cell
     * 
     * @param $row Row array containing cell value
     * @return mixed
     */
    public function cellValue ($row)
    {
        $strIndex = $this->getOption('index');
        
        if (isset($row[$strIndex])){
        
            $strValue = $row[$strIndex];
        
            $arrValues = $this->getValues();
            
            if (isset($arrValues[$strValue])){
                return $arrValues[$strValue];
            }
        }
        
        $strValue = $this->_column->cellValue($row);
        return $strValue;
    }

}