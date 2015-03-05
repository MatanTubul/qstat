<?php

class Qstat_Db_Table_LockScheduled extends Bf_Db_Table
{
	CONST TBL_NAME = "scheduled_locks";

	const COL_ID_LOCK = 'id_scheduled_locks';
	const COL_ID_CATALOG = 'id_catalog';
	const COL_ID_USER = 'id_users';
	const COL_LOCK_START = 'start_time';

	protected $_name = self::TBL_NAME;
}
