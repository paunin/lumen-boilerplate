#!/usr/bin/env bash
set -e

/usr/local/bin/app/pre_run.sh

php-fpm -RF
