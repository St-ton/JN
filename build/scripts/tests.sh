#!/usr/bin/env sh

XUID=$1
XGID=$2

echo "] execute 'composer install'.."
composer install -o -q -d includes/
echo "] prepare all sources for re-use.."
chown -R ${XUID}.${XGID} .

echo "] check composer packages vulnerabilities..";
result=$(./includes/vendor/bin/security-checker security:check "includes/composer.lock")
errorcode=$?
if [ $errorcode -ne 0 ]; then
    echo -ne "\e[1;31m Remote error: ${result}\e[0m \n"
    #exit 1;
else 
    echo $result
fi

echo "] build components..";
for COMPONENT in build/components/*/ ; do
    echo "] execute 'composer install' for ${COMPONENT}.."
    composer install --no-plugins -a -o -q -d ${COMPONENT}

    echo "] prepare all sources for re-use.."
    chown -R ${XUID}.${XGID} .

    echo "] check composer packages vulnerabilities for ${COMPONENT}.."
    result=$(./includes/vendor/bin/security-checker security:check "${COMPONENT}composer.lock")
    errorcode=$?
    if [ $errorcode -ne 0 ]; then
        echo -ne "\e[1;31m Remote error: ${result}\e[0m \n"
        #exit 1;
    else 
        echo $result
    fi
done

echo "] execute tests..";
./includes/vendor/bin/phpunit tests;
echo "] unit tests finished."


