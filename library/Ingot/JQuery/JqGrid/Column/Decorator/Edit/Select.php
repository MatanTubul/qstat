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
class Ingot_JQuery_JqGrid_Column_Decorator_Edit_Select extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract
{
    protected $_options = array();
    protected $_rules = array();
    protected $_formOptions = array();


    private $_arrRules = array();

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct ($column, $editOptions = array(), $editRules = array(), $formOptions = array())
    {
        $this->_column = $column;
        if (!isset($editOptions['value']) && empty($editOptions['dataUrl'])) {
            throw new Ingot_JQuery_JqGrid_Exception("Value mast be set for select", - 3);
        }
        $this->_options = $editOptions;
        $this->_rules = $editRules;
        $this->_formOptions = $formOptions;
		// $this->decorate();
    }

    /**
     * Decorate column to search select
     *
     * @return void
     */
    public function decorate ()
    {
        $this->setOption('editable', TRUE);
        $this->setOption('edittype', 'select');

        $this->_setEditOptions();
        $this->_setEditRules();
        $this->_setEditFormOptions();
    }

    private function _setEditOptions ()
    {
        /*
        if (Zend_Registry::isRegistered('Zend_Translate')) {
        $objTranslate = Zend_Registry::get('Zend_Translate');
        }
        */

        $arrData = array();
        $arrData['value'] = '';

        if (! empty($this->_options['dataUrl'])) {
            $arrData['dataUrl'] = $this->_options['dataUrl'];
            if (! empty($this->_options['buildSelect'])) {
                $arrData['buildSelect'] = $this->_options['buildSelect'];
                unset($this->_options['buildSelect']);
            }
            if (! empty($this->_options['defaultValue'])) {
                $arrData['defaultValue'] = $this->_options['defaultValue'];
                unset($this->_options['defaultValue']);
            }
            unset($this->_options['dataUrl']);
        } else {
            $arrValues = array("" => 'Select')+$this->_options['value'];

            foreach ($arrValues as $strKey => $strValue) {
                if (! empty($arrData['value'])) {
                    $arrData['value'] .= ";";
                }
                /*
                if (! empty($objTranslate)) {
                $strValue = $objTranslate->translate($strValue);
                }
                */
                $arrData['value'] .= $strKey . ":" . $strValue;

                if (! empty($this->_options['defaultValue'])) {
                    if ($strKey == $this->_options['defaultValue']) {
                        $arrData['defaultValue'] = $strValue;
                        unset($this->_options['defaultValue']);

                    } elseif ($strValue == $this->_options['defaultValue']) {
                        $arrData['defaultValue'] = $this->_options['defaultValue'];
                        unset($this->_options['defaultValue']);

                    }
                }
            }
            unset($this->_options['value']);
        }

        foreach ($this->_options as $strKey => $strValue){
            $arrData[$strKey] = $strValue;
        }
        $this->setOption('editoptions', $arrData);
    }

    private function _setEditRules ()
    {
        $arrData = array();

        if (! empty($this->_rules['custom']) && ! empty($this->_rules['custom_func'])) {
            $arrData['custom'] = TRUE;
            $arrData['custom_func'] = new Zend_Json_Expr($this->_rules['custom_func']);
            unset($this->_rules['custom']);
            unset($this->_rules['custom_func']);
        }
        foreach ($this->_rules as $strKey => $strValue){
            $arrData[$strKey] = $strValue;
        }

        $this->setOption('editrules', $arrData);
    }

    private function _setEditFormOptions ()
    {
        $arrData = array();
        foreach ($this->_formOptions as $strKey => $strValue){
            $arrData[$strKey] = $strValue;
        }

        $this->setOption('formoptions', $arrData);
    }
}