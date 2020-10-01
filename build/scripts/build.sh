#!/usr/bin/env bash

build_create()
{
    # $1 repository dir
    export REPOSITORY_DIR=$1;
    # $2 target build version
    export APPLICATION_VERSION=$2;
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

    source ${SCRIPT_DIR}/create_version_string.sh;
    source ${SCRIPT_DIR}/generate-tpl-checksums.sh;

    # Deactivate git renameList
    git config diff.renames 0;

    echo "Create build info";
    create_version_string ${REPOSITORY_DIR} ${APPLICATION_VERSION} ${APPLICATION_BUILD_SHA};

    if [[ "$APPLICATION_VERSION"  == "master" ]]; then
        if [[ ! -z "${NEW_VERSION}" ]]; then
            export APPLICATION_VERSION_STR=${NEW_VERSION};
        fi
    else
        export APPLICATION_VERSION_STR=${APPLICATION_VERSION};
    fi

    echo "Executing composer";
    build_composer_execute;

    echo "Create delete files csv";
    build_create_deleted_files_csv;

    echo "Create templates md5 csv files";
    create_tpl_md5_hashfile "${REPOSITORY_DIR}/templates/Evo";
    create_tpl_md5_hashfile "${REPOSITORY_DIR}/templates/NOVA";

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

    echo "Executing migrations";
    build_migrate;

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
    local VERSION="${VERSION//[v]/}";
    local CUR_PWD=$(pwd);
    local DELETE_FILES_CSV_FILENAME="${REPOSITORY_DIR}/admin/includes/shopmd5files/deleted_files_${VERSION}.csv";
    local DELETE_FILES_CSV_FILENAME_TMP="${REPOSITORY_DIR}/admin/includes/shopmd5files/deleted_files_${VERSION}_tmp.csv";
    local BRANCH_REGEX="(master|release\\/([0-9]{1,})\\.([0-9]{1,}))";
    local REMOTE_STR="";

    if [[ ${APPLICATION_VERSION} =~ ${BRANCH_REGEX} ]]; then
        REMOTE_STR="origin/";
    else
        REMOTE_STR="tags/";
    fi

    cd ${REPOSITORY_DIR};
    git pull 2>&1 >/dev/null;
    git diff --name-status --diff-filter D tags/v4.03.0 ${REMOTE_STR}${APPLICATION_VERSION} -- ${REPOSITORY_DIR} ':!admin/classes' ':!classes' ':!includes/ext' ':!includes/plugins' > ${DELETE_FILES_CSV_FILENAME_TMP};
    cd ${CUR_PWD};

    while read line; do
        local LINEINPUT="${line//[D ]/}";
        echo ${LINEINPUT} >> ${DELETE_FILES_CSV_FILENAME};
    done < ${DELETE_FILES_CSV_FILENAME_TMP};

    rm ${DELETE_FILES_CSV_FILENAME_TMP};

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
    composer install --no-dev -o -q -d ${REPOSITORY_DIR}/build/components/vue-installer;
}

build_create_md5_hashfile()
{
    local VERSION="${APPLICATION_VERSION_STR//[\/\.]/-}";
    local VERSION="${VERSION//[v]/}";
    local CUR_PWD=$(pwd);
    local MD5_HASH_FILENAME="${REPOSITORY_DIR}/admin/includes/shopmd5files/${VERSION}.csv";

    cd ${REPOSITORY_DIR};
    find -type f ! \( -name ".asset_cs" -or -name ".git*" -or -name ".idea*" -or -name ".htaccess" -or -name ".php_cs" -or -name ".travis.yml" -or -name "${VERSION}.csv" -or -name "composer.lock" -or -name "config.JTL-Shop.ini.initial.php" -or -name "phpunit.xml" -or -name "robots.txt" -or -name "rss.xml" -or -name "shopinfo.xml" -or -name "sitemap_index.xml" -or -name "*.md" \) -printf "'%P'\n" | grep -vE ".git/|admin/gfx/|admin/includes/emailpdfs/|admin/templates_c/|bilder/|build/|docs/|downloads/|export/|gfx/|includes/plugins/|includes/vendor/|install/|jtllogs/|mediafiles/|templates/|templates_c/|tests/|uploads/" | xargs md5sum | awk '{ print $1";"$2; }' | sort --field-separator=';' -k2 -k1 > ${MD5_HASH_FILENAME};
    cd ${CUR_PWD};

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
    local VERSION="${VERSION//[v]/}";
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
    local VERSION="${VERSION//[v]/}";

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
    rsync -rR templates/NOVA/checksums.csv ${PATCH_DIR};

    if [[ -f "${PATCH_DIR}/includes/composer.json" ]]; then
        mkdir "/tmp_composer-${PATCH_VERSION}";
        mkdir "/tmp_composer-${PATCH_VERSION}/includes";
        touch "/tmp_composer-${PATCH_VERSION}/includes/composer.json";
        git show ${PATCH_VERSION}:includes/composer.json > /tmp_composer-${PATCH_VERSION}/includes/composer.json;
        git show ${PATCH_VERSION}:includes/composer.lock > /tmp_composer-${PATCH_VERSION}/includes/composer.lock;
        composer install --no-dev -o -q -d /tmp_composer-${PATCH_VERSION}/includes;

        while read -r line;
        do
            path=$(echo "${line}" | grep "^Files.*differ$" | sed 's/^Files .* and \(.*\) differ$/\1/');
            if [[ -z "${path}" ]]; then
                filename=$(echo "${line}" | grep "^Only in includes\/vendor.*: .*$" | sed 's/^Only in \(includes\/vendor[\/]*.*\): \(.*\)$/\1\/\2/');
                if [[ ! -z "${filename}" ]]; then
                    path="${filename}";
                    rsync -Ra -f"+ *" ${path} ${PATCH_DIR};
                fi
            else
                rsync -R ${path} ${PATCH_DIR};
            fi
        done< <(diff -rq /tmp_composer-${PATCH_VERSION}/includes/vendor includes/vendor);
    fi
}


(build_create $*)
