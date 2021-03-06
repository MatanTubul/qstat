<?php
class User_Model_Roles
{
	CONST DEFAULT_ROLE_GUEST = '1';
	CONST DEFAULT_ROLE_GROUP = '4';
	CONST DEFAULT_ROLE_SYSADMIN = '3';

	public static $arrRoles = array(
		self::DEFAULT_ROLE_GUEST => array(
			User_Model_Db_Roles::COL_ID_ROLES => 1,
			User_Model_Db_Roles::COL_ID_PARENT => 0,
			User_Model_Db_Roles::COL_ROLE => 'guest',
		),
		self::DEFAULT_ROLE_GROUP => array(
			User_Model_Db_Roles::COL_ID_ROLES => 4,
			User_Model_Db_Roles::COL_ID_PARENT => 1,
			User_Model_Db_Roles::COL_ROLE => 'group'
		),
		self::DEFAULT_ROLE_SYSADMIN => array(
			User_Model_Db_Roles::COL_ID_ROLES => 3,
			User_Model_Db_Roles::COL_ID_PARENT => 7,
			User_Model_Db_Roles::COL_ROLE => 'sysadmin'
		)
	);

	/**
	*
	* Enter description here ...
	* @throws Exception
	* @return Zend_Db_Table_Rowset
	*/
	public static function getRoles($boolCheckSession = TRUE){
		$objSessionUserData = new Zend_Session_Namespace('userData');

		// Add roles to the Acl element
		if ( $boolCheckSession && ! empty( $objSessionUserData->roles ) ) {
			return $objSessionUserData->roles;
		}

		try {
			$roles = self::getRolesFromDb();

			if ( ! $roles->count() ) {
				$roles = self::_getDefaultRoles();
			}
		} catch (Zend_Db_Exception $objE) {
			$roles = self::_getDefaultRoles();
		} catch (Exception $objE) {
			throw new Exception("", 0, $objE);
		}

		return $roles;
	}

	/**
	*
	* Enter description here ...
	* @return Zend_Db_Table_Rowset
	*/
	public static function getRolesFromDb() {
		$objRoles = new User_Model_Db_Roles();
		$objRolesSelect = $objRoles->select(TRUE);
		$objRolesSelect->where(User_Model_Db_Resources::COL_IS_DELETED . ' = ?', false);

		$objRolesSelect->order(User_Model_Db_Roles::COL_ID_PARENT);

		$objRolesRowSet = $objRoles->fetchAll($objRolesSelect);

		return $objRolesRowSet;
	}

	private static function _getDefaultRoles() {
		$objRoles = new User_Model_Db_Roles();

		foreach (self::$arrRoles as $arrRole) {
			$objRoles->insert($arrRole);
		}

		return self::getRolesFromDb();
	}

	/**
	*
	* Enter description here ...
	* @return Zend_Db_Select
	*/
	public static function getPairSelect(){
		$objModel = new User_Model_Db_Roles();
		$objSelect = $objModel->select(TRUE);
		$objSelect->reset(Zend_Db_Select::COLUMNS);
		$objSelect->columns(array(User_Model_Db_Roles::COL_ID_ROLES,User_Model_Db_Roles::COL_ROLE));
		$objSelect->where(User_Model_Db_Roles::COL_IS_DELETED." = ?", 0);

		return $objSelect;
	}
}
