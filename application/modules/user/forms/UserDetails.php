<?php

class User_Form_UserDetails extends ZendX_JQuery_Form
{
	private $_currentUserId = 0;

	public function init () {
		$this->addPrefixPath('Bf_Form_Element_', 'Bf/Form/Element/', Zend_Form::ELEMENT);

		// Form Elements & Other Definitions Here.
		$objOptions = new Zend_Config_Xml(dirname(__FILE__) . '/../configs/forms/forms.xml');
		$this->setConfig($objOptions->profile);

		foreach ($this as $objElement) {
			$currentElementName = $objElement->getName();
			if ( ! in_array( $currentElementName, array( 'username', 'email', ) ) ) {
				continue;
			}

			$validatorOptions = array(
				'table' => User_Model_Db_Users::TBL_NAME,
				'field' => $currentElementName,
				'messages' => array(
					'recordFound' => 'This '.$currentElementName.' already exists',
				),
			);
			if ( $this->_currentUserId ) {
				// This is editing of the exist user, exclude current username and email fields from searching.
				$validatorOptions['exclude'] = array(
					'field' => User_Model_Db_Users::COL_ID_USERS,
					'value' => $this->_currentUserId,
				);
			}

			$objElement->addValidator('Db_NoRecordExists', TRUE, $validatorOptions);
		}

		// Check permissions.
		$objUserSessionData = new Zend_Session_Namespace('user');
		$objUserDetails = $objUserSessionData->userDetails;

		if ( ! empty( $objUserDetails->{User_Model_Db_Users::COL_ID_ROLE} ) ) {
			switch ( $objUserDetails->{User_Model_Db_Users::COL_ID_ROLE} ) {
				case 4: // Group Member
				case 5: // Group Manager
				case 7: // Global Manager
					// They can only see the current level, not change it.
					$this->getElement(User_Model_Db_Users::COL_ID_ROLE)->setAttrib('disabled', 'disabled');
					break;
				default:
					break;
			}
		}
	}

	public function validateElements() {
		if ( 'disabled' == $this->getElement( User_Model_Db_Users::COL_ID_ROLE)->getAttrib('disabled') ) {
			$this->removeElement( $this->getElement( User_Model_Db_Users::COL_ID_ROLE )->getName() );
		}
	}

	protected function setCurrentUserId($currentUserId = 0) {
		$this->_currentUserId = $currentUserId;
	}

}
