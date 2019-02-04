#!/usr/bin/env bash

echo "Execute composer install...";
composer install -a -o -q -d includes;

echo "Check composer packages vulnerabilities.";
includes/vendor/bin/security-checker security:check "includes/composer.lock"
if [[ $? -eq 1 ]]; then
    exit 1;
fi

echo "Build components...";
for component in build/components/*/ ; do
    composer install -a -o -q -d ${component};

    echo "Check composer packages vulnerabilities for ${component}.";
    includes/vendor/bin/security-checker security:check "${component}composer.lock"
    if [[ $? -eq 1 ]]; then
        exit 1;
    fi
done

echo "Execute tests...";
includes/vendor/bin/phpunit tests;