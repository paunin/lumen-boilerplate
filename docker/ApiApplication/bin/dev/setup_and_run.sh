#!/usr/bin/env bash

set -e

/var/www/application/docker/ApiApplication/bin/dev/setup.sh
/usr/local/bin/app/run.sh