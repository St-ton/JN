#!/usr/bin/env bash

echo "Execute composer install...";
composer install -q -d includes;

echo "Build components...";
for component in build/components/*/ ; do
    composer install -d ${component};
done

echo "Execute tests...";
includes/vendor/bin/phpunit tests;