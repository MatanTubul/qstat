<?php

date_default_timezone_set('Asia/Jerusalem');

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));

defined('APPLICATION_FILES')
|| define('APPLICATION_FILES', APPLICATION_PATH . '/../files');

defined('APPLICATION_REPORTS')
|| define('APPLICATION_REPORTS', APPLICATION_PATH . '/../report');

defined('APPLICATION_FILES_TMP')
|| define('APPLICATION_FILES_TEM', APPLICATION_FILES . '/tmp');

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
	realpath(APPLICATION_PATH . '/../library'),
	get_include_path(),
)));

/** Zend_Application */
require_once 'Bf/Application.php';

// Create application, bootstrap, and run
$application = new Bf_Application(
	APPLICATION_ENV,
	array(
		'bootstrap' => array(
			'class' => 'Bootstrap_Cron',
			'path' => APPLICATION_PATH . '/Cron.php',
		),
		'config' => APPLICATION_PATH . '/configs/application.ini',
	)
);

$application
->bootstrap()
->run();