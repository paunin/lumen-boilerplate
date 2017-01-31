#!/usr/bin/env bash

set -e

echo '> Running confd...'
confd -onetime -backend env
