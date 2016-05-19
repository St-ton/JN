#!/bin/bash

REL_SCRIPT_DIR="`dirname \"$0\"`"
SCRIPT_DIR="`( cd \"$REL_SCRIPT_DIR\" && pwd )`"
PROJECT_DIR="`( cd \"$SCRIPT_DIR/..\" && pwd )`"

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

    for cmd in "curl" "nodejs" "npm" "composer"; do
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
    
    # curl
    sudo apt-get install curl -yqq
    
    # nodejs / npm
    curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -
    sudo apt-get install -y nodejs
    sudo npm install npm -g
    
    # composer
    curl -sS https://getcomposer.org/installer | php
    sudo mkdir -p /usr/local/bin
    sudo mv composer.phar /usr/local/bin/composer
    sudo composer self-update
}

build_init()
{
    echo "Initializing..."

    # composer (composer.json)
    cd $PROJECT_DIR/includes
    composer install
    
    # npm (package.json)
    cd $PROJECT_DIR/build/scripts
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
