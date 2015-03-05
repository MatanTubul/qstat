<?php
/**
 * Represents a single column within the grid.
 *
 * @package Ingot_JQuery_JQgrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */
class Ingot_JQuery_JqGrid_Column {
    /**
     * Field name of column
     *
     * @var string
     */
    protected $_name = null;
    /**
     * An array of column properites
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options
     * @var array
     */
    protected $_options = array();
    /**
     * An array of row on values
     *
     * @var unknown_type
     */
    protected $_row = array();
    /**
     * Constructer.
     *
     * @param string $name Column field name
     * @param array $options
     */
    public function __construct ($name, $options = array()) {
        $this->_name = $name;
        $this->_options = $options;
        $this->_options['name'] = htmlspecialchars( $this->_name, ENT_QUOTES );
        if (empty($this->_options['label'])) {
            $this->_options['label'] = ucwords(
            str_replace("_", " ", $this->_name));
        }
        $this->prepareLabel();
        $this->_options['label'] = htmlspecialchars( $this->_options['label'], ENT_QUOTES );
    }
    /**
     * Translates Label!
     */
    private function prepareLabel () {
        if (Zend_Registry::isRegistered('Zend_Translate')) {
            $objTranslate = Zend_Registry::get('Zend_Translate');
            if (! empty($objTranslate)) {
                $this->_options['label'] = $objTranslate->translate(
                $this->_options['label']);
            }
        }
    }
    /*
	 * Get the column field name
	 *
	 * @return string
	 */
    public function getName () {
        return $this->_name;
    }

    /*
	 * Get the column index name
	 *
	 * @return string
	 */
    public function getIndex () {
        return $this->getOption('index');
    }
    /*
	 * Set the column friendly label name
	 *
	 * @return string
	 */
    public function setLabel ($label) {
        $this->_options['label'] = $label;
        $this->prepareLabel();
    }
    /**
     * Override set to allow access to column options
     *
     * @return void
     */
    public function __set ($name, $value) {
        $this->setOption($name, $value);
    }
    /**
     * Override get to allow access to column options
     *
     * @param string $name column option name
     * @return void
     */
    public function __get ($name) {
        return $this->getOption($name);
    }
    /*
     * Get a single column option
     *
     * @return mixed
     */
    public function getOption ($name) {
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        } else {
            return false;
        }
    }

    /**
     *
     * Checks that the option isSet in current column
     * @param string $name
     * @return bool
     */
    public function isSetOption($name){
    	return array_key_exists($name, $this->_options);
    }

    /*
     * Set a single column option
     *
     * @return Ingot_JQuery_JqGrid_Column
     */
    public function setOption ($name, $value) {
        if ($name == 'name' && isset($this->_options['name'])) {
            throw new Ingot_JQuery_JqGrid_Exception(
            'The column name cannot be changed, as it has already been defined.');
        }
        $arrUnEscapeList = array_merge(Ingot_JQuery_JqGrid::$arrEvents,
        Ingot_JQuery_JqGrid::$arrCallbacks);
        if (in_array($name, $arrUnEscapeList, true)) {
            $this->_options[$name] = new Zend_Json_Expr($value);
        } else {
            $this->_options[$name] = $value;
        }
        return $this;
    }
    /*
     * Get all column options
     *
     * @return array
     */
    public function getOptions () {
        return $this->_options;
    }
    /**
     * Get cell value
     *
     * Accepts an array representing a grid row, useful
     * for decorators which may require access to other
     * cells in row data.
     *
     * @param $row Row array containing column cell value
     * @return mixed
     */
    public function cellValue ($row) {

    	if (isset($row[$this->getIndex()])) {
    		$strCellValue = $row[$this->getIndex()];
    	}elseif (isset($row[$this->getName()])) {
            $strCellValue = $row[$this->getName()];
        } else {
            $strCellValue = "";
        }
        return htmlentities($strCellValue, ENT_COMPAT, "UTF-8");
    }
    public function unformatValue ($strValue) {
        return $strValue;
    }
    /**
     * Set Grid Object
     *
     * @param Ingot_JQuery_JqGrid $objGrid
     * @return Ingot_JQuery_JqGrid_Column_Decorator_Abstract
     */
    public function setGrid (Ingot_JQuery_JqGrid $objGrid) {
        $this->_objGrid = $objGrid;
        return $this;
    }
    /**
     *
     * Get Grid Object
     * @return Ingot_JQuery_JqGrid_Column_Decorator_Abstract
     */
    public function getGrid () {
        return $this->_objGrid;
    }
    public function decorate () {}

	public function getExpression($strPostExpression){
		return $strPostExpression;
	}
}