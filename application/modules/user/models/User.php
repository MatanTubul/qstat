<?php

class User_Model_User
{
	CONST PREFIX_CODE = '{';
	CONST SUFFIX_CODE = '}';
	CONST CODE = '%';

	public function createNewPasswordForUserLogin($strLoginName)
	{
		$objUserDb = new User_Model_Db_Users();
		$objUserDbSelect = $objUserDb->select(TRUE);
		$objUserDbSelect->where(User_Model_Db_Users::COL_LOGIN . " = ?", $strLoginName);
		$objUserDbRow = $objUserDb->fetchRow($objUserDbSelect);
		if ( ! empty($objUserDbRow) ) {
			return $this->createNewPasswordForUser( $objUserDbRow->{User_Model_Db_Users::COL_ID_USERS} );
		}

		return false;
	}

	public function createPasswordRecoveryMail($passwordRecoveryUrl, $strLoginName)
	{
		$objUserDb = new User_Model_Db_Users();
		$objUserDbSelect = $objUserDb->select(TRUE);
		$objUserDbSelect->where(User_Model_Db_Users::COL_LOGIN . " = ?", $strLoginName);
		$objUserRow = $objUserDb->fetchRow($objUserDbSelect);
		if ( empty($objUserRow) ) {
			return false;
		}

		$objUserRow->{User_Model_Db_Users::COL_RECOVERY_HASH} = md5( $this->_generateNewPassword(6, 3) );
		if ( ! $objUserRow->save() ) {
			return false;
		}

		if ( ! $this->sendNewPasswordToUser($objUserRow, $passwordRecoveryUrl.$objUserRow->recovery_hash, 'LBL_TEXT_EMAIL_CHOOSE_PASSWORD' ) ) {
			return false;
		}

		return TRUE;
	}

	/**
	*
	* Crete new password for the user, send it to email and save it.
	*
	* @param int $intUserID
	* @return bool
	*/
	public function createNewPasswordForUser ($intUserID)
	{
		$objUserDb = new User_Model_Db_Users();
		$objUserDbRowSet = $objUserDb->find($intUserID);
		if ( ! $objUserDbRowSet->count() ) {
			return false;
		}

		$objUserDbRow = $objUserDbRowSet->current();
		// Generate new Password
		$strPassword = $this->_generateNewPassword(9, 8);
		// Send new password to email
		if ( ! $this->sendNewPasswordToUser($objUserDbRow, $strPassword, 'LBL_TEXT_EMAIL_NEW_PASSWORD') ) {
			return false;
		}

		// Save new password
		$objUserDbRow->{User_Model_Db_Users::COL_PWD} = md5($strPassword);
		if ( ! $objUserDbRow->save() ) {
			return false;
		}

		return true;
	}

	/**
	*
	* Generates rundom password
	*
	* @author
	* @link http://www.webtoolkit.info/php-random-password-generator.html
	*
	* @param int $length
	* @param int $strength
	* @return string
	*/
	private function _generateNewPassword ($length = 9, $strength = 0)
	{
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength & 2) {
			$vowels .= "AEUY";
		}
		if ($strength & 4) {
			$consonants .= '23456789';
		}
		if ($strength & 8) {
			$consonants .= '@#$%';
		}
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i ++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}

	public function sendNewPasswordToUser($objUserRow, $parameter, $messageCode)
	{
		$strNewMailMessage = $this->_preperePasswordMsg($parameter, $messageCode);
		$objMail = new Zend_Mail();
		$objMail->setBodyHtml($strNewMailMessage);
		$objMail->addTo($objUserRow->{User_Model_Db_Users::COL_EMAIL}, $objUserRow->{User_Model_Db_Users::COL_FIRST_NAME} . ' ' . $objUserRow->{User_Model_Db_Users::COL_LAST_NAME});
		$objZendTRanslate = Zend_Registry::get('Zend_Translate');
		$objMail->setSubject($objZendTRanslate->translate('LBL_SUBJECT_EMAIL_NEW_PASSWORD'));
		try {
			$objMail->send();
		} catch (Zend_Mail_Exception $objException) {
			return false;
		}

		return true;
	}

	/**
	*
	* Prepere Message for sending with password
	* @param string $strNewPassword
	* $return string
	*/
	private function _preperePasswordMsg($parameter, $messageCode)
	{
		$objZendTRanslate = Zend_Registry::get('Zend_Translate');
		$strNewMessage = $objZendTRanslate->translate($messageCode);
		$strCode = self::PREFIX_CODE . self::CODE . self::SUFFIX_CODE;
		$strPattern = '/[' . $strCode . ']/u';
		$strMessageAfterReplace = preg_replace($strPattern, $parameter, $strNewMessage);

		return $strMessageAfterReplace;
	}

	public static function makeLogin ($strUsername, $strPassword)
	{
		$username = $strUsername;
		$password = $strPassword;

		$auth = Zend_Auth::getInstance();

		$authAdapter = new Zend_Auth_Adapter_DbTable();
		$authAdapter->setTableName(User_Model_Db_Users::TBL_NAME)
		->setIdentityColumn(User_Model_Db_Users::COL_LOGIN)
		->setCredentialColumn(User_Model_Db_Users::COL_PWD)
		->setCredentialTreatment('md5(?)')
		->setIdentity($username)
		->setCredential($password);
		// Remove it from Array...
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid()) {
			$session = new Zend_Session_Namespace("user");
			$objUserRow = $authAdapter->getResultRowObject();
			$arrCustomColumns = self::loadCustomColumns($objUserRow);
			$objUserRow->{User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS} = $arrCustomColumns[User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS];
			$objUserRow->{User_Model_Db_Users::COL_CUSTOM_COLUMNS} = $arrCustomColumns[User_Model_Db_Users::COL_CUSTOM_COLUMNS];
			$session->userDetails = $objUserRow;
			$session->userDetails->password=$strPassword;
			$session->userDetails->extraArray = @unserialize($objUserRow->{User_Model_Db_Users::COL_EXTRA_DATA});
		}
		return $result;
	}

	public static function makeLoginByName ($strUsername)
	{
		$username = $strUsername;

		$auth = Zend_Auth::getInstance();
		$authAdapter = new Zend_Auth_Adapter_DbTable();
		$authAdapter->setTableName(User_Model_Db_Users::TBL_NAME)
		->setIdentityColumn(User_Model_Db_Users::COL_LOGIN)
		->setCredentialColumn(User_Model_Db_Users::COL_LOGIN)
		->setIdentity($username)
		->setCredential($username);
		// Remove it from Array...
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid()) {
			$session = new Zend_Session_Namespace("user");
			$objUserRow = $authAdapter->getResultRowObject();
			$arrCustomColumns = self::loadCustomColumns($objUserRow);
			$objUserRow->{User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS} = $arrCustomColumns[User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS];
			$objUserRow->{User_Model_Db_Users::COL_CUSTOM_COLUMNS} = $arrCustomColumns[User_Model_Db_Users::COL_CUSTOM_COLUMNS];
			$session->userDetails = $objUserRow;
			$session->userDetails->extraArray = @unserialize($objUserRow->{User_Model_Db_Users::COL_EXTRA_DATA});
		}
		return $result;
	}

	/**
	*
	* @param stdClass|int $mixUserData
	* @return stdClass
	*/
	public static function loadCustomColumns ($mixUserData)
	{
		if ($mixUserData instanceof stdClass) {
			$objUserData = $mixUserData;
		} else {
			$intUserId = (int) $mixUserData;
			if (! empty($intUserId)) {
				$objUserTable = new User_Model_Db_Users();
				$objUserData = $objUserTable->find($intUserId)->current();
			} else {
				throw new Exception('Wrong User Data');
			}
		}

		$arrExtraData = @unserialize($objUserData->{User_Model_Db_Users::COL_EXTRA_DATA});
		$boolUseCustomFields = (bool) $objUserData->{User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS};
		if ($boolUseCustomFields) {
			$arrResult[User_Model_Db_Users::COL_CUSTOM_COLUMNS] = @unserialize($objUserData->{User_Model_Db_Users::COL_CUSTOM_COLUMNS});
		} else {
			//no user defined custom fields
			if (! empty($arrExtraData['groups'])) {
				$intGroupId = (int) $arrExtraData['groups'];
				if (! empty($intGroupId)) {
					//User is group related, check group
					$objGroupTable = new Qstat_Db_Table_Groups();
					$objGroupRow = $objGroupTable->find($intGroupId)->current();
					$boolUseCustomFields = (bool) $objGroupRow->{Qstat_Db_Table_Groups::COL_USE_CUSTOM_COLUMNS};
					if ($boolUseCustomFields) {
						$arrResult[User_Model_Db_Users::COL_CUSTOM_COLUMNS] = @unserialize($objGroupRow->{Qstat_Db_Table_Groups::COL_CUSTOM_COLUMNS});
					}
				}
			}
			if (! empty($arrExtraData['sites'])) {
				$intSiteId = (int) $arrExtraData['sites'];
				if (! $boolUseCustomFields && ! empty($intSiteId)) {
					$objSiteTable = new Qstat_Db_Table_Sites();
					$objSiteRow = $objSiteTable->find($intSiteId)->current();
					$boolUseCustomFields = (bool) $objSiteRow->{Qstat_Db_Table_Sites::COL_USE_CUSTOM_COLUMNS};
					if ($boolUseCustomFields) {
						$arrResult[User_Model_Db_Users::COL_CUSTOM_COLUMNS] = @unserialize($objSiteRow->{Qstat_Db_Table_Sites::COL_CUSTOM_COLUMNS});
					}
				}
			}
		}

		if (empty($arrResult[User_Model_Db_Users::COL_CUSTOM_COLUMNS])) {
			$arrResult[User_Model_Db_Users::COL_CUSTOM_COLUMNS] = array();
		}

		$arrResult[User_Model_Db_Users::COL_USE_CUSTOM_COLUMNS] = $boolUseCustomFields;
		return $arrResult;
	}

	public static function isSysAdmin(){
		$session = new Zend_Session_Namespace("user");
		if (!empty($session->userDetails->id_users)&&$session->userDetails->id_users==4){
			return true;
		}else{
			return false;
		}
	}

	public static function isOmer(){
		$session = new Zend_Session_Namespace("user");
		if (!empty($session->userDetails->id_users)&&$session->userDetails->id_users==5){
			return true;
		}else{
			return false;
		}
	}

	public static function getPassword(){
		$session = new Zend_Session_Namespace("user");
		if (!empty($session->userDetails->password)){
			return $session->userDetails->password;
		}else{
			return null;
		}
	}

	public static function getUserData(){
		$session = new Zend_Session_Namespace("user");
		if (!empty($session->userDetails)){
			return $session->userDetails;
		}else{
			return null;
		}
	}
}
