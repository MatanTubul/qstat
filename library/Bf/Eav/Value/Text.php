<?php
class Bf_Eav_Value_Text extends Bf_Eav_Value_Simple {
	CONST ELEMENT_CLASS = 'Zend_Form_Element_Text';
	CONST VALUES_DB_CLASS = 'Bf_Eav_Db_Values_Varchar';

	public static function addValidators(Zend_Form_Element $objElement) {}
	public static function addDecorators(Zend_Form_Element $objElement) {}
}