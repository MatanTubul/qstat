<?php

class Qstat_Db_Table_LockEventsLog extends Bf_Db_Table
{

    CONST TBL_NAME = "lock_events_log";

    const COL_ID_LOCK_EVENTS_LOG = 'id_lock_events_log';
    const COL_ID_LOCK_EVENTS = 'id_lock_events';
    const COL_DATE_TIME_UNLOCK = 'date_time_unlock';
    const COL_IS_DELETED = 'is_deleted';

	protected $_name = self::TBL_NAME;
}
