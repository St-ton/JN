#!/usr/bin/env bash

echo "Execute composer install...";
composer install -q -d includes;

echo "Execute tests...";
includes/vendor/bin/phpunit tests;