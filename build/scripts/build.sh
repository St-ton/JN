#!/usr/bin/env bash

build_create()
{
    # $1 repository dir
    export REPOSITORY_DIR=$1;
    # $2 target build version
    export APPLICATION_VERSION_BASE=$2;
    if [[ $2 =~ (release)?\/?v?(.*) ]]; then
      export APPLICATION_VERSION="v${BASH_REMATCH[2]}";
    else
      export APPLICATION_VERSION=$2;
    fi
    # $3 last commit sha
    local APPLICATION_BUILD_SHA=$3;
    # $4 database host
    export DB_HOST=$4;
    # $5 database user
    export DB_USER=$5;
    # $6 database password
    export DB_PASSWORD=$6;
    # $7 database name
    export DB_NAME=$7;

    local SCRIPT_DIR="${REPOSITORY_DIR}/build/scripts";
    local VERSION_REGEX="v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";

    # source ${SCRIPT_DIR}/create_version_string.sh;
    source ${SCRIPT_DIR}/generate-tpl-checksums.sh;

    # Deactivate git renameList
    git config diff.renames 0;

    echo "Create build info";

	# extract version from defines_inc.php -> APPLICATION_VERSION
	definesInc=`cat ${REPOSITORY_DIR}/includes/defines_inc.php`;
	pattern="const[[:blank:]]APPLICATION_VERSION[[:blank:]]*=[[:blank:]]*'([0-9]\.[0-9].[0-9])(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?';"

	if [[ $definesInc =~ $pattern ]];then
		if [[ ! -z "${BASH_REMATCH[2]}" ]]; then
			  #if string contains prerelease versions like alpha|beta|rc
			  export APPLICATION_VERSION_STR="${BASH_REMATCH[1]}${BASH_REMATCH[2]}";
		else
			  export APPLICATION_VERSION_STR="${BASH_REMATCH[1]}";
		fi
	else
		echo "version extraction pattern did not found a match"
	fi

	#if tag was created, check if defines_inc version matches the tag version, if not, abort.
    if [[ ${APPLICATION_VERSION} =~ ${VERSION_REGEX} ]]; then
		if [[ "v${APPLICATION_VERSION_STR}" != "${APPLICATION_VERSION}" ]]; then
			echo "Tag creation aborted, please make sure the APPLICATION_VERSION (includes/defines_inc.php) and the tag version are the same (don't mind the leading 'v').";
			echo "parsed APPLICATION_VERSION: v${APPLICATION_VERSION_STR}";
			echo "tag version: ${APPLICATION_VERSION} ";
			echo "\n\nPLEASE DELETE THE FAILED, LAST CREATED TAG BEFORE CREATING A NEW ONE!";
			exit 1;
		fi
    fi

	# insert git sha hash into defines_inc.php -> APPLICATION_BUILD_SHA
    sed -ri "s/const APPLICATION_BUILD_SHA( *)= '#DEV#'/const APPLICATION_BUILD_SHA\1= '${APPLICATION_BUILD_SHA}'/g" ${REPOSITORY_DIR}/includes/defines_inc.php

    echo "Executing composer";
    build_composer_execute;

    echo "Create delete files csv";
    build_create_deleted_files_csv;

    echo "Move class files";
    build_move_class_files;

    echo "Set classes path";
    build_set_classes_path;

    echo "Add old files";
    build_add_old_files;

    echo "Create shop installer";
    build_create_shop_installer;

    echo "Creating md5 hashfile";
    build_create_md5_hashfile;

    echo "Importing initial schema";
    build_import_initial_schema;

    echo "Writing config.JTL-Shop.ini.initial.php";
    build_create_config_file;

    echo "Compile css from scss files";
    build_compile_css_files;

    echo "Create templates md5 csv files";
    create_tpl_md5_hashfile "${REPOSITORY_DIR}/templates/Evo";
    create_tpl_md5_hashfile "${REPOSITORY_DIR}/templates/NOVA";

    echo "Executing migrations";
    build_migrate;

    echo "Reset mailtemplates";
    build_reset_mailtemplates;

    echo "Creating database struct";
    build_create_db_struct;

    echo "Creating new initial schema";
    build_create_initial_schema;

    echo "Clean up";
    build_clean_up;

    if [[ ${APPLICATION_VERSION} =~ ${VERSION_REGEX} ]]; then
	    echo "Create patch(es)";
	    build_create_patches "${BASH_REMATCH[@]}";
    fi

    # Activate git renameList
    git config diff.renames 1;
}

build_composer_execute()
{
    composer install --no-dev -o -q -d ${REPOSITORY_DIR}/includes;
}

build_create_deleted_files_csv()
{
    local VERSION="${APPLICATION_VERSION_STR//[\/\.]/-}";
    local VERSION="${VERSION//^[v]/}";
    local DELETE_FILES_CSV_FILENAME="${REPOSITORY_DIR}/admin/includes/shopmd5files/deleted_files_${VERSION}.csv";
    local BRANCH_REGEX="(master|release\\/([0-9]{1,})\\.([0-9]{1,}))";
    local REMOTE_STR="";

    if [[ ${APPLICATION_VERSION_BASE} =~ ${BRANCH_REGEX} ]]; then
        REMOTE_STR="origin/";
    else
        REMOTE_STR="tags/";
    fi

    cd ${REPOSITORY_DIR};
    git pull >/dev/null 2>&1;
    git diff --name-only --diff-filter D tags/v4.03.0 ${REMOTE_STR}${APPLICATION_VERSION_BASE} -- ${REPOSITORY_DIR} ':!admin/classes' ':!classes' ':!includes/ext' ':!includes/plugins' ':!templates/Evo' > ${DELETE_FILES_CSV_FILENAME};

    echo "  Deleted files schema admin/includes/shopmd5files/deleted_files_${VERSION}.csv";
}

build_move_class_files()
{
    # Move admin old classes
    if [[ -d "${REPOSITORY_DIR}/admin/classes/old/" ]]; then
        cp -a ${REPOSITORY_DIR}/admin/classes/old/. ${REPOSITORY_DIR}/admin/classes;
        rm -R ${REPOSITORY_DIR}/admin/classes/old;
    fi
    # Move old classes
    if [[ -d "${REPOSITORY_DIR}/classes/old/" ]]; then
        cp -a ${REPOSITORY_DIR}/classes/old/. ${REPOSITORY_DIR}/classes;
        rm -R ${REPOSITORY_DIR}/classes/old;
    fi
}

build_set_classes_path()
{
    sed -i "s/'PFAD_CLASSES', '.*'/'PFAD_CLASSES', 'classes\/'/g" ${REPOSITORY_DIR}/includes/defines.php
}

build_add_old_files()
{
    while read line; do
        echo "<?php // moved to /includes/src" > ${REPOSITORY_DIR}/${line};
    done < ${REPOSITORY_DIR}/oldfiles.txt;

    rm ${REPOSITORY_DIR}/oldfiles.txt;
}

build_create_shop_installer() {
    npm --prefix ${REPOSITORY_DIR}/build/components/vue-installer install && npm --prefix ${REPOSITORY_DIR}/build/components/vue-installer run build;
}

build_create_md5_hashfile()
{
    local VERSION="${APPLICATION_VERSION_STR//[\/\.]/-}";
    local VERSION="${VERSION//^[v]/}";
    local CUR_PWD=$(pwd);
    local MD5_HASH_FILENAME="${REPOSITORY_DIR}/admin/includes/shopmd5files/${VERSION}.csv";

    cd ${REPOSITORY_DIR};
    find -type f -not \( -name ".asset_cs" \
      -or -name ".git*" -or -name ".idea*" \
      -or -name ".php_cs" -or -name ".travis.yml" \
      -or -name ".htaccess" \
      -or -name "${VERSION}.csv" -or -name "composer.lock" \
      -or -name "config.JTL-Shop.ini.initial.php" \
      -or -name "phpunit.xml" -or -name "robots.txt" \
      -or -name "rss.xml" -or -name "shopinfo.xml" \
      -or -name "sitemap_index.xml" -or -name "*.md" \) -printf "'%P'\n" \
    | grep -v -f "${REPOSITORY_DIR}/build/scripts/md5_excludes.lst" \
    | xargs md5sum | awk '{ print $1";"$2; }' \
    | sort --field-separator=';' -k2 -k1 > ${MD5_HASH_FILENAME};

    find -type f -name '.htaccess' \
	  -and \( \
		-not -regex './.htaccess' \
		-not -regex './build/.*' \
		-not -regex './install/.*' \)  -printf "'%P'\n" \
    | xargs md5sum | awk '{ print $1";"$2; }' \
    | sort --field-separator=';' -k2 -k1 >> ${MD5_HASH_FILENAME};

    echo "  File checksums admin/includes/shopmd5files/${VERSION}.csv";
}

build_import_initial_schema()
{
    local INITIALSCHEMA=${REPOSITORY_DIR}/install/initial_schema.sql

    mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME}";

    while read -r table;
    do
        mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "DROP TABLE IF EXISTS ${table}";
    done< <(mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "show tables;" | sed 1d);

    mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} < ${INITIALSCHEMA};
}

build_create_config_file()
{
    echo "<?php define('PFAD_ROOT', '${REPOSITORY_DIR}/'); \
        define('URL_SHOP', 'http://build'); \
        define('DB_HOST', '${DB_HOST}'); \
        define('DB_USER', '${DB_USER}'); \
        define('DB_PASS', '${DB_PASSWORD}'); \
        define('DB_NAME', '${DB_NAME}'); \
        define('BLOWFISH_KEY', 'BLOWFISH_KEY');" > ${REPOSITORY_DIR}/includes/config.JTL-Shop.ini.php;
}

build_migrate()
{
    php -r "
    require_once '${REPOSITORY_DIR}/includes/globalinclude.php'; \
      \$time    = date('YmdHis'); \
      \$manager = new MigrationManager(Shop::Container()->getDB()); \
      try { \
          \$migrations = \$manager->migrate(\$time); \
      } catch (Exception \$e) { \
          \$migration = \$manager->getMigrationById(array_pop(array_reverse(\$manager->getPendingMigrations()))); \
          \$result    = new IOError('Migration: '.\$migration->getName().' | Errorcode: '.\$e->getMessage()); \
          echo \$result->message; \
          return 1; \
      } \
    ";
    if [[ $? -ne 0 ]]; then
        exit 1;
    fi

    echo 'TRUNCATE tversion' | mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} -D ${DB_NAME};
    echo "INSERT INTO tversion (nVersion, nZeileVon, nZeileBis, nInArbeit, nFehler, nTyp, cFehlerSQL, dAktualisiert) VALUES ('${APPLICATION_VERSION_STR}', 1, 0, 0, 0, 0, '', NOW())" | mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} -D ${DB_NAME};
}

build_create_db_struct()
{
    local VERSION="${APPLICATION_VERSION_STR//[\/\.]/-}";
    local VERSION="${VERSION//^[v]/}";
    local i=0;
    local DB_STRUCTURE='{';
    local TABLE_COUNT=$(($(mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "show tables;" | wc -l)-1));
    local SCHEMAJSON_PATH="${REPOSITORY_DIR}/admin/includes/shopmd5files/dbstruct_${VERSION}.json";

    while ((i++)); read -r table;
    do
        DB_STRUCTURE+='"'${table}'":[';
        local j=0;
        local COLUMN_COUNT=$(($(mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "SHOW COLUMNS FROM ${table};" | wc -l)-1));

        while ((j++)); read -r column;
        do
            local value=$(echo "${column}" | awk -F'\t' '{print $1}');
            DB_STRUCTURE+='"'${value}'"';

            if [[ ${j} -lt ${COLUMN_COUNT} ]]; then
                DB_STRUCTURE+=',';
            else
                DB_STRUCTURE+=']';
            fi
        done< <(mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "SHOW COLUMNS FROM ${table};" | sed 1d);

        if [[ ${i} -lt ${TABLE_COUNT} ]]; then
            DB_STRUCTURE+=',';
        else
            DB_STRUCTURE+='}';
        fi
    done< <(mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "show tables;" | sed 1d);

    echo "${DB_STRUCTURE}" > ${SCHEMAJSON_PATH};

    echo "  Dbstruct file admin/includes/shopmd5files/dbstruct_${VERSION}.json";
}

build_create_initial_schema()
{
    local INITIAL_SCHEMA_PATH=${REPOSITORY_DIR}/install/initial_schema.sql;
    local MYSQL_CONN="-h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD}";
    local ORDER_BY="table_name ASC";
    local SQL="SET group_concat_max_len = 1048576;";
          SQL="${SQL} SELECT GROUP_CONCAT(table_name ORDER BY ${ORDER_BY} SEPARATOR ' ')";
          SQL="${SQL} FROM information_schema.tables";
          SQL="${SQL} WHERE table_schema='${DB_NAME}'";
    local TABLES=$(mysql ${MYSQL_CONN} -ANe"${SQL}");

    mysqldump -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} --default-character-set=utf8 --skip-comments=true --skip-dump-date=true \
        --add-locks=false --add-drop-table=false --no-autocommit=false ${DB_NAME} ${TABLES} > ${INITIAL_SCHEMA_PATH};
}

build_clean_up()
{
    #Delete created database
    mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} -e "DROP DATABASE IF EXISTS ${DB_NAME}";
    #Delete created config file
    rm ${REPOSITORY_DIR}/includes/config.JTL-Shop.ini.php;
}

build_create_patches()
{
    local i=1;
    local VERSION=("$@");

    local SHOP_VERSION_MAJOR=${VERSION[1]};
    local SHOP_VERSION_MINOR=${VERSION[2]};
    local SHOP_VERSION_PATCH=${VERSION[3]};

    if [[ ! -z "${VERSION[5]}" ]]; then
        SHOP_VERSION_GREEK=${VERSION[5]};

        if [[ ! -z "${VERSION[7]}" ]]; then
            SHOP_VERSION_PRERELEASENUMBER=${VERSION[7]};
        fi
    fi

    if [[ ! -z "${SHOP_VERSION_PRERELEASENUMBER}" ]]; then
        for (( i=1; ${i}<${SHOP_VERSION_PRERELEASENUMBER}; ((i++)) ))
        do
            local PATCH_VERSION="v${SHOP_VERSION_MAJOR}.${SHOP_VERSION_MINOR}.${SHOP_VERSION_PATCH}-${SHOP_VERSION_GREEK}.${i}";
            local PATCH_DIR="patch-dir-${PATCH_VERSION}-to-${APPLICATION_VERSION}";
            mkdir ${PATCH_DIR};

            build_add_files_to_patch_dir ${PATCH_VERSION} ${PATCH_DIR};
        done
    else
        for (( i=0; ${i}<${SHOP_VERSION_PATCH}; ((i++)) ))
        do
            local PATCH_VERSION="v${SHOP_VERSION_MAJOR}.${SHOP_VERSION_MINOR}.${i}";
            local PATCH_DIR="patch-dir-${PATCH_VERSION}-to-${APPLICATION_VERSION}";
            mkdir ${PATCH_DIR};

            build_add_files_to_patch_dir ${PATCH_VERSION} ${PATCH_DIR};
        done
    fi
}

build_add_files_to_patch_dir()
{
    local PATCH_VERSION=$1;
    local PATCH_DIR=$2;
    local VERSION="${APPLICATION_VERSION_STR//[\/\.]/-}";
    local VERSION="${VERSION//^[v]/}";

    echo "  Patch ${PATCH_VERSION} to ${APPLICATION_VERSION}";

    while read -r line;
    do
        local path=$(echo "${line}" | awk -F'\t' '{print $2}');
        local rename_path=$(echo "${line}" | awk -F'\t' '{print $3}');

        if [[ ! -z "${rename_path}" ]]; then
            path=${rename_path};
        fi
        if [[ -f ${path} ]]; then
            rsync -R ${path} ${PATCH_DIR};
        fi
    done< <(git diff --name-status --diff-filter=d ${PATCH_VERSION} ${APPLICATION_VERSION});

    rsync -R admin/includes/shopmd5files/${VERSION}.csv ${PATCH_DIR};
    rsync -R admin/includes/shopmd5files/dbstruct_${VERSION}.json ${PATCH_DIR};
    rsync -R admin/includes/shopmd5files/deleted_files_${VERSION}.csv ${PATCH_DIR};
    rsync -R includes/defines_inc.php ${PATCH_DIR};
    rsync -rR admin/classes/ ${PATCH_DIR};
    rsync -rR classes/ ${PATCH_DIR};
    rsync -rR includes/ext/ ${PATCH_DIR};
    rsync -rR includes/vendor/ ${PATCH_DIR};
    rsync -rR templates/NOVA/checksums.csv ${PATCH_DIR};
}

build_compile_css_files()
{
	php ${REPOSITORY_DIR}/cli compile:sass;
}
build_reset_mailtemplates()
{
    php ${REPOSITORY_DIR}/cli mailtemplates:reset
}


(build_create $*)
