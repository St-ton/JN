#!/bin/bash

create_tpl_diff()
{
    # $1 diff path
    DIFF_PATH=$1;
    # $2 from tag
    DIFF_START_TAG=$2;
    # $3 app version
    DIFF_END_TAG=$3;
    # $4 base path
    BASE_PATH=$4;

    VERSION_REGEX="v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";

    if [[ ${DIFF_END_TAG} =~ ${VERSION_REGEX} ]]; then
        if [[ "${DIFF_PATH}" == "templates/NOVA" ]]; then
            TPL_TYPE="nova";
        elif [[ "${DIFF_PATH}" == "templates/Evo" ]]; then
            TPL_TYPE="evo";
        else
            TPL_TYPE="mail";
        fi

        echo "] create: ${TPL_TYPE} tpl diff";

        DIFF_FILE_NAME=${BASE_PATH}/${TPL_TYPE}-${DIFF_START_TAG}-to-${DIFF_END_TAG}-tpl.diff;
        DIFF_CLEAN_FILE_NAME=${BASE_PATH}/${TPL_TYPE}-${DIFF_START_TAG}-to-${DIFF_END_TAG}-tplclean.diff;

        git pull >/dev/null 2>&1;
        git diff --ignore-all-space --ignore-blank-lines --minimal --unified=2 ${DIFF_START_TAG} ${DIFF_END_TAG} -- ${DIFF_PATH} > ${DIFF_FILE_NAME};
        filterdiff --exclude='*.css' --exclude='*.map' --exclude='*.txt' --exclude='*.ttf' --exclude='*.md' ${DIFF_FILE_NAME} > ${DIFF_CLEAN_FILE_NAME};

        mv -u ${DIFF_CLEAN_FILE_NAME} ${DIFF_FILE_NAME};
        chmod g+w ${DIFF_FILE_NAME};

        echo "] diff filename: ${TPL_TYPE}-${DIFF_START_TAG}-to-${DIFF_END_TAG}-tpl.diff";
    fi
}


(create_tpl_diff $*)
