<?php

class Catalog_Form_LockEdit extends ZendX_JQuery_Form
{

	public function init()
	{
		$this->setName('Lockedit');
		$this->setAttrib('id', 'Lockedit');

		$objElement = new Zend_Form_Element_Hidden(Qstat_Db_Table_Lock::COL_ID_LOCK);
		$this->addElement($objElement);

		// Get Allowed Users
		$arrValues = $this->getMySubUsers();
		$objElement = new Zend_Form_Element_Select(Qstat_Db_Table_Lock::COL_ID_USER);
		$objElement->addMultiOptions($arrValues);
		$objElement
		->setAttrib('id', Qstat_Db_Table_Lock::COL_ID_USER)
		->setLabel('LBL_LOCK_USER');
		$this->addElement($objElement);

		$objElement = new Zend_Form_Element_Text(Qstat_Db_Table_Lock::COL_LOCK_START);
		$objElement
		->setAttrib('id', Qstat_Db_Table_Lock::COL_LOCK_START)
		->setAttrib('disabled', TRUE)
		->setLabel('LBL_LOCK_START');
		$this->addElement($objElement);

		$objElement = new Bf_Form_Element_DateTime(Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK);
		$objElement
		->setAttrib('id', Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK)
		->setLabel('LBL_LOCK_SCHEDULED_END')
		->setJQueryParam('dateFormat', 'yy-mm-dd');
		$this->addElement($objElement);
	}

	protected function getMySubUsers()
	{
		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;

		$objUsers = new User_Model_Db_Users();
		$objUsersSelect = $objUsers->select(TRUE);

		switch ( $objUserDetails->{User_Model_Db_Users::COL_ID_ROLE} ) {
			case 5:
				// Group Manager, get all group users (and managers)
				$objUsersSelect->where(User_Model_Db_Users::COL_ID_ROLE." in (?)", array( 4, 5, ));

				break;
			case 7:
				// Global Manager
				$objUsersSelect->where(User_Model_Db_Users::COL_ID_ROLE." in (?)", array( 4, 5, 7, ));

				break;
			case 3:
				// Sys Admin
				$objUsersSelect->where(User_Model_Db_Users::COL_ID_ROLE." in (?)", array( 4, 5, 7, 3, ));

				break;
			default:
				$objUsersSelect->where("0 = 1");
				break;
		}
		$objUsersSelect->reset(Zend_Db_Select::COLUMNS);
		$objUsersSelect->columns(array(User_Model_Db_Users::COL_ID_USERS,new Zend_Db_Expr('CONCAT_WS(" ",'.User_Model_Db_Users::COL_FIRST_NAME.','.User_Model_Db_Users::COL_LAST_NAME.')')));

		$arrData = $objUsers->getAdapter()->fetchPairs($objUsersSelect);

		return $arrData;
	}
}
