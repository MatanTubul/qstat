<?php

class Labels_Form_Mail extends ZendX_JQuery_Form
{

	public function init ()
	{
		/* Form Elements & Other Definitions Here ... */
		
		$this->setName('editMail');
		$this->setAttrib('id', 'editMailForm');
		
		$objElement = new Zend_Form_Element_Text("Subject");
		$objElement->setLabel('LBL_MAIL_SUBJECT');
		$this->addElement($objElement);
		
		$objElement = new Zend_Form_Element_Textarea("Content");
		$objElement->setLabel('LBL_MAIL_CONTENT');
		$objElement->setOptions(array("class"=>"ckeditor"));
		$this->addElement($objElement);
	
	}

}

