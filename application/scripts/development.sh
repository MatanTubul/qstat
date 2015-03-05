#!/bin/bash

export APPLICATION_ENV="development"

DIR="$( cd "$( dirname "$0" )" && pwd )"

cd $DIR

php cron.php