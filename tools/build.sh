#!/bin/bash

REL_SCRIPT_DIR="`dirname \"$0\"`"
SCRIPT_DIR="`( cd \"$REL_SCRIPT_DIR\" && pwd )`"
PROJECT_DIR="`( cd \"$SCRIPT_DIR/..\" && pwd )`"

# https://docs.npmjs.com/getting-started/installing-node

build_help()
{
    echo "$(tput setaf 3)Usage:$(tput sgr0)"
    echo "  build.sh <action>"
    echo ""
    echo "$(tput setaf 3)Actions:$(tput sgr0)"
    echo "  $(tput setaf 2)check$(tput sgr0)      - Check dependencies"
    echo "  $(tput setaf 2)deps$(tput sgr0)       - Install dependencies"
    echo "  $(tput setaf 2)init$(tput sgr0)       - Initialize repository"
    echo "  $(tput setaf 2)clean$(tput sgr0)      - Clean up"
    echo ""
}

build_check()
{
    echo "Checking dependencies..."

    for cmd in "wget" "npm" "node"; do
        printf "%-10s" "$cmd"
        if hash "$cmd" 2>/dev/null;
        then
            printf "$(tput setaf 2) Ok $(tput sgr0)\n"
        else
            printf "$(tput setaf 1) Not found $(tput sgr0)\n"
        fi
    done
}

build_deps()
{
    echo "Installing dependencies..."

    # composer
    wget http://getcomposer.org/composer.phar -O $SCRIPT_DIR/composer.phar
    php $SCRIPT_DIR/composer.phar self-update
}

build_init()
{
    echo "Initializing..."

    # composer (composer.json)
    cd $PROJECT_DIR/includes
    php $SCRIPT_DIR/composer.phar install

    # npm (package.json)
    cd $SCRIPT_DIR/scripts
    npm install
}

build_all()
{
    BUILD_DIR=`mktemp -d`
    BRANCH=master

    echo "Temporary directory: $BUILD_DIR"

    git clone git@gitlab.jtl-software.de:jtlshop/shop4.git $BUILD_DIR || exit 1
    git -C $BUILD_DIR checkout $BRANCH || exit 1

    echo "Executing composer"
    composer install --working-dir=$BUILD_DIR/includes -q || exit 1

    INITIALSCHEMA=$BUILD_DIR/install/initial_schema.sql
    TEMPCONFIG=$BUILD_DIR/includes/config.JTL-Shop.ini.php
    TEMPDB=shop_${BASHPID}

    echo "Creating database: $TEMPDB"
    mysql -uroot -pjtlgmbh -e"CREATE DATABASE IF NOT EXISTS $TEMPDB" || exit 1

    echo "Importing initial schema: $INITIALSCHEMA"
    mysql -uroot -pjtlgmbh $TEMPDB < $INITIALSCHEMA || exit 1

    echo "<?php define('PFAD_ROOT', '${BUILD_DIR}/'); \
    define('URL_SHOP', 'http://jenkins'); \
    define('DB_HOST', 'localhost'); \
    define('DB_NAME', '${TEMPDB}'); \
    define('DB_USER', 'root'); \
    define('DB_PASS', 'jtlgmbh'); \
    define('BLOWFISH_KEY', 'BLOWFISH_KEY');" > $TEMPCONFIG

    echo "Running updates"

    php -r "
        require_once '${BUILD_DIR}/includes/globalinclude.php'; \
        \$updater = new Updater(); \
        while (\$updater->hasPendingUpdates()) { \
            \$updater->update(); \
        } \
    "
}

build_clean()
{
    if [ -d "$BUILD_DIR" ]; then
        echo "Removing directory: $BUILD_DIR"
        rm -rf $BUILD_DIR
    fi

    TEMPDB=shop_${BASHPID}
    echo "Removing dababase: $TEMPDB"
    mysql -uroot -pjtlgmbh -e"DROP DATABASE IF EXISTS $TEMPDB" || exit 1
}

main() {
    cd $PROJECT_DIR
    local ACTION=build_${1:-full}

    if [ -n "$(type -t $ACTION)" ] && [ "$(type -t $ACTION)" = "function" ]; then
        trap build_clean exit
        $ACTION
    else
        build_help
    fi
}

(main $*)
