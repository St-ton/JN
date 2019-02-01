#!/usr/bin/env bash

echo "Execute composer install...";
composer install -a -o -q -d includes;

echo "Build components...";
for component in build/components/*/ ; do
    composer install -a -o -q -d ${component};
done

echo "Execute tests...";
includes/vendor/bin/phpunit tests;