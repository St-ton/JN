#!/bin/bash

create_tpl_diff()
{
    # $1 repo dir
    REPO_DIR=$1;
    # $2 diff path
    DIFF_PATH=$2
    # $3 from tag
    DIFF_START_TAG=$3
    # $4 app version
    DIFF_END_TAG=$4

    VERSION_REGEX="v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";

    #if [[ ${DIFF_END_TAG} =~ ${VERSION_REGEX} ]]; then
        if [ "${DIFF_PATH}" == "templates/Evo" ]; then
            TPL_TYPE="evo";
        else
            TPL_TYPE="mail";
        fi

        DIFF_FILE_NAME=${REPO_DIR}/${TPL_TYPE}-${DIFF_START_TAG}-to-${DIFF_END_TAG}-tpl.diff;
        DIFF_CLEAN_FILE_NAME=${REPO_DIR}/${TPL_TYPE}-${DIFF_START_TAG}-to-${DIFF_END_TAG}-tplclean.diff;

        git diff --ignore-all-space --ignore-blank-lines --minimal --unified=2 ${DIFF_START_TAG}...${DIFF_END_TAG} ${DIFF_PATH} > ${DIFF_FILE_NAME};
        filterdiff --exclude='*.css' --exclude='*.txt' --exclude='*.ttf' --exclude='*.md' ${DIFF_FILE_NAME} > ${DIFF_CLEAN_FILE_NAME};
    #fi
}