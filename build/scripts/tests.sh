#!/usr/bin/env bash

echo "Execute composer install...";
composer install -d includes;

echo "Build components...";
for component in build/components/*/ ; do
    composer install -q -d ${component};
done

echo "Execute tests...";
includes/vendor/bin/phpunit tests;