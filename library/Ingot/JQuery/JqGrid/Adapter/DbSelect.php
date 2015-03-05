<?php

/**
 * @see Zend_Paginator_Adapter_DbSelect
 */
require_once 'Zend/Paginator/Adapter/DbSelect.php';

/**
 * @see Ingot_JQuery_JqGrid_Adapter_Interface
 */
require_once 'Ingot/JQuery/JqGrid/Adapter/Interface.php';

/**
 * JqGrid DbSelect Adapter
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author andy.roberts
 */

class Ingot_JQuery_JqGrid_Adapter_DbSelect extends Zend_Paginator_Adapter_DbSelect implements Ingot_JQuery_JqGrid_Adapter_Interface
{
	CONST BEGIN_WITH = 'BEGIN_WITH';
	CONST NOT_BEGIN_WITH = 'NOT_BEGIN_WITH';
	CONST END_WITH = 'END_WITH';
	CONST NOT_END_WITH = 'NOT_END_WITH';
	CONST CONTAIN = 'CONTAIN';
	CONST NOT_CONTAIN = 'NOT_CONTAIN';
	CONST EQUAL = 'EQUAL';
	CONST NOT_EQUAL = 'NOT_EQUAL';
	CONST LESS_THAN = 'LESS_THAN';
	CONST LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
	CONST GREATER_THAN = 'GREATER_THAN';
	CONST GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
	CONST IN = 'IN';

	public static $arrOperator = array(
		self::EQUAL => '= ?',
		self::NOT_EQUAL => '!= ?',
		self::LESS_THAN => '< ?',
		self::LESS_THAN_OR_EQUAL => '<= ?',
		self::GREATER_THAN => '> ?',
		self::GREATER_THAN_OR_EQUAL => '>= ?',
		self::BEGIN_WITH => 'LIKE ?',
		self::NOT_BEGIN_WITH => 'NOT LIKE ?',
		self::END_WITH => 'LIKE ?',
		self::NOT_END_WITH => 'NOT LIKE ?',
		self::CONTAIN => 'LIKE ?',
		self::NOT_CONTAIN => 'NOT LIKE ?',
		self::IN => 'IN (?)',
	);

	protected $_operator;

	public function __construct ($select)
	{
		$this->_operator = self::$arrOperator;
		parent::__construct($select);
	}

	/**
	* Sort the result set by a specified column.
	*
	* @param Zend_Db_Expr $field Column name
	* @param string $direction Ascending (ASC) or Descending (DESC)
	* @return void
	*/
	public function sort (Zend_Db_Expr $field, $direction)
	{
		if (isset($field)) {
			// Bypas becouse of the grouping
			$arrSortList = explode(",", $field);

			$arrSortList[count($arrSortList) - 1] .= ' ' . $direction;

			$this->_select->order($arrSortList);
		}
	}

	/**
	* Filter the result set based on criteria.
	*
	* @param string $field Column name
	* @param string $value Value to filter result set
	* @param string $operation Search operator
	*/
	public function filter ($filter, $options = array())
	{
		/*
		if (strpos($filter['value'][0], "%") === 0 && !in_array("END_WITH", strpos($filter['expression']))
		{
		$filter['expression'][] = "END_WITH";
		}

		if (strpos($filter['value'][0], "%") === 0 && !in_array("BEGIN_WITH", strpos($filter['expression']))
		{
		$filter['expression'][] = "BEGIN_WITH";
		}

		$strKey = $expression[0];
		$boolTest =  array_key_exists(array($strKey,'NOT_CONTAIN'), $this->_operator);
		if (! array_key_exists($expression, $this->_operator)) {
		return;
		}
		*/
		if (isset($options['multiple'])) {
			return $this->_multiFilter($filter, $options);
		}

		return $this->_select->where($filter['field'] . ' ' . $this->_operator[$filter['expression']], $this->_setWildCardInValue($filter['expression'], $filter['value']));
	}

	/**
	* Multiple filtering
	*
	* @return
	*/
	protected function _multiFilter ($rules, $options = array())
	{
		$boolean = strtoupper($options['boolean']);

		foreach ($rules['field'] as $key => $rule) {
			// Check that the field is not EMPTY and is NOT NUMERIc (becouse then 0 is a valid data)
			if (empty($rules['value'][$key]) && ! is_numeric($rules['value'][$key])) {
				continue;
			}

			if ( ! empty( $rules['unionPart'][$key] ) ) {
				$arrUnion = $this->_select->getPart(Zend_Db_Select::UNION);
				$objSelect = $arrUnion[ $rules['unionPart'][$key] ][0];
			} else {
				$objSelect = $this->_select;
			}
			if ($rules['value'][$key] instanceof Zend_Db_Select) {
				if (! empty($rules['useHaving'][$key])) {
					$objValueSelect = $rules['value'][$key];
					$strSearchCriteria = implode(' ', $objValueSelect->getPart(Zend_Db_Select::HAVING));
					if ($boolean == 'OR') {
						$objSelect->orHaving($strSearchCriteria);
					} else {
						$objSelect->having($strSearchCriteria);
					}
				} else {
					if ($boolean == 'OR') {
						foreach ((array)$rules['value'][$key] as $strWhere) {
							$objSelect->orWhere($strWhere);
						}
					} else {
						foreach ((array)$rules['value'][$key] as $strWhere) {
							$objSelect->where($strWhere);
						}
					}
				}
			} else {
				if (! empty($rules['useHaving'][$key])) {
					if ($boolean == 'OR') {
						$objSelect->orHaving($rule . ' ' . $this->_operator[$rules['expression'][$key]], $this->_setWildCardInValue($rules['expression'][$key], $rules['value'][$key]));
					} else {
						$objSelect->having($rule . ' ' . $this->_operator[$rules['expression'][$key]], $this->_setWildCardInValue($rules['expression'][$key], $rules['value'][$key]));
					}
				} else {
					if ($boolean == 'OR') {
						$objSelect->orWhere($rule . ' ' . $this->_operator[$rules['expression'][$key]], $this->_setWildCardInValue($rules['expression'][$key], $rules['value'][$key]));
					} else {
						$objSelect->where($rule . ' ' . $this->_operator[$rules['expression'][$key]], $this->_setWildCardInValue($rules['expression'][$key], $rules['value'][$key]));
					}
				}
			}
		}
	}

	/**
	* Place wildcard filtering in value
	*
	* @return string
	*/
	protected function _setWildCardInValue ($expression, $value)
	{
		return self::prepereValue($expression, $value);
	}

	public static function prepereValue ($expression, $value)
	{
		switch (strtoupper($expression)) {
			case self::BEGIN_WITH:
			case self::NOT_BEGIN_WITH:
				$value = $value . '%';
				break;

			case self::END_WITH:
			case self::NOT_END_WITH:
				$value = '%' . $value;
				break;

			case self::CONTAIN:
			case self::NOT_CONTAIN:
				$value = '%' . $value . '%';
				break;
		}

		return $value;
	}
}
