<?php

class Qstat_Db_Table_LockMailCron extends Bf_Db_Table
{

	CONST TBL_NAME = "lock_mail_cron";

	const COL_ID_LOCK_MAIL_CRON = 'id_lock_mail_cron';
	const COL_CRON_TIMEDATE = 'cron_timedate';
	const COL_CRON_TYPE = 'cron_type';

	protected $_name = self::TBL_NAME;
}
