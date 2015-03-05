<?php
require_once APPLICATION_PATH . '/Bootstrap.php';

class Bootstrap_Cron extends Bootstrap
{
    const CRON_RESOURCE = 'cron';
    
    public function run()
    {
    	echo ("Bootstrap Cron Start, DateTime:".date("Y-d-m H:i"));
        try {
            if ($this->hasPluginResource(self::CRON_RESOURCE)) {
                $this->bootstrap(self::CRON_RESOURCE);
                $server = $this->getResource(self::CRON_RESOURCE);
                echo $server->run($this->getOptions());
            } else {
                echo 'The cron plugin resource needs to be configured in application.ini.' . PHP_EOL;
            }
        } catch (Exception $e) {
            echo 'An error has occured.' . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        }
    	echo (" Bootstrap Cron End").PHP_EOL;
    }
}