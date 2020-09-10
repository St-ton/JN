#!/usr/bin/env sh

echo "] execute 'composer install'.."
composer install -o -q -d includes/

echo "] check composer packages vulnerabilities..";
./includes/vendor/bin/security-checker security:check "includes/composer.lock"
if [ $? -ne 0 ]; then
    exit 1;
fi

echo "] build components..";
for COMPONENT in build/components/*/ ; do
    echo "] execute 'composer install' for ${COMPONENT}.."
    composer install --no-plugins -a -o -q -d ${COMPONENT}

    echo "] check composer packages vulnerabilities for ${COMPONENT}.."
    ./includes/vendor/bin/security-checker security:check "${COMPONENT}composer.lock"
    if [ $? -ne 0 ]; then
        exit 1;
    fi
done

echo "] execute tests..";
./includes/vendor/bin/phpunit tests;
echo "] unit tests finished."

echo "] prepare changed stuff for re-use.."
chmod -R o+w ./install ./build/ ./includes/vendor/
