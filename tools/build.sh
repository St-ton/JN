#!/bin/bash

REL_SCRIPT_DIR="`dirname \"$0\"`"

export SCRIPT_DIR="`( cd \"${REL_SCRIPT_DIR}\" && pwd )`"
export PROJECT_DIR="`( cd \"${SCRIPT_DIR}/..\" && pwd )`"

source ${SCRIPT_DIR}/scripts/tools.sh
source ${SCRIPT_DIR}/scripts/deploy.sh

build_help()
{
    echo "${fgYellow}Usage:${C}"
    echo "  build.sh <action>"
    echo ""
    echo "${fgYellow}Actions:${C}"
    echo "  ${fgGreen}check${C}             - Check dependencies"
    echo "  ${fgGreen}deps${C}              - Install dependencies"
	echo "  ${fgGreen}ide_meta${C}          - Create metadata"
    echo "  ${fgGreen}deploy <archive>${C}  - Deploy"
    echo ""
}

build_check()
{
    msg "Checking dependencies..."

    pathadd ${SCRIPT_DIR}/bin

    for cmd in "composer" "php-cs-fixer" "phpcs" "phpcbf" "zip"; do
        if hash "$cmd" 2>/dev/null;
        then
            printf "${fgGreen}  Y  ${C}"
        else
            printf "${fgRed}  N  ${C}"
        fi
        printf "$cmd\n"
    done
}

build_deps()
{
    msg "Installing dependencies..."

    # composer
    wget http://getcomposer.org/composer.phar -O ${SCRIPT_DIR}/bin/composer -q --show-progress || exit 1
    chmod u+x ${SCRIPT_DIR}/bin/composer

    # php-cs-fixer
    wget http://get.sensiolabs.org/php-cs-fixer.phar -O ${SCRIPT_DIR}/bin/php-cs-fixer -q --show-progress || exit 1
    chmod u+x ${SCRIPT_DIR}/bin/php-cs-fixer

    # phpcs
    wget https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar -O ${SCRIPT_DIR}/bin/phpcs -q --show-progress || exit 1
    chmod u+x ${SCRIPT_DIR}/bin/phpcs

    # phpcbf
    wget https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar -O ${SCRIPT_DIR}/bin/phpcbf -q --show-progress || exit 1
    chmod u+x ${SCRIPT_DIR}/bin/phpcbf
}

build_fixcs()
{
    php ${SCRIPT_DIR}/bin/php-cs-fixer --config-file="${PROJECT_DIR}/.php_cs" fix ${PROJECT_DIR} -vvv --dry-run
}

build_ide_meta()
{
    META_FILE="${PROJECT_DIR}/.phpstorm.meta.php"

    echo "<?php" > ${META_FILE}
	deploy_ide_meta >> ${META_FILE}
}

build_init()
{
    msg "Initializing..."

    # composer (composer.json)
    php ${SCRIPT_DIR}/bin/composer install --working-dir=${PROJECT_DIR}/includes || exit 1
}

# $1 archive filepath
build_deploy()
{
    deploy_create $1
}

main() {
    cd ${PROJECT_DIR}
    local ACTION=build_${1:-full}

    if [ -n "$(type -t ${ACTION})" ] && [ "$(type -t ${ACTION})" = "function" ]; then
        ${ACTION} ${*:2}
    else
        build_help
    fi
}

(main $*)
