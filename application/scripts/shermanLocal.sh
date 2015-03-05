#!/bin/bash

rm -f /www/labadmin/files/tmp/*

export APPLICATION_ENV="ShermanLocal"

DIR="$( cd "$( dirname "$0" )" && pwd )"

cd $DIR

php cron.php
