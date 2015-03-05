<?php
class Eav_Form_EntTypeGroupForm extends ZendX_JQuery_Form
{

	protected $_intEntityTypeId;

	public function __construct($intEntityTypeId,$options = array()){
		$this->_intEntityTypeId = $intEntityTypeId;
		parent::__construct($options);

	}

	public function init ()
	{
		$this->setName('formEntTypeGroup');
		$this->setAttrib('id', 'formEntTypeGroup');

		$objElement = new Zend_Form_Element_Hidden(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR);
		$objElement->setAttrib('id', Bf_Eav_Db_GroupAttributes::COL_ID_ATTR);
		$this->addElement($objElement);

		$objElement = new Zend_Form_Element_Select(Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP);
		$objElement->setAttrib('id', Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP)->setLabel('LBL_EAV_GROUP_SELECT');
		$objElement->addMultiOptions(Bf_Eav_Db_EntitiesTypesGroups::getGroupPair($this->_intEntityTypeId));
		$this->addElement($objElement);

		$objElement = new Zend_Form_Element_Text(Bf_Eav_Db_GroupAttributes::COL_ORDER);
		$objElement->setAttrib('id', Bf_Eav_Db_GroupAttributes::COL_ORDER)->setLabel('LBL_EAV_GROUP_ATTR_ORDER');
		$objElement->setValue('99');
		$this->addElement($objElement);
	}
}