<?php

class User_IndexController extends Zend_Controller_Action
{
	public function forgotPasswordAction ()
	{
		$objForm = new User_Form_ForgotPassword();
		if ( $this->_request->isPost() ) {
			$formData = $this->_request->getPost();
			if ( $objForm->isValid($formData) ) {
				//  Get User Data.
				$objUserModel = new User_Model_User();
				$passwordRecoveryUrl =
				$this->view->serverUrl().
				$this->view->url(array(
					'module' => 'user',
					'controller' => 'index',
					"action" => "recover-password",
					"recovery-hash" => '',
				));

				if ( $objUserModel->createPasswordRecoveryMail( $passwordRecoveryUrl, $formData[User_Model_Db_Users::COL_LOGIN] ) ) {
					Labels_Model_SystemLabels::setJgrowlMessage("LBL_PASSWORD_RECOVERY_SENT");
				} else {
					Labels_Model_SystemLabels::setJgrowlMessage("LBL_PASSWORD_GENERATION_FAIL");
				}
			} else {
				Labels_Model_SystemLabels::setJgrowlMessage("LBL_NO_MATCH_FOUND");
			}

			$this->_redirect( $this->getRequest()->getRequestUri() );
		}

		$this->view->objForm = $objForm;
		$this->view->arrActions = array(
			array(
				'module' => 'user',
				'controller' => 'index',
				"action" => "forgot-password",
				"onClick" => '$("#'.$objForm->getAttrib('id').'").submit();',
				"name" => 'LBL_BUTTON_USER_PASSWORD_NEW',
			),
		);
	}

	public function indexAction ()
	{
		$strUrl = $this->view->url(array('module' => 'user', 'controller' => 'index', 'action' => 'del'), null, false, true);

		$arrOptions = array("hiddengrid" => false, "editurl" => $strUrl);
		$arrOptions['rowNum'] = 100;
		$arrOptions['multiselect'] = true;

		$objUserTable = new User_Model_Db_Users();
		$objSelect = $objUserTable->select();
		$objSelect->where(User_Model_Db_Users::TBL_NAME.'.'.User_Model_Db_Users::COL_IS_DELETED." = ?",FALSE);

		$grid = new Ingot_JQuery_JqGrid('users', new Ingot_JQuery_JqGrid_Adapter_DbTableSelect($objSelect), $arrOptions);

		$grid->setIdCol(User_Model_Db_Users::COL_ID_USERS);
		$strUrl = $this->view->url(array('module' => 'user', 'controller' => 'index', 'action' => 'view'), null, true, false);
		$grid->setOption('ondblClickRow', "function(rowId, iRow, iCol, e) { if (rowId) { var newTabOpened = window.open( '".$strUrl."/UserId/' + rowId, '_blank' ); newTabOpened.focus(); } }");
		$grid->setOption('loadComplete', "function(data) { prepareSubGroupCell( jQuery(this) ) }");
		$grid->addColumn(new Ingot_JQuery_JqGrid_Column(User_Model_Db_Users::COL_LOGIN));
		$grid->addColumn(new Ingot_JQuery_JqGrid_Column(User_Model_Db_Users::COL_FIRST_NAME));
		$grid->addColumn(new Ingot_JQuery_JqGrid_Column(User_Model_Db_Users::COL_LAST_NAME));
		$grid->addColumn(new Ingot_JQuery_JqGrid_Column_Decorator_Link(new Ingot_JQuery_JqGrid_Column(User_Model_Db_Users::COL_EMAIL), array('link' => 'mailto:%s')));
		$grid->addColumn(new Ingot_JQuery_JqGrid_Column(User_Model_Db_Users::COL_PHONE));

		Ingot_JQuery_JqGrid_Column_DoubleColumn::createSelectColumn($grid, 'Roles');

		$objSites = new Qstat_Db_Table_Sites();
		$objSitesSelect = $objSites->getPairSelect();
		$arrPairs = $objSites->getAdapter()->fetchPairs($objSitesSelect);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select(
			new Ingot_JQuery_JqGrid_Column( 'sites', array( 'useHaving' => true, 'customField' => User_Model_Db_Users::COL_EXTRA_DATA, ) ), array( "value" => $arrPairs, )
		);
		$grid->addColumn(new Qstat_JQuery_JqGrid_Column_Decorator_UserExtra($column, array('values' => $arrPairs)));

		$objGroups = new Qstat_Db_Table_Groups();
		$objSitesSelect = $objGroups->getPairSelect();
		$arrPairs = $objGroups->getAdapter()->fetchPairs($objSitesSelect);
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select(
			new Ingot_JQuery_JqGrid_Column( 'groups', array( 'useHaving' => true, 'customField' => User_Model_Db_Users::COL_EXTRA_DATA, ) ), array( "value" => $arrPairs, )
		);
		$grid->addColumn(new Qstat_JQuery_JqGrid_Column_Decorator_UserExtra($column, array('values' => $arrPairs)));

		$preliminaryColumn = new Ingot_JQuery_JqGrid_Column('subgroups', array(
			'useHaving' => true,
			'customField' => User_Model_Db_Users::COL_EXTRA_DATA,
			'title' => false,
			'classes' => 'sub-groups-column',
		));
		$column = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select( $preliminaryColumn, array( "value" => $arrPairs, ) );
		$grid->addColumn( new Qstat_JQuery_JqGrid_Column_Decorator_UserExtraSubGroups( $column, array( 'values' => $arrPairs, ) ) );

		$objGridPager = $grid->getPager();
		$objGridPager->setDefaultDel();
		$objGridPager->setDefaultSearch();

		$aclInstance = User_Model_Acl::getInstance();
		if ( $aclInstance->checkPermissions('user', 'index', 'edit-multi') ) {
			$grid->registerPlugin(
				new Ingot_JQuery_JqGrid_Plugin_CustomButton(
					array( "caption" => "", "title" => "Multi Edit Selected", "buttonicon" => "ui-icon-cart", "onClickButton" => "function(){ editMulti(); }", "position" => "first", )
				)
			);
		}
		$grid->registerPlugin( new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter() );
		$this->view->grid = $grid->render();
		$arrActions = array();
		$arrActions[] = array('module' => 'user', 'controller' => 'index', "action" => "edit", "name" => 'Add New User');
		$this->view->arrActions = $arrActions;
	}

	public function delAction ()
	{
		$intId = $this->_request->getParam('id');

		// Get model object
		$objSystemSettings = new User_Model_Db_Users();

		if (! empty($intId)) {
			$objRowSet = $objSystemSettings->find($intId);

			if ($objRowSet->count() > 0) {
				$objRow = $objRowSet->current();
			} else {
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
				return;
			}
		} else {
			$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_ERROR_UNAUTHORIZED"));
			return;
		}

		if ("del" == $this->_request->getParam("oper")) {
			if ($objRow->delete()) {
				// Deleted
				$this->view->data = array("code" => "ok", "msg" => "");
			} else {
				// Delete failed
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_DEL_FAIL"));
			}
		} else {
			if ($this->_request->isPost()) {
				$arrData = $this->_request->getPost();
				$objRow->setFromArray($arrData);
				$intId = $objRow->save();
				if (! empty($intId)) {
					$this->view->data = array("code" => "ok", "msg" => "");
				} else {
					$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));
				}
			} else {
				$this->view->data = array("code" => "error", "msg" => $this->view->translate("LBL_UPDATE_FAIL"));

			}
		}
		Labels_Model_SystemLabels::initLables(TRUE);
	}


	public function editAction ()
	{
		// Get user object.
		$objUserData = new User_Model_Db_Users();
		$intUserID = intval( $this->_request->getParam("UserId", 0) );
		if ($intUserID) {
			$objUserRowSet = $objUserData->find($intUserID);
			if ( empty($objUserRowSet) ) {
				$strUrl = $this->view->url( array( 'module' => 'users', 'controller' => 'index', 'action' => 'index', ), null, true );
				$this->_redirect($strUrl);

				return;
			}

			$objUserRow = $objUserRowSet->current();
		} else {
			$objUserRow = $objUserData->createRow();
			$objRolesTable = new User_Model_Db_Roles();
			$objUserRow->{User_Model_Db_Users::COL_ID_ROLE} = $objRolesTable->getRoleId('group');
		}

		// Get Extra Data.
		$arrOptions = $this->getInvokeArg('bootstrap')->getOptions();
		$arrExtraData = $arrOptions['extraData']['user']['userDetails'];
		// Create form.
		$objForm = new User_Form_UserDetails( array( 'currentUserId' => $intUserID,) );
		foreach ( (array) $arrExtraData as $strExtraDataClass ) {
			$objExtraData = new $strExtraDataClass();
			if ( ! $objExtraData instanceof User_Model_User_Extra_Interface ) {
				continue;
			}

			$objExtraData->setMainRow($objUserRow);
			$objSubForm = $objExtraData->getForm( $this->getRequest()->getParams() );
			$objExtraData->validateElements($objSubForm);
			$objForm->addSubForm( $objSubForm, $objExtraData->getFormName() );
			$objForm->populate($objExtraData->getData());
		}
		$objForm->validateElements();
		$objForm->populate($objUserRow->toArray());

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();

			if ( $objForm->isValid($formData) ) {
				$objUserRow->setFromArray($objForm->getValues());
				$intUserID = $objUserRow->save();
				if ($intUserID) {
					// Save Extra Data.
					foreach ((array) $arrExtraData as $strExtraDataClass) {
						$objExtraData = new $strExtraDataClass();
						if ( ! $objExtraData instanceof User_Model_User_Extra_Interface ) {
							continue;
						}

						$objSubForm = $objForm->getSubForm($objExtraData->getFormName());
						if ( ! empty($objSubForm) ) {
							$objExtraData->setMainRow($objUserRow);
							$objExtraData->save($objSubForm->getValues());
						}
					}
					$intOriginalId = $this->_request->getParam("UserId", 0);
					if (empty($intOriginalId)) {
						$objUserModel = new User_Model_User();
						if ($objUserModel->createNewPasswordForUser($intUserID)) {
							Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_OK");
							$strUrl = $this->view->url(array('module' => 'user', 'controller' => 'index', 'action' => 'view', "UserId" => $intUserID), null, true);
							$this->_redirect($strUrl);
						} else {
							Labels_Model_SystemLabels::setJgrowlMessage("LBL_PASSWORD_GENERATION_FAIL");
						}
						echo $this->Actions();
					} else {
						Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_OK");
						$strUrl = $this->view->url(array('module' => 'user', 'controller' => 'index', 'action' => 'view', "UserId" => $intUserID), null, true);
						$this->_redirect($strUrl);
					}
				} else {
					Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_FAIL");
				}
			} else {
				$objForm->populate($formData);
				Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_FAIL");
			}
		}

		$this->view->form = $objForm;
		$arrButtons[] = array( 'module' => 'user', 'controller' => 'index', "action" => "edit", "onClick" => '$("#' . $objForm->getAttrib('id') . '").submit();', "name" => 'LBL_BUTTON_USER_EDIT_SAVE', );
		if ( ! empty($intUserID) ) {
			$arrButtons[] = array( 'module' => 'user', 'controller' => 'index', "action" => "view", "name" => 'LBL_BUTTON_USER_DETAILS', "params" => array( "UserId" => $intUserID, ), );
		}
		$arrButtons[] = array( 'module' => 'user', 'controller' => 'index', "action" => "index", "name" => 'LBL_BUTTON_USER_LIST', );
		$this->view->arrActions = $arrButtons;
	}

	public function editMultiAction()
	{
		// Create form.
		$objForm = new User_Form_UserMultiEdit();

		if ($this->_request->isPost()) {
			$usersIds = $this->_request->getParam("users-ids", '');
			$usersIds = explode(',', $usersIds);

			// Get users object.
			$objUserData = new User_Model_Db_Users();

			// Get Extra Data.
			$arrOptions = $this->getInvokeArg('bootstrap')->getOptions();
			$arrExtraData = $arrOptions['extraData']['user']['userDetails'];

			$isUpdateValid = true;
			foreach ( $usersIds as $intUserID ) {
				$intUserID = intval( trim($intUserID) );
				if ( ! $intUserID) {
					$isUpdateValid = false;
					break;
				}

				$objUserRowSet = $objUserData->find($intUserID);
				if ( empty($objUserRowSet) ) {
					$isUpdateValid = false;
					break;
				}
				$objUserRow = $objUserRowSet->current();
				$formData = $this->_request->getPost();

				if ( ! $objForm->isValid($formData) ) {
					$isUpdateValid = false;
					break;
				}
				$objUserRow->setFromArray( $objForm->getValues() );
				$intUserID = $objUserRow->save();
				if ( ! $intUserID) {
					$isUpdateValid = false;
					break;
				}

				// Save Extra Data.
				foreach ( (array) $arrExtraData as $strExtraDataClass) {
					$objExtraData = new $strExtraDataClass();
					if ( ! $objExtraData instanceof User_Model_User_Extra_Interface ) {
						continue;
					}
					$objExtraData->setMainRow($objUserRow);
					$objSubForm = $objExtraData->getForm( $this->getRequest()->getParams() );
					$objExtraData->validateElements($objSubForm);
					$extraValues = array_intersect_key( $objForm->getValues(), array( 'sites' => '', 'groups' => '', ) );
					if ( ! $objExtraData->save($extraValues) ) {
						$isUpdateValid = false;
						break;
					}
				}
			}

			if ($isUpdateValid) {
				Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_OK");
				echo '<script type="text/javascript">window.opener.location.reload();window.close();</script>';
				exit;
			} else {
				Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_FAIL");
			}
		}

		$this->view->form = $objForm;
		$arrButtons[] = array( 'module' => 'user', 'controller' => 'index', "action" => "edit", "onClick" => '$("#'.$objForm->getAttrib('id').'").submit();', "name" => 'LBL_BUTTON_USER_EDIT_SAVE', );
		$arrButtons[] = array( 'module' => 'user', 'controller' => 'index', "action" => "index", "name" => 'LBL_BUTTON_USER_LIST', );
		$this->view->arrActions = $arrButtons;
		$this->_helper->viewRenderer->setRender('edit');
	}

	public function viewAction ()
	{
		/// Get user object.
		$objUserData = new User_Model_Db_Users();
		$intUserID = (int) $this->_request->getParam("UserId", 0);
		if ( empty($intUserID) ) {
			// @TODO redirect to error.
			$strUrl = $this->view->url(array('module' => 'users', 'controller' => 'index', 'action' => 'index'), null, true);
			$this->_redirect($strUrl);
			return;
		}

		$objUserRowSet = $objUserData->find($intUserID);
		if ( empty($objUserRowSet) ) {
			// @TODO redirect to error.
			$strUrl = $this->view->url(array('module' => 'users', 'controller' => 'index', 'action' => 'index'), null, true);
			$this->_redirect($strUrl);
			return;
		}

		$objUserRow = $objUserRowSet->current();

		// Get Extra Data.
		$arrOptions = $this->getInvokeArg('bootstrap')->getOptions();
		$arrExtraData = $arrOptions['extraData']['user']['userDetails'];
		// Create form.
		$objForm = new User_Form_UserDetails();
		$objForm->populate( $objUserRow->toArray() );
		foreach ( (array) $arrExtraData as $strExtraDataClass ) {
			$objExtraData = new $strExtraDataClass();
			if ( ! $objExtraData instanceof User_Model_User_Extra_Interface ) {
				continue;
			}

			$objExtraData->setMainRow($objUserRow);
			$objSubForm = $objExtraData->getForm( $this->getRequest()->getParams() );
			foreach ($objSubForm as $objElement) {
				$objElement->setAttrib('disabled', 'disabled');
			}
			$objForm->addSubForm( $objSubForm, $objExtraData->getFormName() );
			$objForm->populate( $objExtraData->getData() );
		}

		// Close all fields for edeting.
		foreach ($objForm as $objElement) {
			$objElement->setAttrib('disabled', 'disabled');
		}

		// Render.
		$this->view->objForm = $objForm;
		// Create form.
		$arrButtons[] = array('module' => 'user', 'controller' => 'index', "action" => "edit", "name" => "LBL_BUTTON_USER_DETAILS_EDIT", "params" => array("UserId" => $intUserID));
		$arrButtons[] = array('module' => 'catalog', 'controller' => 'index', "action" => "view-settings", "name" => 'LBL_BUTTON_USER_CATALOG_COLUMNS', "params" => array(User_Model_Db_Users::COL_ID_USERS => $intUserID));
		$arrButtons[] = array('module' => 'user', 'controller' => 'index', "action" => "update-password", "name" => "LBL_BUTTON_USER_CHANGE_PWD", "params" => array("UserId" => $intUserID));
		$arrButtons[] = array('module' => 'user', 'controller' => 'authentication', "action" => "takepermition", "name" => "LBL_BUTTON_USER_ASSUME_USER_PERMITION", "params" => array("UserId" => $intUserID));
		$arrButtons[] = array('module' => 'user', 'controller' => 'index', "action" => "index", "name" => "LBL_BUTTON_USER_LIST");
		$this->view->arrActions = $arrButtons;
	}

	public function inituserdetailsAction ()
	{
		$objUserTable = new User_Model_User();
		$objUserTable->forceUserDetailUpdate();
		Bf_Static::setJgrowlMessage("LBL_UPDATE_OK");
		$strUrl = $this->view->url(array('module' => 'semesters', 'controller' => 'semester', 'action' => 'index'), null, true);
		$this->_redirect($strUrl);
	}

	public function initAction ()
	{
		$intRequestInit = $this->_request->getParam("initstart", 0);
		if (! empty($intRequestInit)) {
			// Start table initialization
			$arrClassActions = array();
			$handle = opendir(realpath(dirname(__FILE__) . self::DIRECTORY));
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && ! is_dir($file)) {
						// Now include file
						include_once realpath(dirname(__FILE__) . self::DIRECTORY) . '/' . $file;
						$info = pathinfo($file);
						$file_name = basename($file, '.' . $info['extension']);
						$intControllerStringPosition = strpos($file_name, self::CONTROLLER);
						if (! empty($intControllerStringPosition)) {
							$strControllerName = substr($file_name, 0, $intControllerStringPosition);
							$strControllerName = self::lcfirst($strControllerName);
							// Read with Reflection
							$className = self::CLASS_PREFIX . $file_name;
							$resourceName = $objReflection = new ReflectionClass($className);
							$arrClassMethods = $objReflection->getMethods();
							// Get Actions Names
							foreach ($arrClassMethods as $objMethod) {
								$intStringPosition = strpos($objMethod->name, self::ACTION);
								if (! empty($intStringPosition)) {
									$strActionName = substr($objMethod->name, 0, $intStringPosition);
									$arrClassActions[$strControllerName][$strActionName][] = User_Model_Roles::DEFAULT_ROLE_GUEST;
								}
							}
						}
					}
				}
				closedir($handle);
			}
		}
		$arrButtons = array();
		$arrButtons[] = array(
			"module" => "prj",
			"controller" => "init",
			"action" => "index",
			"onClick" => 'document.location.href="'.$this->view->url(array('module' => 'prj', "controller" => "init", "action" => "index", "initstart" => "1")) . '";',
			"name" => "LBL_BUTTON_ADMIN_INIT_TABLE",
		);
		$this->view->arrActions = $arrButtons;
	}

	public function updatePasswordAction ()
	{
		$intUserId = (int) $this->_request->getParam("UserId", 0);
		if ( empty($intUserId) ) {
			// Cannot login. Send to index.
			Labels_Model_SystemLabels::setJgrowlMessage("LBL_INCORRECT_DATA");
		}

		$objForm = new User_Form_UpdatePassword();
		//  Get User Data
		$objUserData = new User_Model_Db_Users();
		$objUserDataSelect = $objUserData->select();
		$objUserDataSelect->where(User_Model_Db_Users::COL_ID_USERS . " = ?", $intUserId);
		$objUserDataRow = $objUserData->fetchRow($objUserDataSelect);
		if ( empty($objUserDataRow) ) {
			// echo $this->Actions();
			exit;
		}

		if ( $this->_request->isPost() ) {
			$formData = $this->_request->getPost();
			if ( $objForm->isValid($formData) ) {
				$objUserDataRow->{User_Model_Db_Users::COL_PWD} = md5($formData['password']);
				$intUserID = $objUserDataRow->save();
				if ($intUserID) {
					// Get user info.
					$objUserDb = new User_Model_Db_Users();
					$objUserDbRowSet = $objUserDb->find($intUserID);
					$objUserDbRow = $objUserDbRowSet->current();

					// Send email.
					$userObj = new User_Model_User();
					$userObj->sendNewPasswordToUser($objUserDbRow, $formData['password'], 'LBL_TEXT_EMAIL_NEW_PASSWORD');

					Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_OK");
					$strUrl = $this->view->url(array('module' => 'user', 'controller' => 'index', 'action' => 'view', "UserId" => $intUserID), null, true);
					$this->_redirect($strUrl);
				} else {
					Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_FAIL");
				}
			} else {
				Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_FAIL");
			}
		}

		$this->view->objForm = $objForm;
		$arrButtons[] = array('module' => 'user', 'controller' => 'index', "action" => "edit", "onClick" => '$("#' . $objForm->getAttrib('id') . '").submit();', "name" => 'LBL_BUTTON_USER_PASSWORD_SAVE');
		$arrButtons[] = array('module' => 'user', 'controller' => 'index', "action" => "view", "name" => 'LBL_BUTTON_USER_DETAILS', "params" => array("UserId" => $intUserId));
		$arrButtons[] = array('module' => 'user', 'controller' => 'index', "action" => "index", "name" => 'LBL_BUTTON_USER_LIST');
		$this->view->arrActions = $arrButtons;
	}

	public function recoverPasswordAction ()
	{
		try {
			$recoveryHash = $this->_request->getParam('recovery-hash', '');
			if ( empty($recoveryHash) ) {
				// Cannot to continue with recovery flow. Send to index.
				throw new Exception('LBL_INCORRECT_DATA');
			}

			// Get User Data.
			$objUserData = new User_Model_Db_Users();
			$objUserDataSelect = $objUserData->select()
			->where(User_Model_Db_Users::COL_RECOVERY_HASH . " = ?", $recoveryHash)
			->where("TIMESTAMPDIFF( SECOND, `updated_on`, NOW() ) < ". User_Model_Db_Users::PASSWORD_RECOVERY_TIME_RESTRUCT);
			$objUserDataRow = $objUserData->fetchRow($objUserDataSelect);
			if ( empty($objUserDataRow) ) {
				throw new Exception('LBL_INCORRECT_DATA.');
			}

			$objForm = new User_Form_UpdatePassword();
			$this->view->objForm = $objForm;
			$this->view->arrActions = array(
				array(
					'module' => 'user',
					'controller' => 'index',
					"action" => "recover-password",
					"onClick" => '$("#' . $objForm->getAttrib('id') . '").submit();',
					"name" => 'LBL_BUTTON_USER_PASSWORD_SAVE',
				),
			);

			if ( $this->_request->isPost() ) {
				$formData = $this->_request->getPost();
				if ( ! $objForm->isValid($formData) ) {
					throw new Exception('LBL_UPDATE_FAIL');
				}

				$objUserDataRow->{User_Model_Db_Users::COL_PWD} = md5($formData['password']);
				$objUserDataRow->{User_Model_Db_Users::COL_RECOVERY_HASH} = '';
				$intUserID = $objUserDataRow->save();
				if ( ! $intUserID) {
					throw new Exception('LBL_UPDATE_FAIL');
				}

				Labels_Model_SystemLabels::setJgrowlMessage("LBL_UPDATE_OK");
				$strUrl = $this->view->url(array('module' => 'user', 'controller' => 'index', 'action' => 'view', "UserId" => $intUserID), null, true);
				$this->_redirect($strUrl);
			}
		} catch (Exception $e) {
			Labels_Model_SystemLabels::setJgrowlMessage( $e->getMessage() );
		}
	}
}
