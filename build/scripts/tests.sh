#!/usr/bin/env sh

XUID=$1
XGID=$2

echo "] execute 'composer install'.."
composer install -o -q -d includes/

echo "] get security checker.."
wget https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_amd64;
mv local-php-security-checker_1.0.0_linux_amd64 local-php-security-checker && chmod 775 local-php-security-checker;

echo "] prepare all sources for re-use.."
chown -R ${XUID}.${XGID} .

echo "] check composer packages vulnerabilities..";
result=$(./local-php-security-checker --path="includes/composer.lock")
errorcode=$?
if [ $errorcode -ne 0 ]; then
    echo -ne "\e[1;31m Error: ${result}\e[0m \n"
    exit 1;
else
    echo $result
fi

echo "] execute tests..";
./includes/vendor/bin/phpunit tests;
echo "] unit tests finished."


