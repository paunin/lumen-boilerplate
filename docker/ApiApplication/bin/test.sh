#!/usr/bin/env bash
set -e

cd /var/www/application/

/usr/local/bin/app/dev/setup.sh
/usr/local/bin/app/pre_run.sh


FAILED=0
echo '> Running PHP Code Sniffer...'
PHPCS_FILES=`find . -name "*.php" -not -path "./_ide_helper.php"  -not -path "./vendor/*" -not -path "./lint/*" -not -path "./bin/*" -not -path "./build/*" -not -path "./tests/*" | tr '\n' ' '`
./vendor/bin/phpcbf --standard=./lint/phpcs/ruleset.xml $PHPCS_FILES || true
./vendor/bin/phpcs -s --standard=./lint/phpcs/ruleset.xml $PHPCS_FILES || FAILED=1

echo '> Running PHP Copy-Paste detector...'
PHPCPD_FILES="$PHPCS_FILES"
./vendor/bin/phpcpd $PHPCPD_FILES || FAILED=1

echo '> Running PHP Mess Detector...'
PHPMD_FILES=`find . -name "*.php" -not -path "./_ide_helper.php"  -not -path "./vendor/*" -not -path "./lint/*" -not -path "./bin/*" -not -path "./build/*" -not -path "./tests/*"  | tr '\n' ',' | sed 's/,$//'`
./vendor/bin/phpmd $PHPMD_FILES text ./lint/phpmd/ruleset.xml || FAILED=1

echo '> Running Tests...'
./vendor/bin/phpunit --verbose || FAILED=1

echo '> Checking code coverage...'
php ./vendor/bin/coverage-checker.php ./build/coverage/coverage.xml 90 || FAILED=1

echo '> Checking swagger.json...'
php artisan swagger:generate /tmp/swagger.json localhost || FAILED=1
swagger validate /tmp/swagger.json || FAILED=1

exit $FAILED