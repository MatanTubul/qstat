#!/bin/bash

export APPLICATION_ENV="testing"

DIR="$( cd "$( dirname "$0" )" && pwd )"

cd $DIR

php cron.php
