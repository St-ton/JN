#!/bin/bash

source ${SCRIPT_DIR}/scripts/ini_parser.sh

# database credentials
MYCNF=~/.my.cnf

deploy_create()
{
    if [ ! -f ${MYCNF} ]; then
        error "Config file '${MYCNF}' does not exist"
    fi

    source ${SCRIPT_DIR}/version.conf
    export SHOP_VERSION SHOP_BUILD DB_PREFIX

    deploy_ini_values ~/.my.cnf custom

    if [ -z "${dbprefix}" ]; then
        dbprefix="shop"
    fi

    local DB_NAME="${dbprefix}_${BASHPID}"

    local VCS_BRANCH=$(deploy_branch_name)
    local VCS_REVISION=$(git rev-parse HEAD)

    local TARGET=""
    local BUILD_TIMESTAMP=`date +%Y%m%d%H%M%S`

    if [ $VCS_BRANCH == "develop" ]; then
        TARGET="jtlshop_devel"
    else
        TARGET="jtlshop_release"
    fi
    
    TARGET="${TARGET}_${SHOP_VERSION}.${SHOP_BUILD}_${BUILD_TIMESTAMP}"
    
    if [ $VCS_BRANCH != "master" ]; then
        TARGET="${TARGET}_${VCS_REVISION:0:9}"
    fi

    TARGET="${SCRIPT_DIR}/dist/${TARGET}.zip"

    msg "Archive file: ${TARGET}"

    export BUILD_DIR=`mktemp -d`

    msg "Build directory: ${BUILD_DIR}"

    msg "Cloning repository"
    deploy_checkout ${VCS_BRANCH}

    msg "Creating additional files"
    deploy_additional_files

    deploy_build_info ${SHOP_BUILD} ${BUILD_TIMESTAMP}

    msg "Executing composer"
    deploy_vendors

    msg "Creating md5 hashfile"
    deploy_md5_hashfile ${SHOP_VERSION}

    msg "Importing initial schema"
    deploy_initial_schema ${DB_NAME}

    msg "Writing config.JTL-Shop.ini.initial.php"
    deploy_config_file ${DB_NAME}

    msg "Executing migrations"
    deploy_migrate ${DB_NAME} ${SHOP_VERSION}

    msg "Creating database struct"
    deploy_db_struct ${DB_NAME} ${SHOP_VERSION}

    msg "Creating archive"
    deploy_create_zip ${TARGET}

    msg "Cleaning workspace"
    deploy_clean ${DB_NAME}
}

deploy_branch_name()
{
    BRANCH=$(git symbolic-ref -q HEAD)
    BRANCH=${BRANCH##refs/heads/}
    BRANCH=${BRANCH:-HEAD}
    echo ${BRANCH}
}

# $1 branch
deploy_checkout()
{
    git clone git@gitlab.jtl-software.de:jtlshop/shop4.git ${BUILD_DIR} -q || exit 1
    git -C ${BUILD_DIR} checkout $1 -q || exit 1

    rm -rf ${BUILD_DIR}/.git*
    rm -rf ${BUILD_DIR}/tools
}

deploy_additional_files()
{
    mkdir -p ${BUILD_DIR}/export ${BUILD_DIR}/includes
    touch ${BUILD_DIR}/shopinfo.xml ${BUILD_DIR}/export/sitemap_index.xml ${BUILD_DIR}/rss.xml ${BUILD_DIR}/includes/config.JTL-Shop.ini.initial.php
}

# $1 target build version
# $2 target timestamp
deploy_build_info()
{
    sed -i "s/#JTL_MINOR_VERSION#/$1/g" ${BUILD_DIR}/includes/defines_inc.php
    sed -i "s/#JTL_BUILD_TIMESTAMP#/$2/g" ${BUILD_DIR}/includes/defines_inc.php
}

deploy_vendors()
{
    composer install --working-dir=${BUILD_DIR}/includes -q || exit 1
}

# $1 database name
deploy_config_file()
{
    deploy_ini_values ~/.my.cnf client

    echo "<?php define('PFAD_ROOT', '${BUILD_DIR}/'); \
        define('URL_SHOP', 'http://jenkins'); \
        define('DB_HOST', '${host}'); \
        define('DB_SOCKET', '${socket}'); \
        define('DB_USER', '${user}'); \
        define('DB_PASS', '${password}'); \
        define('DB_NAME', '$1'); \
        define('BLOWFISH_KEY', 'BLOWFISH_KEY');" > ${BUILD_DIR}/includes/config.JTL-Shop.ini.php
}

# $1 database name
# $2 target version
deploy_migrate()
{
    php -r "
        require_once '${BUILD_DIR}/includes/globalinclude.php'; \
        \$updater = new Updater(); \
        while (\$updater->hasPendingUpdates()) { \
            \$updater->update(); \
        } \
    "

    echo 'TRUNCATE tmigration' | mysql -D $1
    echo 'TRUNCATE tmigrationlog' | mysql -D $1
    echo 'TRUNCATE tversion' | mysql -D $1

    echo "INSERT INTO tversion (nVersion, nZeileVon, nZeileBis, nInArbeit, nFehler, nTyp, cFehlerSQL, dAktualisiert) VALUES ('$2', 1, 0, 0, 0, 0, '', NOW())" | mysql -D $1
}

# $1 database name
# $2 target version
deploy_db_struct()
{
    local SCHEMAJSON_PATH=${BUILD_DIR}/admin/includes/shopmd5files/dbstruct_$2.json
    ${SCRIPT_DIR}/scripts/dbstruct.pl $1 > ${SCHEMAJSON_PATH}
}

# $1 version
deploy_md5_hashfile()
{
    local OLDPWD=`pwd`
    local MD5_DB_FILENAME="${BUILD_DIR}/admin/includes/shopmd5files/$1.csv"

    cd ${BUILD_DIR}
    find . -type f ! -name robots.txt ! -name rss.xml ! -name shopinfo.xml ! -name .htaccess ! -samefile includes/defines.php ! -samefile includes/defines_inc.php ! -samefile includes/config.JTL-Shop.ini.initial.php -printf '"%P"\n' | grep -v -E '.git/|/.gitkeep|tools/|admin/gfx|admin/includes/emailpdfs|admin/includes/shopmd5files|admin/templates/gfx|admin/templates_c/|bilder/|downloads/|gfx/|includes/plugins|install/|jtllogs/|mediafiles/|templates/|templates_c/|uploads/|export/|shopinfo.xml|sitemap_index.xml' | xargs md5sum | awk '{ print $2";"$1; }' | sort > ${MD5_DB_FILENAME}

    cd $OLDPWD
}

# $1 database name
deploy_initial_schema()
{
    local INITIALSCHEMA=${BUILD_DIR}/install/initial_schema.sql

    mysql -e "CREATE DATABASE IF NOT EXISTS $1" || exit 1
    mysql $1 < ${INITIALSCHEMA} || exit 1
}

# $1 archive name
deploy_create_zip()
{
    pushd ${BUILD_DIR} >> /dev/null 2>&1
    zip -r $1 . -q || exit 1
    popd >> /dev/null 2>&1
}

deploy_ide_meta()
{
	FILE="${PROJECT_DIR}/includes/defines.php"
	PATTERN="^\s*ifndef\('(.*)'\s*,\s*(.*)\);"
	cat $FILE | while read LINE
	do
		[[ $LINE =~ $PATTERN ]]
		if [[ ${BASH_REMATCH[0]} ]]
		then
			META="define('${BASH_REMATCH[1]}', ${BASH_REMATCH[2]});"
			echo $META
		fi
	done
}

# $1 database name
deploy_clean()
{
    if [ -d "${BUILD_DIR}" ]; then
        rm -rf ${BUILD_DIR}
    fi

    mysql -e "DROP DATABASE IF EXISTS $1"
}

# $1 ini file
# $2 section
# $3 value
deploy_ini_value()
{
    local VAR=$3

    cfg_parser $1
    cfg_section_$2

    echo ${!VAR}
}

# $1 ini file
# $2 section
deploy_ini_values()
{
    cfg_parser $1
    cfg_section_$2
}