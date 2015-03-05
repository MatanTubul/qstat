<?php

class Ingot_JQuery_JqGrid_Column_Decorator_Edit_DoubleSelect extends Ingot_JQuery_JqGrid_Column_Decorator_Edit_Select
{

    /**
     * Get value of the column cell
     * 
     * @param $row Row array containing cell value
     * @return mixed
     */
    public function cellValue ($row)
    {
        $strValue = $this->_column->cellValue($row);
        
        $arrValues = $this->getOption('value');
        
        if (isset($arrValues[$strValue])){
            return $arrValues[$strValue];
        }
        return $strValue;
    }
}