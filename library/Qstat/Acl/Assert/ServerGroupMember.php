<?php

class Qstat_Acl_Assert_ServerGroupMember implements Zend_Acl_Assert_Interface
{

    public function assert (Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null)
    {
        $boolAllow = FALSE;

        $objFrontController = Zend_Controller_Front::getInstance();
        $objRequest = $objFrontController->getRequest();
        $arrParams = $objRequest->getParams();

        if (empty($arrParams[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG])) {
            if (! empty($arrParams[Qstat_Db_Table_Lock::COL_ID_LOCK])) {
                $intLockId = (int) $arrParams[Qstat_Db_Table_Lock::COL_ID_LOCK];
                $objLocks = new Qstat_Db_Table_Lock();
                $objLocksSelect = $objLocks->select(TRUE)->setIntegrityCheck(FALSE);
                $objLocksSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_ID_LOCK) . "=?", $intLockId);

                $objLockRow = $objLocks->fetchRow($objLocksSelect);
                $intCatalogId = $objLockRow->{Qstat_Db_Table_Lock::COL_ID_CATALOG};

            }
        } else {
            $intCatalogId = (int) $arrParams[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG];
        }

        if (! empty($intCatalogId)) {
            // Get Catalog Data
            $objCatalogData = new Catalog_Model_CatalogData();
            $objCatalogDataSelect = $objCatalogData->select(TRUE)->setIntegrityCheck(FALSE);
            $objCatalogDataSelect->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
            Bf_Catalog_Models_Db_Catalog::TBL_NAME . '.' . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . " = " . Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG);
            $objCatalogDataSelect->where(Catalog_Model_CatalogData::TBL_NAME . '.' . Catalog_Model_CatalogData::COL_ID_CATALOG . " = ?", $intCatalogId);

            $objCatalogDataRow = $objCatalogData->fetchRow($objCatalogDataSelect);

            $objUserSessionData = new Zend_Session_Namespace('user');
            $objUserDetails = $objUserSessionData->userDetails;

            $arrUserExtra = unserialize($objUserDetails->{User_Model_Db_Users::COL_EXTRA_DATA});

            if (! empty($objCatalogDataRow)) {
                switch ($role->getRoleId()) {
                    case 4:
                        if (! empty($objCatalogDataRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED}) && 'api' !== $arrParams['controller']) {
                            // GEt Lock Data
                            $objLocks = new Qstat_Db_Table_Lock();
                            $objLocksSelect = $objLocks->select(TRUE)->setIntegrityCheck(FALSE);
                            $objLocksSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_ID_CATALOG) . "=?", $intCatalogId);
                            $objLocksSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_IS_DELETED) . "=?", FALSE);
                            $objLocksSelect->where(Qstat_Db_Table_Lock::getColumnName(Qstat_Db_Table_Lock::COL_LOCK_END) . " IS NULL");

                            $objLockRow = $objLocks->fetchRow($objLocksSelect);

                            if (! empty($objLockRow)) {
                                if ($objLockRow->{Qstat_Db_Table_Lock::COL_ID_USER} !== $objUserDetails->{User_Model_Db_Users::COL_ID_USERS}) {
                                    break;
                                }
                            }
                        }
                    case 5:
						// Group Member
						// Group Manager
						if (
						! empty( $objCatalogDataRow->{Catalog_Model_CatalogData::COL_IS_SHARED} ) ||
						( ( ! empty( $arrUserExtra['groups'] ) ) && ( $objCatalogDataRow->{Catalog_Model_CatalogData::COL_ID_GROUPS} == $arrUserExtra['groups'] ) ) ||
						( ( ! empty( $arrUserExtra['subgroups'] ) ) && in_array( $objCatalogDataRow->{Catalog_Model_CatalogData::COL_ID_GROUPS}, $arrUserExtra['subgroups'] ) )
						) {
							$boolAllow = TRUE;
						}

                        break;
                    case 6:
                        // Site Manager
                        if ((! empty($objCatalogDataRow->{Catalog_Model_CatalogData::COL_IS_SHARED})) ||
                         ((! empty($arrUserExtra['groups'])) && ($objCatalogDataRow->{Catalog_Model_CatalogData::COL_ID_SITES} == $arrUserExtra['sites']))) {
                            $boolAllow = TRUE;
                        }

                        break;
                    default:
                        $boolAllow = FALSE;
                        break;
                }
            }
        }

        return $boolAllow;
    }

}