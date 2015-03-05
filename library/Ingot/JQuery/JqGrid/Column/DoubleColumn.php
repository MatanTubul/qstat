<?php

class Ingot_JQuery_JqGrid_Column_DoubleColumn
{

    public static function createSelectColumn (Ingot_JQuery_JqGrid $objGrid, $mixValueData, $options = array(), $boolRequiered = TRUE)
    {
        
        $objAdapter = $objGrid->getAdapter();
        
        if ($objAdapter instanceof Ingot_JQuery_JqGrid_Adapter_DbTableSelect && is_string($mixValueData)) {
            
            $objTable = $objGrid->getAdapter()
                ->getSelect()
                ->getTable();
            $strTableClassName = get_class($objTable);
            $arrReferenceMap = $objTable->getReferenceByName($mixValueData);
            
            $strTableClass = $arrReferenceMap[$strTableClassName::REF_TABLE_CLASS];
            $arrColumns = array_merge($arrReferenceMap[$strTableClassName::REF_COLUMNS], array($arrReferenceMap['displayColumn']));
            $index = $arrReferenceMap[$strTableClassName::COLUMNS][0];
            
            if ((! isset($options['disableSelect'])) || (isset($options['disableSelect']) && empty($options['disableSelect']))) {
                
                $objReferenceTable = new $strTableClass();
                $objReferenceTableSelect = $objReferenceTable->select(TRUE);
                
                $objReferenceTableSelect->reset(Zend_Db_Select::COLUMNS);
                $objReferenceTableSelect->columns($arrColumns);
                
                $arrValues = $objReferenceTable->getAdapter()->fetchPairs($objReferenceTableSelect);
            }
        
     //if (empty($arrValues)){
        //  $arrValues = array();
        //}
        

        } else {
            $arrValues = $mixValueData;
            
            if (! isset($options['index'])) {
                throw new Exception('When using Values an Index mast be set');
            }
            $index = $options['index'];
        }
        
        $name = $objGrid->getId() . '_' . $index;
        
        $arrSearchOptions = array();
        
        if (!empty($options['searchoptions'])){
            $arrSearchOptions = $options['searchoptions'];
            unset($options['searchoptions']);
        }
        $arrSearchOptions['value'] = $arrValues;
        
        $objParentColumn = new Ingot_JQuery_JqGrid_Column($name, array_merge($options, array('index' => $index)));
        if ((! isset($options['disableSelect'])) || (isset($options['disableSelect']) && empty($options['disableSelect']))) {
            $objParentDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_Search_DoubleSelect($objParentColumn, $arrSearchOptions);
        } else {
            $objParentDecorator = $objParentColumn;
        }
        if (! empty($options['translate'])) {
            $objParentDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_ZendTranslate($objParentDecorator);
        }
        $objGrid->addColumn($objParentDecorator);
        
        if (empty($options['label'])) {
            $options['label'] = $objParentColumn->label;
        }
        
        $options['hidden'] = true;
        
        $objEditParentColumn = new Ingot_JQuery_JqGrid_Column($index, $options);
        
        if ((! isset($options['disableSelect'])) || (isset($options['disableSelect']) && empty($options['disableSelect']))) {
            $objParentEditDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_Edit_Select($objEditParentColumn, array('value' => $arrValues), array('required' => $boolRequiered, 'edithidden' => true));
        } else {
            $objParentEditDecorator = $objEditParentColumn;
        }
        $objGrid->addColumn($objParentEditDecorator);
    
    }

}