<?php

class Qstat_Plugin_Cron_LockDelayedStart extends Bf_Plugin_Cron_CronAbstract
{

    public function run ()
    {
        $objScheduledLocks = new Qstat_Db_Table_LockScheduled();
        $objScheduledLocksSelect = $objScheduledLocks->select(TRUE);
        $objScheduledLocksSelect->where(Qstat_Db_Table_LockScheduled::COL_LOCK_START . " <= NOW()");
        $objScheduledLocksSelect->where(Qstat_Db_Table_LockScheduled::COL_IS_DELETED . '=?', FALSE);

        $objScheduledLocksRows = $objScheduledLocks->fetchAll($objScheduledLocksSelect);

        $objApplicationOptions = new Zend_Config($this->getAppOptions());

        $objLocks = new Qstat_Db_Table_Lock();

        foreach ($objScheduledLocksRows as $objScheduledLockInfo) {

            $intLockId = $objLocks->createLock($objScheduledLockInfo->{Qstat_Db_Table_LockScheduled::COL_ID_CATALOG}, $objScheduledLockInfo->{Qstat_Db_Table_LockScheduled::COL_ID_USER},
            $objApplicationOptions->defaultUnlockTime);

            if ($intLockId) {
                $objScheduledLocks->delete($objScheduledLockInfo->{Qstat_Db_Table_LockScheduled::COL_ID_LOCK});
            }
            //:TODO Send message to user.

            $objLockRowSet = $objLocks->find($intLockId);

            if ($objLockRowSet->count() > 0) {

                $objLockRowS = $objLockRowSet->current();

                $objApplicationOptions = new Zend_Config($this->getAppOptions());
                $objCatalog = new Bf_Catalog($objApplicationOptions->catalog);
                $objLocks->_notifyUserLockIsOff($objCatalog, $objLockRowS, 'LBL_SUBJECT_EMAIL_LOCK_END_NOTIFICATION', 'LBL_TEXT_EMAIL_LOCK_END_NOTIFICATION');
            }
        }
    }
}