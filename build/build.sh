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

main() {
    cd $PROJECT_DIR
    local ACTION
    ACTION=build_${1:-full}
    if [ -n "$(type -t $ACTION)" ] && [ "$(type -t $ACTION)" = "function" ]; then
        $ACTION
    else
        build_help
    fi
}

(main $*)
