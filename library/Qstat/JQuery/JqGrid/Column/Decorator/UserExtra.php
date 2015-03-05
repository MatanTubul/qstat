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

class Qstat_JQuery_JqGrid_Column_Decorator_UserExtra extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract
{

	private $arrValuePairs = array();

	/**
	* @return the $arrValuePairs
	*/
	public function getArrValuePairs () {
		return $this->arrValuePairs;
	}

	/**
	* @param field_type $arrValuePairs
	*/
	public function setArrValuePairs ($arrValuePairs) {
		$this->arrValuePairs = $arrValuePairs;
	}

	/**
	* Constructor
	*
	* @return void
	*/
	public function __construct ($column, $options = array())
	{
		if (empty($options['values'])){
			throw new Exception("Values cannt be empty");
		}
		$this->setArrValuePairs($options['values']);
		unset($options['values']);
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

	public function cellValue($row) {
		$strReturnVal = "";

		$arrExtra = unserialize( $row[ User_Model_Db_Users::COL_EXTRA_DATA ] );
		if ( empty($arrExtra) || empty( $arrExtra[ $this->getName() ] ) ) {
			return $strReturnVal;
		}

		// Something was unserialized.
		$values = $arrExtra[ $this->getName() ];
		$arrVal = $this->getArrValuePairs();
		if ( ! is_scalar( $values ) || empty( $arrVal[ $values ] ) ) {
			return $strReturnVal;
		}

		return $arrVal[ $values ];
	}

	public function unformatValue($strValue, $strRule) {
		$arrData = array();

		$arrData[$this->getName()] = $strValue;

		$strSerializeData = serialize($arrData);

		$strSerializeData = substr($strSerializeData, 5);
		$strSerializeData = substr($strSerializeData, 0, -1);

		return $strSerializeData;
	}

	public function getExpression($strPostExpression){
		return Ingot_JQuery_JqGrid_Adapter_DbSelect::CONTAIN;
	}
}