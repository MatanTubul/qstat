<?php

/**
 * @see Ingot_JQuery_JqGrid_Plugin_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Plugin/Abstract.php';

/**
 * Add On DoubleClick Row redirect... 
 * Using RowID only currently
 *  
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Plugin_Row_DblClkRedirect extends Ingot_JQuery_JqGrid_Plugin_Abstract
{
	protected $_additionalParam;
	protected $_additionalStaticParams;
	
	protected $_model;
	protected $_controller;
	protected $_action;

	/**
	 * 
	 * Enter description here ...
	 * @param string $strModel
	 * @param string $strController
	 * @param string $strAction
	 * @param string $strParamName
	 */
	function __construct ($strModel, $strController, $strAction, $strParamName = 'id', $arrAdditionalParams = array())
	{
		$this->_model = $strModel;
		$this->_controller = $strController;
		$this->_action = $strAction;
		$this->_additionalParam = $strParamName;
		$this->_additionalStaticParams = $arrAdditionalParams;
				
	}

	public function preRender ()
	{
		$arrUrlData = array(
			'module' => $this->_model, 'controller' => $this->_controller, 'action' => $this->_action
		) + $this->_additionalStaticParams;
		$strUrl = $this->getGrid()
			->getView()
			->url($arrUrlData, null, true, false);
		
		$this->getGrid()->setOption('ondblClickRow', "function(rowId, iRow, iCol, e){ if(rowId){  document.location.href ='" . $strUrl . "/".$this->_additionalParam."/'+rowId } }");
	
	}

	public function postRender ()
	{ // Not implemented
	}

	public function preResponse ()
	{ // Not implemented
	}

	public function postResponse ()
	{ // Not implemented
	}

	public function getMethods ()
	{
		return array();
	}

	public function getEvents ()
	{
		return array();
	}

}