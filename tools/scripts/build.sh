#!/usr/bin/env bash

build_create() {
    # $1 target build version
    APPLICATION_VERSION=$1;
    # $2 last commit sha
    APPLICATION_BUILD_SHA=$2;
    # $3 repository dir
    REPOSITORY_DIR=$3;
    # start tag for tpl diff
    TPL_DIFF_START_TAG="v4.06.9";

    SCRIPT_DIR="${REPOSITORY_DIR}/tools/scripts";

    source ${SCRIPT_DIR}/create_template_diff.sh;
    source ${SCRIPT_DIR}/create_version_string.sh;

    echo "Create build info";
    create_version_string ${REPOSITORY_DIR} ${APPLICATION_VERSION} ${APPLICATION_BUILD_SHA};

    echo "Executing composer";
    build_composer_execute ${REPOSITORY_DIR}/includes;

    echo "Create delete files csv";
    build_deleted_files_csv ${REPOSITORY_DIR} ${APPLICATION_VERSION};

    echo "Move class files";
    build_move_class_files ${REPOSITORY_DIR};

    echo "Set classes path";
    build_set_classes_path ${REPOSITORY_DIR}/includes/defines.php;

    echo "Add old files";
    build_add_old_files ${REPOSITORY_DIR};

    echo "Creating md5 hashfile";
    build_md5_hashfile ${APPLICATION_VERSION} ${REPOSITORY_DIR};

    #echo "Importing initial schema";
    #deploy_import_initial_schema ${DB_NAME}

    #echo "Writing config.JTL-Shop.ini.initial.php";
    #deploy_config_file ${DB_NAME}

    #echo "Executing migrations";
    #deploy_migrate ${DB_NAME} ${SHOP_VERSION}

    #echo "Creating database struct";
    #deploy_db_struct ${DB_NAME} ${SHOP_VERSION}

    #echo "Preparing archive";
    #deploy_prepare_zip

    #echo "Creating archive";
    #deploy_create_zip ${TARGET_FULLPATH}

    #echo "Cleaning workspace";
    #deploy_clean ${DB_NAME}
}

build_composer_execute() {
    composer install --no-dev -d $1;
}

build_deleted_files_csv()
{
    local REPO_DIR=${1};
    local VERSION="${2//[\/\.]/-}";
    local VERSION="${VERSION//[v]/}";
    local CUR_PWD=$(pwd);
    local DELETE_FILES_CSV_FILENAME="${REPO_DIR}/admin/includes/shopmd5files/deleted_files_${VERSION}.csv";
    local DELETE_FILES_CSV_FILENAME_TMP="${REPO_DIR}/admin/includes/shopmd5files/deleted_files_${VERSION}_tmp.csv";

    cd ${REPO_DIR};
    git diff --name-status --diff-filter D v4.03.0 ${2} -- ${REPO_DIR} ':!admin/classes' ':!classes' ':!includes/ext' ':!includes/plugins' > ${DELETE_FILES_CSV_FILENAME_TMP};
    cd ${CUR_PWD};

    while read line; do
        local LINEINPUT="${line//[D ]/}";
        echo ${LINEINPUT} >> ${DELETE_FILES_CSV_FILENAME};
    done < ${DELETE_FILES_CSV_FILENAME_TMP};

    rm ${DELETE_FILES_CSV_FILENAME_TMP};
}

build_move_class_files() {
    local REPOSITORY_DIR=$1

    # Move admin old classes
    if [ -d "${REPOSITORY_DIR}/admin/classes/old/" ]; then
        cp -a ${REPOSITORY_DIR}/admin/classes/old/. ${REPOSITORY_DIR}/admin/classes
        rm -R ${REPOSITORY_DIR}/admin/classes/old
    fi
    # Move old classes
    if [ -d "${REPOSITORY_DIR}/classes/old/" ]; then
        cp -a ${REPOSITORY_DIR}/classes/old/. ${REPOSITORY_DIR}/classes
        rm -R ${REPOSITORY_DIR}/classes/old
    fi
}

build_set_classes_path() {
    sed -i "s/'PFAD_CLASSES', '.*'/'PFAD_CLASSES', '\/classes'/g" ${1}
}

build_add_old_files() {
    local REPOSITORY_DIR=${1};

    while read line; do
        echo "<?php // moved to /includes/src" > ${REPOSITORY_DIR}/${line};
    done < ${REPOSITORY_DIR}/oldfiles.txt
}

build_md5_hashfile()
{
    local VERSION="${1//[\/\.]/-}";
    local VERSION="${VERSION//[v]/}";
    local REPO_DIR=${2};
    local CUR_PWD=$(pwd);
    local MD5_HASH_FILENAME="${REPO_DIR}/admin/includes/shopmd5files/${VERSION}.csv";

    cd ${REPO_DIR};
    find -type f ! \( -name ".git*" -o -name ".idea*" -o -name ".htaccess" -o -name ".php_cs" -o -name "config.JTL-Shop.ini.initial.php" -o -name "robots.txt" -o -name "rss.xml" -o -name "shopinfo.xml" -o -name "sitemap_index.xml" -o -name "*.md" \) -printf "'%P'\n" | grep -vE "admin/gfx/|admin/includes/emailpdfs/|admin/includes/shopmd5files/|admin/templates_c/|bilder/|docs/|downloads/|export/|gfx/|includes/plugins/|includes/vendor/|install/|jtllogs/|mediafiles/|templates_c/|tests/|tools/|uploads/" | xargs md5sum | awk '{ print $1";"$2; }' | sort --field-separator=';' -k2 -k1 > ${MD5_HASH_FILENAME}
    cd ${CUR_PWD};
}

(build_create $*)