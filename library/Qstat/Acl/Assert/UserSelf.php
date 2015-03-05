<?php
class Qstat_Acl_Assert_UserSelf implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		
		$boolAllow = FALSE;
		
		$objFrontController = Zend_Controller_Front::getInstance();
		$objRequest =  $objFrontController->getRequest();		
		$arrParams = $objRequest->getParams();
		
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		$objUserDetails = $objUserSessionData->userDetails;
		
		if ((! empty ( $objUserDetails->{User_Model_Db_Users::COL_ID_USERS} )) && (! empty ( $arrParams['UserId'] )) && ($arrParams['UserId'] == $objUserDetails->{User_Model_Db_Users::COL_ID_USERS})){
		    $boolAllow = TRUE;
		}
				
		return $boolAllow;
	}

}