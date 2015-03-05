<?php
class Bf_Form_Element_MultiDbSelect extends Bf_Form_Element_DbSelect
{
	/**
	* 'multiple' attribute
	* @var string
	*/
	public $multiple = 'multiple';

	public $size = 20;

	/**
	* Use formSelect view helper by default
	* @var string
	*/
	public $helper = 'formSelect';

	/**
	* Multiselect is an array of values by default
	* @var bool
	*/
	protected $_isArray = true;
}