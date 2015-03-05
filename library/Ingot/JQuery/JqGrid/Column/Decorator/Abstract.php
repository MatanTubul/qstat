<?php

/**
 * JqGrid Column Decorator Abstract
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

abstract class Ingot_JQuery_JqGrid_Column_Decorator_Abstract {
	/**
	 * Column Instance
	 * 
	 * @var Ingot_JQuery_JqGrid_Column
	 */
	protected $_column;
	
	/**
	 * Grid Object
	 * 
	 * @var Ingot_JQuery_JqGrid
	 */
	protected $_objGrid;
	
	protected $_options = array();
    
	
	/**
     * @return Ingot_JQuery_JqGrid_Column $_column
     */
    public function getColumn ()
    {
        return $this->_column;
    }

	/**
     * @param Ingot_JQuery_JqGrid_Column $_column
     */
    public function setColumn ($_column)
    {
        $this->_column = $_column;
        return $this;
    }

	public function __construct( $column, $options = array()) {
		$this->_column = $column;
		$this->setOptions($options);
	}
	
	/**
	 * Get the column field name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->_column->getName ();
	}
	
	/**
	 * Override set to allow access to column options
	 * 
	 * @return void
	 */
	public function __set($name, $value) {
		$this->_column->setOption ( $name, $value );
	}
	
	/**
	 * Override get to allow access to column options
	 * 
	 * @param string $name column option name
	 * @return void
	 */
	public function __get($name) {
		return $this->_column->getOption ( $name );
	}
	
	/**
	 * Get a single column option
	 * 
	 * @return mixed
	 */
	public function getOption($name) {
		return $this->_column->getOption ( $name );
	}
	
    /**
     * 
     * Checks that the option isSet in current column
     * @param string $name
     * @return bool
     */
    public function isSetOption($name){
    	return$this->_column->isSetOption ( $name );
    }
	
	/**
	 * Set a single column option
	 * 
	 * @return Ingot_JQuery_JqGrid_Column
	 */
	public function setOption($name, $value) {
		$this->_column->setOption ( $name, $value );
	}
	
	/**
	 * Get all column options
	 * 
	 * @return array
	 */
	public function getOptions() {
		return $this->_column->getOptions ();
	}
	
	/**
	 * Get value of the column cell
	 * 
	 * @param $row Row array containing cell value
	 * @return mixed
	 */
	public function cellValue($row) {
		return $this->_column->cellValue ( $row );
	}
	
	public function unformatValue($strValue) {
		return $strValue;
	}
	
	/**
	 * Set object state from options array
	 *
	 * @param  array $options
	 * @return Ingot_JQuery_JqGrid_Column_Decorator_Abstract
	 */
	public function setOptions(array $options) {
		
		foreach ( $options as $key => $value ) {
			$method = 'set' . ucfirst ( $key );
			
			if (method_exists ( $this, $method )) {
				// Setter exists; use it
				$this->$method ( $value );
			} else {
				// Assume it's metadata
				$this->_options[$key] = $value;
			}
		}
		return $this;
	}
	
	private function setDataInit($strValue){
		
		$this->_options['dataInit'] = new Zend_Json_Expr($strValue);
		return $this;
	}
	
	/**
	 * Set Grid Object
	 * 
	 * @param Ingot_JQuery_JqGrid $objGrid
	 * @return Ingot_JQuery_JqGrid_Column_Decorator_Abstract
	 */
	public function setGrid(Ingot_JQuery_JqGrid $objGrid){		
		$this->_objGrid = $objGrid;		
		$this->_column->setGrid($objGrid);
		return $this;
	}
	
	/**
	 * 
	 * Get Grid Object
	 * @return Ingot_JQuery_JqGrid
	 */
	public function getGrid(){
		return $this->_objGrid;
	}
	
	public function getGridId(){
		return $this->_objGrid->getId();
	}
	
	public function getExpression($strPostExpression){
		return $strPostExpression;
	}
	
	abstract public function decorate();
} 