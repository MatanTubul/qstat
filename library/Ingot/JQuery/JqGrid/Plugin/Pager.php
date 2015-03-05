<?php

/**
 * @see Ingot_JQuery_JqGrid_Plugin_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Plugin/Abstract.php';

/**
 * Display a pagination interface on grid for navigating through pages,
 * and providing buttons for common row operations.
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Plugin_Pager extends Ingot_JQuery_JqGrid_Plugin_Abstract {

	protected $_pagerName;

	protected $_defaultConfig = array ('edit' => false, 'add' => false, 'del' => false , 'search' => true, );

	protected $_defaultPlugin = "pager";

	protected $_afterSubmitCode = <<<'AFTER_SUBMIT_CODE'
function (data, postdata) {
	try {
		json = $.parseJSON(data.responseText);
		if (json.code == "ok") {
			if ( typeof this.gbox === "string" && $( this.gbox.replace("gbox_", "") ).length ) {
				$( this.gbox.replace("gbox_", "") )[0].clearToolbar();
			}

			return [true,"",""];
		} else {
			if (json.code == "error") {
				return [false,json.msg,""];
			} else {
				return [false,"Error Occured",""];
			}
		}
	} catch (e) {
		result = data.responseText.split(":");
		if ( result[0] != "OK") {
			if (result[1] != "") {
				return [false,result[1],""];
			} else {
				return [false,result[0],""];
			}
		} else {
			return [true,"",""];
		}
	}
}
AFTER_SUBMIT_CODE;

	protected $_afterCompleteCode = <<<'AFTER_COMPLETE_CODE'
function (response, postdata) {
	try { json = $.parseJSON(response.responseText);
		if ( json.code === "ok" ){
			alert( json.code.toUpperCase() );
		}
	} catch (e) {}
}
AFTER_COMPLETE_CODE;

	protected $_onInitializeSearch = <<<'ON_INITIALIZE_SEARCH'
function(elem) {
	var searchElement = $(elem);
	var exclude = new Array();
	var columnsModel = $("#%s").jqGrid('getGridParam', 'colModel');

	for ( var i = 0; i < columnsModel.length; i++ ) {
		if ( typeof columnsModel[i].name !== "undefined" && typeof columnsModel[i].stype !== "undefined" && columnsModel[i].stype === "select" ) {
			exclude.push( columnsModel[i].name );
		}
	}

	searchElement
	.find("td.columns select option")
	.each(function(index) {
		var currentOptionElement = $(this);
		var currentOptionValue = currentOptionElement.val();

		for ( var i = 0; i < exclude.length; i++ ) {
			if ( currentOptionValue.indexOf( exclude[i] ) !== -1 || exclude[i].indexOf(currentOptionValue) !== -1 ) {
				currentOptionElement.remove();
				break;
			}
		}
	});
}
ON_INITIALIZE_SEARCH;

	protected $_defaultAddEditConfig = array ("checkOnSubmit" => true, "reloadAfterSubmit" => true, "closeAfterAdd" => true, "closeAfterEdit" => true, "jqModal" => false, "closeOnEscape" => true, 'width' => 'auto' );

	private $_onInitialiseForm = array ();

	public function preRender() {
		// Render Local Settings
		$this->renderOnInitialiseForm ();

		$this->_pagerName = $pagerName = $this->_grid->getId() . '_pager';

		$js = sprintf( '%s("#%s").navGrid("#%s",%s)', ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(), $this->_grid->getId(), $pagerName, $this->getConfig() );
		$js .= ';';

		$html = '<div id="' . $pagerName . '"></div>';

		$this->addOnLoad($js);
		$this->addHtml($html);

		$this->_grid->setOption( $this->_defaultPlugin, $pagerName );
	}

	public function getName(){
		return $this->_pagerName;
	}

	public function getConfig($strOption = NULL) {
		$arrData = $this->_defaultConfig;

		if ( ! empty( $this->_defaultPlugin ) ) {
			$arrConfigData = $this->getOption( $this->_defaultPlugin );
			if ( ! empty( $arrConfigData ) ) {
				$arrData = array_merge( $arrData, (array) $arrConfigData );
			}
		}

		$strReturnData = "";
		$arrCommonData = "";
		$strEditData = "";
		$strAddData = "";
		$strDelData = "";
		$strSearchData = "{}";

		$objGrid = $this->getGrid();
		foreach ( $arrData as $strKey => $mixConfData ) {
			switch ($strKey) {
				case "add" :
					if ( is_array($mixConfData) ) {
						$arrCommonData [$strKey] = true;
						$strAddData = $objGrid->encodeJsonOptions ( $mixConfData );
					} else {
						$arrCommonData [$strKey] = $mixConfData;
						$strAddData = '{}';
					}
					break;
				case "edit" :
					if ( is_array($mixConfData) ) {
						$arrCommonData [$strKey] = true;
						$strEditData = $objGrid->encodeJsonOptions ( $mixConfData );
					} else {
						$arrCommonData[$strKey] = $mixConfData;
						$strEditData = '{}';
					}
					break;
				case "del" :
					if ( is_array($mixConfData) ) {
						$arrCommonData[$strKey] = true;
						$strDelData = $objGrid->encodeJsonOptions ( $mixConfData );
					} else {
						$arrCommonData[$strKey] = $mixConfData;
						$strDelData = '{}';
					}
					break;
				case "search" :
					if ( is_array($mixConfData) ) {
						$arrCommonData[$strKey] = true;
						$strSearchData = $objGrid->encodeJsonOptions( $mixConfData );
					} else {
						$arrCommonData[$strKey] = $mixConfData;
						$strSearchData = '{}';
					}
					break;
				default :
					$arrCommonData [$strKey] = $mixConfData;
					break;
			}
		}

		if ($strOption) {
			$strData = $$strOption;
		} else {
			$strData = $objGrid->encodeJsonOptions ( $arrCommonData ) . ', ' . $strEditData . ', ' . $strAddData . ', ' . $strDelData . ', '. $strSearchData;
		}

		return $strData;

	}

	public function postRender() { // Not implemented
	}

	public function preResponse() { // Not implemented
	}

	public function postResponse() { // Not implemented
	}

	private function _registerAfterComplete($strCode) {
		$this->setOption( $this->_defaultPlugin, array ($strCode => array ('afterComplete' => $this->_afterCompleteCode ) ) );
	}

	public function addAfterComplete() {
		$this->_registerAfterComplete( "add" );
	}

	public function editAfterComplete() {
		$this->_registerAfterComplete ( "edit" );
	}

	public function delAfterComplete() {
		$this->_registerAfterComplete ( "del" );
	}

	/**
	* Register default After Submit action
	*
	* @param string $strCode
	*/
	private function registerAfterSubmit($strCode) {
		$this->setOption( $this->_defaultPlugin, array ($strCode => array ('afterSubmit' => $this->_afterSubmitCode ) ) );
	}

	/**
	* Set JS for After Submit
	*/
	public function addAfterSubmit() {
		$this->registerAfterSubmit ( "add" );
	}
	/**
	* Set JS for After Submit
	*/
	public function editAfterSubmit() {
		$this->registerAfterSubmit ( "edit" );
	}
	/**
	* Set JS for After Submit
	*/
	public function delAfterSubmit() {
		$this->registerAfterSubmit ( "del" );
	}

	/**
	* Register default add/edit/del options
	*
	* @param string $strCode
	*/
	private function registerAddEditOption($strCode) {
		$this->setOption ( $this->_defaultPlugin, array ($strCode => $this->_defaultAddEditConfig ) );
		$this->registerAfterSubmit( $strCode );
	}

	/**
	* Set JS for After Submit
	*/
	public function setDefaultAdd( $addAfterComplete = false ) {
		$this->registerAddEditOption ( "add" );
		if ($addAfterComplete) {
			$this->_registerAfterComplete ( "add" );
		}
	}
	/**
	* Set JS for After Submit
	*/
	public function setDefaultEdit( $addAfterComplete = false ) {
		$this->registerAddEditOption ( "edit" );
		if ($addAfterComplete) {
			$this->_registerAfterComplete ( "edit" );
		}
	}
	/**
	* Set JS for After Submit
	*/
	public function setDefaultDel( $addAfterComplete = false ) {
		$this->registerAddEditOption ( "del" );
		if ($addAfterComplete) {
			$this->_registerAfterComplete ( "del" );
		}
	}

	public function setDefaultSearch() {
		$this->setOption( $this->_defaultPlugin, array (
			'search' => array( 'onInitializeSearch' => sprintf( $this->_onInitializeSearch, $this->_grid->getId() ), ),
		));
	}

	public function getMethods() {
		// Not Implimented
	}

	public function getEvents() {
		return array( "onInitializeForm", "onInitializeSearch", "afterSubmit", "afterComplete", 'beforeShowForm', );
	}

	public function registerCustomAddEditOption($strCode, $strFunction, $strMethod) {
		$arrOption = $this->getOption ( $strCode );
		$arrOption [$strFunction] = $strMethod;

		$this->setOption ( $this->_defaultPlugin, array ($strCode => $arrOption ) );
		// $this->registerAfterSubmit($strCode);
		return $this;
	}

	public function addOnInitialiseForm($strColumnAction) {
		$this->_onInitialiseForm [] = $strColumnAction;
		return $this;
	}

	/**
	*
	* Checks that there is additional On Initialise Form actions persent and adds them to theglobal options!
	* @return Ingot_JQuery_JqGrid_Plugin_Pager
	*/
	private function renderOnInitialiseForm() {

		$strData = implode ( ' ', $this->_onInitialiseForm );

		if ( ! empty($strData) ) {
			$this->registerCustomAddEditOption( 'edit', 'onInitializeForm', 'function(formid) { ' . $strData . ' } ' );
			$this->registerCustomAddEditOption( 'add', 'onInitializeForm', 'function(formid) { ' . $strData . ' } ' );
		}

		return $this;
	}
}
