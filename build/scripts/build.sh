#!/bin/bash

###############################################
# Configuration
###############################################

TMP_PATH="$WORKSPACE/build/tmp"
SHOP_EXPORT_PATH="$TMP_PATH/shop_export"
BUILD_TIMESTAMP=$(date +%Y%m%d%H%M%S)

###############################################
# Git parameters
###############################################

HEADREVISION=$(git rev-parse HEAD)
CURRENT_BRANCH=$(git symbolic-ref -q HEAD)
CURRENT_BRANCH=${CURRENT_BRANCH##refs/heads/}
CURRENT_BRANCH=${CURRENT_BRANCH:-HEAD}

git submodule init
git submodule sync
git submodule update -r
git submodule foreach "(git checkout master; git pull)&"

###############################################

source $WORKSPACE/build/version.conf
export SHOPVERSION_MAJOR SHOPVERSION_MINOR SHOPVERSION_BUILD

ARGS=($0 "$@")
debug() {
    echo $(basename ${ARGS[0]})": "$1
}

if [ $# -lt 2 ] ; then
    echo "USAGE: $0 user password"
    exit 0
fi

DB_USER=$1
DB_PASSWORD=$2

cd $WORKSPACE

# Build temporary directories
debug "creating temporary directories..."
[ -d $TMP_PATH ] && rm -rf $TMP_PATH

mkdir -p $TMP_PATH
mkdir -p $SHOP_EXPORT_PATH

debug "copying Shop files to ${SHOP_EXPORT_PATH}..."
rsync -av --exclude=".git" --exclude="build" $WORKSPACE/ $SHOP_EXPORT_PATH

rm $SHOP_EXPORT_PATH/.git*

###############################################
# Additional files
###############################################

debug "creating additional files..."
touch $SHOP_EXPORT_PATH/shopinfo.xml
mkdir -p $SHOP_EXPORT_PATH/export
touch $SHOP_EXPORT_PATH/export/sitemap_index.xml
touch $SHOP_EXPORT_PATH/rss.xml
mkdir -p $SHOP_EXPORT_PATH/includes
touch $SHOP_EXPORT_PATH/includes/config.JTL-Shop.ini.initial.php

###############################################

#rm old templates
rm -r $SHOP_EXPORT_PATH/templates/Tiny
rm -r $SHOP_EXPORT_PATH/templates/Mobile

###############################################
# Add vendor libs
###############################################
cd $SHOP_EXPORT_PATH/includes
curl -sS https://getcomposer.org/installer | php
php composer.phar install

###############################################
# Postprocessing build
###############################################

sed -i "s/#JTL_MINOR_VERSION#/${SHOPVERSION_BUILD}/g" $SHOP_EXPORT_PATH/includes/defines_inc.php
sed -i "s/#JTL_BUILD_TIMESTAMP#/${BUILD_TIMESTAMP}/g" $SHOP_EXPORT_PATH/includes/defines_inc.php

MD5_DB_FILENAME="$SHOP_EXPORT_PATH/admin/includes/shopmd5files/${SHOPVERSION_MAJOR}${SHOPVERSION_MINOR}.csv"
debug "generate MD5 checksum database in ${MD5_DB_FILENAME}..."
cd $SHOP_EXPORT_PATH
find . -type f ! -name robots.txt ! -name rss.xml ! -name shopinfo.xml ! -name .htaccess ! -samefile includes/defines.php ! -samefile includes/defines_inc.php ! -samefile includes/config.JTL-Shop.ini.initial.php -printf '%P\n' | grep -v -E '.git/|/.gitkeep|admin/gfx|admin/includes/emailpdfs|admin/includes/shopmd5files|admin/templates/gfx|admin/templates_c/|bilder/|downloads/|gfx/|includes/plugins|install/|jtllogs/|mediafiles/|templates/|templates_c/|uploads/|export/|shopinfo.xml|sitemap_index.xml' | xargs md5sum | awk '{ print $2";"$1; }' | sort > $MD5_DB_FILENAME

###############################################
# Create database schema
###############################################

# Determine last release version
if (( $SHOPVERSION_MINOR > 0 )); then
    LAST_MAJOR=$SHOPVERSION_MAJOR
    LAST_MINOR=0$(($SHOPVERSION_MINOR - 1))
else
    LAST_MAJOR=$(($SHOPVERSION_MAJOR - 1))
    LAST_MINOR=20
fi

echo Preparing DB-Checkfile: LAST_MAJOR is $LAST_MAJOR, LAST_MINOR is $LAST_MINOR
LAST_SCHEMA=$WORKSPACE/build/sql/${LAST_MAJOR}.${LAST_MINOR}.sql
debug "importing old schema from ${LAST_SCHEMA} ..."

if [ ! -e $LAST_SCHEMA ]; then
    debug "$LAST_SCHEMA: no such file or directory"
    exit 1
fi  

TEMPDB=jenkins_shop_${BASHPID}

echo "DROP DATABASE IF EXISTS ${TEMPDB}" | mysql
echo "CREATE DATABASE ${TEMPDB}" | mysql
mysql --default-character-set=latin1 -D ${TEMPDB} < $LAST_SCHEMA
if [ $? -ne 0 ]; then
    debug "error: could not import old schema"
    exit 1
fi

TEMPCONFIG=$SHOP_EXPORT_PATH/includes/config.JTL-Shop.ini.php

echo "<?php define('PFAD_ROOT', '${SHOP_EXPORT_PATH}/'); \
define('URL_SHOP', 'http://jenkins'); \
define('DB_HOST', 'localhost'); \
define('DB_NAME', '${TEMPDB}'); \
define('DB_USER', '${DB_USER}'); \
define('DB_PASS', '${DB_PASSWORD}'); \
define('BLOWFISH_KEY', 'BLOWFISH_KEY');" > $TEMPCONFIG

echo "CREATE TABLE IF NOT EXISTS tmigration (kMigration bigint(14) NOT NULL, nVersion int(3) NOT NULL, dExecuted datetime NOT NULL, PRIMARY KEY (kMigration)) ENGINE=InnoDB DEFAULT CHARSET=latin1" | mysql -D $TEMPDB
echo "CREATE TABLE IF NOT EXISTS tmigrationlog (kMigrationlog int(10) NOT NULL AUTO_INCREMENT, kMigration bigint(20) NOT NULL, cDir enum('up','down') NOT NULL, cState varchar(6) NOT NULL, cLog text NOT NULL, dCreated datetime NOT NULL, PRIMARY KEY (kMigrationlog)) ENGINE=InnoDB DEFAULT CHARSET=latin1" | mysql -D $TEMPDB

debug "Running migrations"

php -r "require_once '${SHOP_EXPORT_PATH}/includes/globalinclude.php'; \
\$manager = new MigrationManager(${LAST_MAJOR}${LAST_MINOR}); \
print_r(\$manager->migrate(null));"


debug "Compiling themes"

php ${SHOP_EXPORT_PATH}/includes/plugins/evo_editor/version/100/adminmenu/cli.php


rm $TEMPCONFIG

#truncate templates_c dir
rm -r $SHOP_EXPORT_PATH/templates_c/*

echo 'TRUNCATE tmigration' | mysql -D $TEMPDB
echo 'TRUNCATE tmigrationlog' | mysql -D $TEMPDB
echo 'TRUNCATE tversion' | mysql -D $TEMPDB

echo "INSERT INTO tversion (nVersion, nZeileVon, nZeileBis, nInArbeit, nFehler, nTyp, cFehlerSQL, dAktualisiert) VALUES ('${SHOPVERSION_MAJOR}${SHOPVERSION_MINOR}', 1, 0, 0, 0, 0, '', NOW())" | mysql -D $TEMPDB

if [ $CURRENT_BRANCH == "develop" ]; then
SCHEMASQL_PATH=$SHOP_EXPORT_PATH/install/initial_schema_develop.sql
debug "exporting initial DB schema to ${SCHEMASQL_PATH} ..."
mysqldump --default-character-set=latin1 $TEMPDB > $SCHEMASQL_PATH
fi

SCHEMAJSON_PATH=$SHOP_EXPORT_PATH/admin/includes/shopmd5files/dbstruct_${SHOPVERSION_MAJOR}$(printf '%02u' ${SHOPVERSION_MINOR}).json
debug "exporting DB structure into $SCHEMAJSON_PATH ..."
$WORKSPACE/build/scripts/export_db_schema.pl $TEMPDB > $SCHEMAJSON_PATH

debug "deleting intermediate database..."
echo "DROP DATABASE IF EXISTS ${TEMPDB}" | mysql

###############################################
# Build ZIP archive
###############################################

debug "zip and deploy ${TARGET}. CURRENT_BRANCH is ${CURRENT_BRANCH}. "

if [ $CURRENT_BRANCH == "develop" ]; then
    # Development branch
    debug "preparing devel ZIP"
    TARGET="jtlshop_devel"
else
    # Release or preparation branch
    debug "preparing release ZIP"
    TARGET="jtlshop_release"
fi

# Append current shop version
TARGET="${TARGET}_${SHOPVERSION_MAJOR}.${SHOPVERSION_MINOR}"
if [ $SHOPVERSION_BUILD -gt 0 ]; then
    TARGET="${TARGET}_build$SHOPVERSION_BUILD"
fi

# Append build timestamp
TARGET="${TARGET}_${BUILD_TIMESTAMP}"

if [ $CURRENT_BRANCH != "master" ]; then
    # Append git revision if on devel branch
    TARGET="${TARGET}_${HEADREVISION:0:9}"
fi

debug "final build will be placed in ${TARGET}.zip"

cd $SHOP_EXPORT_PATH
TMPZIPFILE="$WORKSPACE/build/${TARGET}.zip"

debug "compressing build into ${TMPZIPFILE}"
rm -f $TMPZIPFILE
zip -r $TMPZIPFILE .

echo BUILD_IDENTIFIER is ${BUILD_IDENTIFIER}
if test -n "$BUILD_IDENTIFIER"; then
    rm -f $WORKSPACE/build/jtl-shop-${BUILD_IDENTIFIER}.zip
    cp $TMPZIPFILE $WORKSPACE/build/jtl-shop-${BUILD_IDENTIFIER}.zip
fi