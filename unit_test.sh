#!/usr/bin/env bash

set -e

# quiet
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests
# verbose
# ./vendor/bin/phpunit --bootstrap vendor/autoload.php -v --debug tests
