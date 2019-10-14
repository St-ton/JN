#!/usr/bin/env bash

create_tpl_md5_hashfile()
{
    local TPL_DIR=$1;
    local CUR_PWD=$(pwd);
    local MD5_HASH_FILENAME="${TPL_DIR}/checksums.csv";

    if [[ -z "$TPL_DIR" ]]; then
        echo "TPL dir path is missing!";
        exit 1;
    fi

    if [[ "$TPL_DIR" != /* ]]; then
        echo "Only full tpl path is allowed!";
        exit 1;
    fi

    cd ${TPL_DIR};
    find -type f ! \( -name ".asset_cs" -or -name ".git*" -or -name ".idea*" -or -name ".htaccess" -or -name ".php_cs" -or -name ".travis.yml" -or -name "checksums.csv" -or -name "custom.css" -or -name "composer.lock" \) -printf "'%P'\n" | grep -vE ".git/" | xargs md5sum | awk '{ print $1";"$2; }' | sort --field-separator=';' -k2 -k1 > ${MD5_HASH_FILENAME};
    cd ${CUR_PWD};

    echo "  File checksums ${MD5_HASH_FILENAME}";
}

if [[ $# -eq 1 ]]; then
    (create_tpl_md5_hashfile $*)
fi