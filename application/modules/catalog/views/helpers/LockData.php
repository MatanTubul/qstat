<?php
/**
 *
 * @author sirshurf
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * LockData helper
 *
 * @uses viewHelper Catalog_View_Helper
 */
class Catalog_View_Helper_LockData extends Zend_View_Helper_Abstract
{
    public function lockData ($objLockRow)
    {
        $strHtml = "";
        $strHtml .= Zend_Registry::get("Zend_Translate")->translate('LBL_LOCK_ON_PREFIX')."<br/>";
        $strHtml .= Zend_Registry::get("Zend_Translate")->translate('LBL_LOCK_ON_STARTED').":".$objLockRow->{Qstat_Db_Table_Lock::COL_LOCK_START}."<br/>";
        $strHtml .= Zend_Registry::get("Zend_Translate")->translate('LBL_LOCK_ON_USER').":".$objLockRow->display_name."<br/>";
        $strHtml .= Zend_Registry::get("Zend_Translate")->translate('LBL_LOCK_ON_EXPECTED_END').":".$objLockRow->{Qstat_Db_Table_Lock::COL_LOCK_SCHEDULED_UNLOCK}."<br/>";

        return $strHtml;
    }
}
