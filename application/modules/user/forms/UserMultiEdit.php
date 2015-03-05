<?php

class User_Form_UserMultiEdit extends ZendX_JQuery_Form
{
	public function init () {
		$this->addPrefixPath('Bf_Form_Element_', 'Bf/Form/Element/', Zend_Form::ELEMENT);

		// Form Elements & Other Definitions Here.
		$objOptions = new Zend_Config_Xml(dirname(__FILE__) . '/../configs/forms/forms.xml');
		$this->setConfig($objOptions->multiedit);
	}
}
