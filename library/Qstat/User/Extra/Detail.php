<?php

class Qstat_User_Extra_Detail implements User_Model_User_Extra_Interface
{

	CONST FORM_NAME = 'qstatSiteGroup';

	CONST DEFAULT_ROLE_GROUP = "4";
	CONST DEFAULT_ROLE_GROUP_MNG = "5";
	CONST DEFAULT_ROLE_GLOBAL_MNG = "7";
	CONST DEFAULT_ROLE_SYS_ADMIN = '3';

	private $_arrRolesWithSite   = array( self::DEFAULT_ROLE_GROUP, self::DEFAULT_ROLE_GROUP_MNG, self::DEFAULT_ROLE_GLOBAL_MNG, self::DEFAULT_ROLE_SYS_ADMIN, );
	private $_arrRolesWithGroups = array( self::DEFAULT_ROLE_GROUP, self::DEFAULT_ROLE_GROUP_MNG, self::DEFAULT_ROLE_GLOBAL_MNG, self::DEFAULT_ROLE_SYS_ADMIN, );

	protected $_arrCurrentUserMainRow;

	public static $arrRoles = array(
		self::DEFAULT_ROLE_GROUP_MNG => array(
			User_Model_Db_Roles::COL_ID_ROLES => self::DEFAULT_ROLE_GROUP_MNG,
			User_Model_Db_Roles::COL_ID_PARENT => self::DEFAULT_ROLE_GROUP,
			User_Model_Db_Roles::COL_ROLE => 'group_mng',
		),
		self::DEFAULT_ROLE_GLOBAL_MNG => array(
			User_Model_Db_Roles::COL_ID_ROLES => self::DEFAULT_ROLE_GLOBAL_MNG,
			User_Model_Db_Roles::COL_ID_PARENT => self::DEFAULT_ROLE_GROUP_MNG,
			User_Model_Db_Roles::COL_ROLE => 'global_mng',
		),
	);

	public function __construct()
	{
		$this->_init();
	}

	private function _init()
	{
		// Get Roles, check that requiered roles exists...
		$boolReload = FALSE;

		$objCurrentRolesRowSet = User_Model_Roles::getRoles();
		foreach ( self::$arrRoles as $arrRole ) {
			$checkedRole = $this->_getCheckedRole( $objCurrentRolesRowSet, $arrRole[User_Model_Db_Roles::COL_ID_ROLES] );
			if ( $checkedRole != null ) {
				// IF role is set, check content.
				if ( $checkedRole->{User_Model_Db_Roles::COL_ROLE} !== $arrRole[User_Model_Db_Roles::COL_ROLE] ) {
					$checkedRole->{User_Model_Db_Roles::COL_ROLE} = $arrRole[User_Model_Db_Roles::COL_ROLE];
					$checkedRole->{User_Model_Db_Roles::COL_ID_PARENT} = $arrRole[User_Model_Db_Roles::COL_ID_PARENT];
					$checkedRole->setTable( new User_Model_Db_Roles() );
					$checkedRole->save();
					$boolReload = TRUE;
				}

				continue;
			}

			// There is no ROW, the Role is not set, create one.
			$objRoles = new User_Model_Db_Roles();
			$objRoles->insert($arrRole);
			$boolReload = TRUE;
		}

		if ($boolReload) {
			User_Model_Roles::getRoles(FALSE);
		}

		return;
	}

	/**
	* (non-PHPdoc)
	* @see User_Model_User_Extra_Interface::setMainRow()
	* @return Qstat_User_Extra_Detail
	*/
	public function setMainRow(Bf_Db_Table_Row $objRow)
	{
		$this->_arrCurrentUserMainRow = $objRow;
		return $this;
	}

	public function getData()
	{
		$strCurrentSeriazedData = $this->_arrCurrentUserMainRow->{User_Model_Db_Users::COL_EXTRA_DATA};
		$arrCurrentData = unserialize($strCurrentSeriazedData);

		return (array)$arrCurrentData;
	}

	public function save($arrFormValues)
	{
		$strCurrentSeriazedData = $this->_arrCurrentUserMainRow->{User_Model_Db_Users::COL_EXTRA_DATA};
		$arrCurrentData = unserialize($strCurrentSeriazedData);
		$arrCurrentData = (array)$arrCurrentData;
		$arrNewData = array_merge($arrCurrentData,$arrFormValues);
		$strNewData = serialize($arrNewData);
		$this->_arrCurrentUserMainRow->{User_Model_Db_Users::COL_EXTRA_DATA} = $strNewData;

		return $this->_arrCurrentUserMainRow->save();
	}

	public function getForm($arrGetPost)
	{
		$objForm = new ZendX_JQuery_Form();

		if ( ! empty($arrGetPost) && ! empty( $arrGetPost[User_Model_Db_Users::COL_ID_ROLE] ) ) {
			// Check that role IS by site.
			$this->getSiteForm( $objForm, $arrGetPost[User_Model_Db_Users::COL_ID_ROLE] );
		} else {
			// Get Role From Default.
			$this->getSiteForm( $objForm, $this->_arrCurrentUserMainRow->{User_Model_Db_Users::COL_ID_ROLE} );
		}

		/*
		if ( ! empty($arrGetPost) && ! empty( $arrGetPost['sites'] ) ) {
		// Check that role IS by site.
		$intSiteId = (int) $arrGetPost['sites'];
		} else {
		// Get Role From Default.
		$arrData = $this->getData();
		if ( ! empty( $arrData['sites'] ) ) {
		$intSiteId = $arrData['sites'];
		} else {
		*/
		$intSiteId = 0;
		// }
		// }


		if ( ! empty($arrGetPost) && ! empty( $arrGetPost[User_Model_Db_Users::COL_ID_ROLE] ) ) {
			// Check that role IS by site.
			$this->getGroupForm( $objForm, $arrGetPost[User_Model_Db_Users::COL_ID_ROLE], $intSiteId );
			$this->getGroupForm( $objForm, $arrGetPost[User_Model_Db_Users::COL_ID_ROLE], $intSiteId, true );
		} else {
			// Get Role From Default.
			$this->getGroupForm( $objForm, $this->_arrCurrentUserMainRow->{User_Model_Db_Users::COL_ID_ROLE}, $intSiteId );
			$this->getGroupForm( $objForm, $this->_arrCurrentUserMainRow->{User_Model_Db_Users::COL_ID_ROLE}, $intSiteId, true );
		}

		return $objForm;
	}

	public function getSiteForm($objForm, $intRoleCode)
	{
		if ( in_array( $intRoleCode, $this->_arrRolesWithSite ) ) {
			$objElement = new Bf_Form_Element_DbSelect('sites');
			$objElement
			->setIdentityColumn( Qstat_Db_Table_Sites::COL_ID_SITES )
			->setDbSelect( Qstat_Db_Table_Sites::getPairSelect() )
			->setValueColumn( Qstat_Db_Table_Sites::COL_SITE_TITLE )
			->setRequired(TRUE)
			->setAllowEmpty(FALSE);
		} else {
			$objElement = new Bf_Form_Element_SelectNoValidate('sites');
			$objElement
			->addMultiOption(0,'LBL_NOT_RELEVANT')
			->setAttrib('disabled', 'disabled');
		}
		$objElement->setLabel('LBL_USER_FORM_SITES');

		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;

		if ( ! empty( $objUserDetails->{User_Model_Db_Users::COL_ID_ROLE} ) ) {
			switch ( $objUserDetails->{User_Model_Db_Users::COL_ID_ROLE} ) {
				case 4: // Group Member
				case 5: // Group Manager
				case 7: // Global Manager
					// They can only see the current level, not change it.
					$objElement->setAttrib('disabled', 'disabled');
					break;
				default:
					break;
			}
		}

		$objForm->addElement($objElement);
	}

	public function getGroupForm($objForm, $intRoleCode, $intSiteId, $isSubGroup = false)
	{
		if ( in_array( $intRoleCode, $this->_arrRolesWithGroups ) ) {
			if ($isSubGroup) {
				$objElement = new Bf_Form_Element_MultiDbSelect('subgroups');
				$objElement
				->setIdentityColumn( Qstat_Db_Table_Groups::COL_ID_GROUPS )
				->setDbSelect( Qstat_Db_Table_Groups::getPairSelect($intSiteId) )
				->setValueColumn( Qstat_Db_Table_Groups::COL_GROUP_NAME );
			} else {
				$objElement = new Bf_Form_Element_DbSelect('groups');
				$objElement
				->setIdentityColumn( Qstat_Db_Table_Groups::COL_ID_GROUPS )
				->setDbSelect( Qstat_Db_Table_Groups::getPairSelect($intSiteId) )
				->setValueColumn( Qstat_Db_Table_Groups::COL_GROUP_NAME )
				->setRequired(TRUE)
				->setAllowEmpty(FALSE);
			}
		} else {
			if ($isSubGroup) {
				$objElement = new Bf_Form_Element_MultiSelectNoValidate('subgroups');
			} else {
				$objElement = new Bf_Form_Element_SelectNoValidate('groups');
			}
			$objElement
			->addMultiOption(0, 'LBL_NOT_RELEVANT')
			->setAttrib('disabled', 'disabled');
		}

		if ($isSubGroup) {
			$objElement->setLabel('LBL_USER_FORM_SUBGROUPS');
		} else {
			$objElement->setLabel('LBL_USER_FORM_GROUPS');
		}

		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;

		if (! empty($objUserDetails->{User_Model_Db_Users::COL_ID_ROLE})) {
			switch ($objUserDetails->{User_Model_Db_Users::COL_ID_ROLE}) {
				case 4: // Group Member
				case 5: // Group Manager
					// They can only see the current level, not change it.
					$objElement->setAttrib('disabled', 'disabled');
					break;
				default:
					break;
			}
		}

		$objForm->addElement($objElement);
	}

	/**
	* (non-PHPdoc)
	* @see User_Model_User_Extra_Interface::getFormName()
	* @return string
	*/
	public function getFormName()
	{
		return self::FORM_NAME;
	}

	public function validateElements($objSubForm) {
		if ('disabled' == $objSubForm->getElement('sites')->getAttrib('disabled')){
			$objSubForm->removeElement($objSubForm->getElement('sites')->getName());
		}
		if ('disabled' == $objSubForm->getElement('groups')->getAttrib('disabled')){
			$objSubForm->removeElement($objSubForm->getElement('groups')->getName());
		}
	}

	public function _getCheckedRole($roles, $id_roles) {
		foreach ( $roles as $role ) {
			if ( $role->id_roles === $id_roles ) {
				return $role;
			}
		}

		return null;
	}
}
