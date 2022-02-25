#!/usr/bin/env bash

build_create()
{
    # $1 repository dir
    export REPOSITORY_DIR=$1;
    # $2 target build version
    export APPLICATION_VERSION=$2;
    # $3 database user
    export DB_HOST=$3;
    # $4 database user
    export DB_USER=$4;
    # $5 database password
    export DB_PASSWORD=$5;
    # $6 database name
    export DB_NAME=$6;

    local SCRIPT_DIR="${REPOSITORY_DIR}/build/scripts";
    local VERSION_REGEX="v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";

    source ${SCRIPT_DIR}/create_version_string.sh;

    # Deactivate git renameList
    git config diff.renames 0;

    echo "Create build info";
    create_version_string ${REPOSITORY_DIR} ${APPLICATION_VERSION};

    if [[ ! -z "${MAJOR_MINOR_VERSION}" ]]; then
        export APPLICATION_VERSION_STR=${MAJOR_MINOR_VERSION};
    else
        export APPLICATION_VERSION_STR=${APPLICATION_VERSION};
    fi

    echo "Executing composer";
    build_composer_execute;

    echo "Git Submodule init";
    build_git_submodule_init;

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
    composer install --no-dev -q -d ${REPOSITORY_DIR}/includes;
}

build_git_submodule_init()
{
    git config --global http.sslverify false;
    git submodule init -- ${REPOSITORY_DIR};
    git submodule update -- ${REPOSITORY_DIR};
}

build_create_md5_hashfile()
{
    local CUR_PWD=$(pwd);
    local MD5_HASH_FILENAME="${REPOSITORY_DIR}/admin/includes/shopmd5files/${APPLICATION_VERSION_STR}.csv";

    cd ${REPOSITORY_DIR};

    find -type f -not \( -name ".asset_cs" \
      -or -name ".git*" -or -name ".idea*" \
      -or -name ".php_cs" -or -name ".travis.yml" \
      -or -name ".htaccess" \
      -or -name "${APPLICATION_VERSION_STR}.csv" -or -name "composer.lock" \
      -or -name "config.JTL-Shop.ini.initial.php" \
      -or -name "phpunit.xml" -or -name "robots.txt" \
      -or -name "rss.xml" -or -name "shopinfo.xml" \
      -or -name "sitemap_index.xml" -or -name "*.md" \) -printf "'%P'\n" \
    | grep -v -f "${REPOSITORY_DIR}/build/scripts/md5_excludes.lst" \
    | xargs md5sum | awk '{ print $2";"$1; }' \
    | sort --field-separator=';' -k1 -k2 > ${MD5_HASH_FILENAME};
    
    find -type f -name '.htaccess' \
        -and \( \
            -not -regex './.htaccess' \
            -not -regex './install/.*' \
            -not -regex './build/.*' \)  -printf "'%P'\n" \
    | xargs md5sum | awk '{ print $2";"$1; }' \
    | sort --field-separator=';' -k1 -k2 >> ${MD5_HASH_FILENAME};
    
    cd ${CUR_PWD};

    echo "  File checksums admin/includes/shopmd5files/${APPLICATION_VERSION_STR}.csv";
}

build_import_initial_schema()
{
    local INITIALSCHEMA=${REPOSITORY_DIR}/install/initial_schema.sql

    mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME}" || $(echo "failed to create database" && exit 1);

    while read -r table;
    do
        mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "DROP TABLE IF EXISTS ${table}" || $(echo "failed to delete table ${table}" && exit 1);
    done< <(mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "show tables;" | sed 1d);

    mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} < ${INITIALSCHEMA} || $(echo "failed to import initial schema to database" && exit 1);
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
        \$manager = new MigrationManager(); \

        try {
            \$result = \$manager->migrate(null);
        } catch (Exception \$e) {
            \$migration = \$manager->getMigrationById(array_pop(array_reverse(\$manager->getPendingMigrations())));
            \$result = new IOError('Migration: '.\$migration->getName().' | Errorcode: '.\$e->getMessage());
            echo \$result;
            exit(1);
        }
    ";

    echo 'TRUNCATE tversion' | mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} -D ${DB_NAME};
    echo "INSERT INTO tversion (nVersion, nZeileVon, nZeileBis, nInArbeit, nFehler, nTyp, cFehlerSQL, dAktualisiert) VALUES ('${APPLICATION_VERSION_STR}', 1, 0, 0, 0, 0, '', NOW())" | mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} -D ${DB_NAME};
}

build_create_db_struct()
{
    local i=0;
    local DB_STRUCTURE='{';
    local TABLE_COUNT=$(($(mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "show tables;" | wc -l)-1));
    local SCHEMAJSON_PATH=${REPOSITORY_DIR}/admin/includes/shopmd5files/dbstruct_${APPLICATION_VERSION_STR}.json;

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

    mysqldump -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} --default-character-set=latin1 --skip-comments=true --skip-dump-date=true \
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
        elif [[ -d ${path} ]]; then
            rsync -Rrl ${path} ${PATCH_DIR};
        fi
    done< <(git diff --name-status --diff-filter=d ${PATCH_VERSION} ${APPLICATION_VERSION});
    
    # Rsync shopmd5files
    rsync -R admin/includes/shopmd5files/dbstruct_${APPLICATION_VERSION_STR}.json ${PATCH_DIR};
    rsync -R admin/includes/shopmd5files/${APPLICATION_VERSION_STR}.csv ${PATCH_DIR};
    rsync -R includes/defines_inc.php ${PATCH_DIR};

    if [[ -f "${PATCH_DIR}/includes/composer.json" ]]; then
        mkdir /tmp/composer_${PATCH_VERSION};
        mkdir /tmp/composer_${PATCH_VERSION}/includes;
        git show ${PATCH_VERSION}:includes/composer.json > /tmp/composer_${PATCH_VERSION}/includes/composer.json;
        git show ${PATCH_VERSION}:includes/composer.lock > /tmp/composer_${PATCH_VERSION}/includes/composer.lock;
        composer install --no-dev -q -d /tmp/composer_${PATCH_VERSION}/includes;

        while read -r line;
        do
            path=$(echo "${line}" | grep "^Files.*differ$" | sed 's/^Files .* and \(.*\) differ$/\1/');
            if [[ -z "${path}" ]]; then
                filename=$(echo "${line}" | grep "^Only in includes\/vendor.*: .*$" | sed 's/^Only in \(includes\/vendor[\/]*.*\): \(.*\)$/\1\/\2/');
                if [[ ! -z "${filename}" ]] && [[ -f ${filename} ]]; then
                    path="${filename}";
                    rsync -Ra -f"+ *" ${path} ${PATCH_DIR};
                elif [[ ! -z "${filename}" ]] && [[ -d ${filename} ]]; then
                    rsync -Rrl ${filename} ${PATCH_DIR};
                fi
            else
                rsync -R ${path} ${PATCH_DIR};
            fi
        done< <(diff -rq -x \*composer.lock /tmp/composer_${PATCH_VERSION}/includes/vendor includes/vendor);
    fi
}


(build_create $*)
