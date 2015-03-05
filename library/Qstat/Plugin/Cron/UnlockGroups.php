<?php

class Qstat_Plugin_Cron_UnlockGroups extends Bf_Plugin_Cron_CronAbstract
{
    public function run (){

    	Qstat_Db_Table_LockEvents::unlock();

    }

}