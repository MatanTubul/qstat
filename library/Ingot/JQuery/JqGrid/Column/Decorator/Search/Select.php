<?php
/**
 * @see Ingot_JQuery_JqGrid_Column_Decorator_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Column/Decorator/Abstract.php';

/**
 * Decorate a column which contains Search Select in search toolbar
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Alex Frenkel
 */
class Ingot_JQuery_JqGrid_Column_Decorator_Search_Select extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract
{
    protected $_options = array();

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct ($column, $options = array())
    {
        $this->_column = $column;
        if (! isset($options['value'])) {
            throw new Ingot_JQuery_JqGrid_Exception("Value mast be set for select", - 3);
        }
        $this->_options = $options;

		// $this->decorate();
    }

    /**
     * Decorate column to search select
     *
     * @return void
     */
    public function decorate()
    {
        $this->_column->setOption('stype', 'select');
        $this->_column->setOption('search', TRUE);
        $strData['value'] = "";

        /*
        if (Zend_Registry::isRegistered('Zend_Translate')) {
        $objTranslate = Zend_Registry::get('Zend_Translate');
        }
        */

        $arrValues = array("" => 'Select') + $this->getValues();

        foreach ($arrValues as $strKey => $strValue) {
            if (! empty($strData['value'])) {
                $strData['value'] .= ";";
            }
            /*
            if (! empty($objTranslate)) {
            $strValue = $objTranslate->translate($strValue);
            }
            */
            $strData['value'] .= $strKey . ":" . $strValue;
        }

        if (! empty($this->_options['sopt'])) {
            $strData['sopt'] = $this->_options['sopt'];
        }
        if (! empty($this->_options['defaultValue'])) {
            $strData['defaultValue'] = $this->_options['defaultValue'];
        }
        if (! empty($this->_options['dataUrl'])) {
            $strData['dataUrl'] = $this->_options['dataUrl'];
            if (! empty($this->_options['buildSelect'])) {
                $strData['buildSelect'] = $this->_options['buildSelect'];
            }
        }
        if (! empty($this->_options['dataInit'])) {
            $strData['dataInit'] = new Zend_Json_Expr("function(el){" . $this->_options['dataInit'] . " }");
        }
        if (! empty($this->_options['dataEvents'])) {
            $strData['dataEvents'] = $this->_options['dataEvents'];
        }
        if (! empty($this->_options['attr'])) {
            $strData['attr'] = $this->_options['attr'];
        }
        if (! empty($this->_options['searchhidden'])) {
            $strData['searchhidden'] = $this->_options['searchhidden'];
        }
        $this->_column->setOption('searchoptions', $strData);
    }

    /**
     *
     * Enter description here ...
     * @return array
     */
    public function getValues(){
        return $this->_options['value'];
    }
}