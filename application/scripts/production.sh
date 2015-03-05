#!/bin/bash

export APPLICATION_ENV="production"

DIR="$( cd "$( dirname "$0" )" && pwd )"
cd $DIR


# cd /www/labadmin/application/scripts
/usr/local/zend/bin/php cron.php
