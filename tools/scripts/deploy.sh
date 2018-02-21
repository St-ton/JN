#!/bin/bash

source ${SCRIPT_DIR}/scripts/ini_parser.sh

# database credentials
MYCNF=~/.my.cnf

# $1 branch/tag
# $2 build number
deploy_create()
{
    source ${SCRIPT_DIR}/version.conf

    export VCS_TYPE="head"
    export SHOP_VERSION SHOP_BUILD DB_PREFIX
    export BUILD_DIR=`mktemp -d`

    local VCS_BRANCH=$1
    local VCS_BUILD_NUMBER=$2

    local TARGET_FILE="shop"
    local DB_NAME=$(deploy_db_name)

    if [ -z "${VCS_BRANCH}" ]; then
        VCS_BRANCH=$(deploy_branch_name)
    fi

    local VCS_REG="refs\\/(head|tag)s\\/(.+)"
    local VCS_REG_TAG="v([0-9])\\.([0-9]{2})\\.([0-9])"
    local VCS_REF=$VCS_BRANCH

    if [[ $VCS_BRANCH =~ $VCS_REG ]]; then
        VCS_TYPE=${BASH_REMATCH[1]}
        VCS_REF=${BASH_REMATCH[2]}
    fi

    local SHOP_VERSION_MAJOR=${SHOP_VERSION:0:1}
    local SHOP_VERSION_MINOR=${SHOP_VERSION:1:2}

    if [ "$VCS_TYPE" = "tag" ]; then
        if [[ $VCS_REF =~ $VCS_REG_TAG ]]; then
            SHOP_VERSION_MAJOR=${BASH_REMATCH[1]}
            SHOP_VERSION_MINOR=${BASH_REMATCH[2]}
            SHOP_BUILD=${BASH_REMATCH[3]}
        fi
    fi

    if [ "$VCS_REF" = "master" ]; then
        TARGET_FILE="${TARGET_FILE}_devel.zip"
    else
        TARGET_FILE="${TARGET_FILE}_${SHOP_VERSION_MAJOR}.${SHOP_VERSION_MINOR}.${SHOP_BUILD}.zip"
    fi

    TARGET_FILE="$(echo $TARGET_FILE | sed -e 's/[^A-Za-z0-9._-]/_/g')"

    TARGET_PATH="${SCRIPT_DIR}/dist/${VCS_TYPE}"
    TARGET_FULLPATH="${TARGET_PATH}/${TARGET_FILE}"

    text "Deploying #${VCS_BUILD_NUMBER} ${VCS_TYPE} '${VCS_REF}' to ${TARGET_FILE}"

    mkdir -p ${TARGET_PATH}

    msg "Cloning repository"
    deploy_checkout ${VCS_BRANCH}

    msg "Creating additional files"
    deploy_additional_files

    deploy_build_info ${SHOP_BUILD}

    msg "Executing composer"
    deploy_vendors

    msg "Creating md5 hashfile"
    deploy_md5_hashfile ${SHOP_VERSION}

    msg "Importing initial schema"
    deploy_import_initial_schema ${DB_NAME}

    msg "Writing config.JTL-Shop.ini.initial.php"
    deploy_config_file ${DB_NAME}

    msg "Executing migrations"
    deploy_migrate ${DB_NAME} ${SHOP_VERSION}

    msg "Creating database struct"
    deploy_db_struct ${DB_NAME} ${SHOP_VERSION}

    msg "Preparing archive"
    deploy_prepare_zip

    msg "Creating archive"
    deploy_create_zip ${TARGET_FULLPATH}

    msg "Cleaning workspace"
    deploy_clean ${DB_NAME}
}

deploy_db_name()
{
    if [ ! -f ${MYCNF} ]; then
        error "Config file '${MYCNF}' does not exist"
    fi

    deploy_ini_values ~/.my.cnf custom

    if [ -z "${dbprefix}" ]; then
        dbprefix="build"
    fi

    echo "${dbprefix}_${BASHPID}"
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

    git -C ${BUILD_DIR} submodule init -q || exit 1
    git -C ${BUILD_DIR} submodule sync -q || exit 1
    git -C ${BUILD_DIR} submodule update -q || exit 1

    rm -rf ${BUILD_DIR}/.git*
    rm -rf ${BUILD_DIR}/tools
}

deploy_additional_files()
{
    mkdir -p ${BUILD_DIR}/export ${BUILD_DIR}/includes
    touch ${BUILD_DIR}/shopinfo.xml ${BUILD_DIR}/export/sitemap_index.xml ${BUILD_DIR}/rss.xml ${BUILD_DIR}/includes/config.JTL-Shop.ini.initial.php
}

# $1 target build version
deploy_build_info()
{
    local TIMESTAMP=`date +%Y%m%d%H%M%S`
    sed -i "s/#JTL_MINOR_VERSION#/$1/g" ${BUILD_DIR}/includes/defines_inc.php
    sed -i "s/#JTL_BUILD_TIMESTAMP#/$TIMESTAMP/g" ${BUILD_DIR}/includes/defines_inc.php
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
    find . -type f ! -name robots.txt ! -name rss.xml ! -name shopinfo.xml ! -name .htaccess ! -samefile includes/defines.php ! -samefile includes/defines_inc.php ! -samefile includes/config.JTL-Shop.ini.initial.php -printf '"%P"\n' | grep -v -E '.git/|/.gitkeep|build/|tools/|admin/gfx|admin/includes/emailpdfs|admin/includes/shopmd5files|admin/templates/gfx|admin/templates_c/|bilder/|downloads/|gfx/|includes/plugins|install/|jtllogs/|mediafiles/|templates/|templates_c/|uploads/|export/|shopinfo.xml|sitemap_index.xml' | xargs md5sum | awk '{ print $2";"$1; }' | sort > ${MD5_DB_FILENAME}

    cd $OLDPWD
}

# $1 database name
deploy_import_initial_schema()
{
    local INITIALSCHEMA=${BUILD_DIR}/install/initial_schema.sql

    mysql -e "CREATE DATABASE IF NOT EXISTS $1" || exit 1
    mysql $1 < ${INITIALSCHEMA} || exit 1
}

# $1 database name
deploy_create_initial_schema()
{
    source ${SCRIPT_DIR}/version.conf

    export SHOP_VERSION

    local TMPDB="${1}_tmp"
    local TMPFILE=`mktemp`

    mysqldump --default-character-set=utf8 --skip-add-locks  --skip-add-drop-table --skip-comments $1 -r $TMPFILE

    mysql -e "DROP DATABASE IF EXISTS ${TMPDB}"
    mysql -e "CREATE DATABASE ${TMPDB} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"

    mysql -D $TMPDB -e "SET names utf8; SOURCE ${TMPFILE};"

    rm $TMPFILE

    mysql -D $TMPDB -e "TRUNCATE TABLE tsynclogin; TRUNCATE TABLE tadminlogin;TRUNCATE TABLE tbesucher; TRUNCATE TABLE tbesucherarchiv; TRUNCATE TABLE tbesuchteseiten; TRUNCATE TABLE tbrocken; TRUNCATE TABLE tfirma; TRUNCATE TABLE tsprachlog; TRUNCATE TABLE tredirect; TRUNCATE TABLE tredirectreferer;TRUNCATE TABLE tjtllog;TRUNCATE TABLE tsuchanfragencache;TRUNCATE TABLE tsuchanfrageerfolglos;TRUNCATE TABLE ttrustedshopskundenbewertung;TRUNCATE TABLE teinheit;TRUNCATE TABLE trevisions;"

    mysql -D $TMPDB -e "SET @orig_sql_mode:=(SELECT @@sql_mode); SET @@sql_mode:='';  UPDATE tversion SET nVersion=${SHOP_VERSION}; UPDATE tbesucherzaehler SET nZaehler=0; UPDATE tnummern SET nNummer = 10000 WHERE nArt=1; UPDATE tnummern SET dAktualisiert='0000-00-00 00:00:00'; UPDATE tmigration SET dExecuted=NOW(); SET @@sql_mode:=@orig_sql_mode;"

    mysqldump --default-character-set=utf8 --skip-add-locks  --skip-add-drop-table --skip-comments $TMPDB

    mysql -e "DROP DATABASE IF EXISTS ${TMPDB}"
}

deploy_prepare_zip()
{
    rm -r ${BUILD_DIR}/build
    rm ${BUILD_DIR}/includes/config.JTL-Shop.ini.php
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
