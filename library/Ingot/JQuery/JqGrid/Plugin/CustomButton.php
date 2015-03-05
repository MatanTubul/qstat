<?php

/**
 * @see Ingot_JQuery_JqGrid_Plugin_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Plugin/Abstract.php';

/**
 * Display a search filter on each column
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Plugin_CustomButton extends Ingot_JQuery_JqGrid_Plugin_Abstract
{
	protected $_options;

	public function __construct ($options = array())
	{
		$this->setOptions($options);
	}

	public function preRender ()
	{
		
		$js = sprintf('%s("#%s").navButtonAdd("#%s",%s);', ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(), $this->getGrid()->getId(), $this->getGrid()
			->getPager()
			->getName(), $this->encodeJsonOptions($this->_options));
		
		$this->addOnLoad($js);
	
	}

	public function setOption ($name, $value)
	{
		$method_name = 'set'.ucwords($name);
		if (method_exists($this, $method_name)){
			$this->$method_name($value);
		} else {
			$this->_options[$name] = $value;
		}
	}


	function setCaption ($strCaption)
	{
		$this->_options['caption'] = $strCaption;
		return $this;
	}

	function setButtonicon ($strButtonIcon)
	{
		$this->_options['buttonicon'] = $strButtonIcon;
		return $this;
	}

	function setOnClickButton ($strOnClickButton)
	{
		$this->_options['onClickButton'] =  new Zend_Json_Expr($strOnClickButton); ;
		return $this;
	}

	function setPosition ($strPosition)
	{
		$this->_options['position'] = $strPosition;
		return $this;
	}

	function setTitle ($strTitle)
	{
		$this->_options['title'] = $strTitle;
		return $this;
	}

	function setCursor ($strCursor)
	{
		$this->_options['cursor'] = $strCursor;
		return $this;
	}

	function setId ($strId)
	{
		$this->_options['id'] = $strId;
		return $this;
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
		return array(
			"onClickButton"
		);
	}

}