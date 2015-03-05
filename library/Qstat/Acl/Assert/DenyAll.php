<?php
class Qstat_Acl_Assert_DenyAll implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
				
		return FALSE;
	}

}