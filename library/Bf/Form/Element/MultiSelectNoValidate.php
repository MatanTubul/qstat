<?php
class Bf_Form_Element_MultiSelectNoValidate extends Bf_Form_Element_MultiDbSelect
{
	public function isValid($value, $context = null){
		return TRUE;
	}

}