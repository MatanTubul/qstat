<?php

class Qstat_Db_Table_ClickCounter extends Bf_Db_Table
{
	CONST TBL_NAME = "click_counter";

	const COL_ID = 'id_click_counter';
	const COL_TYPE = 'type';
	CONST TYPE_REBBOT="rebut";
	CONST TYPE_INSTALL="install";

	protected $_name = self::TBL_NAME;

	public static function count($strType){
		$objTable=new self();
		$objRow = $objTable->createRow();
		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;
		$objRow->{self::COL_TYPE}=$strType;
		$objRow->save();
	}

	public static function getAll(){
		$objTable=new self();
		$objTableSelect=$objTable->select();
		return $objTable->fetchAll($objTableSelect);
	}
}
