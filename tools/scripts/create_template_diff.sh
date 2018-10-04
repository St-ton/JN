#!/bin/bash

create_tpl_diff()
{
    # $3 diff path
    DIFF_PATH=$1;

    if [[ ${APPLICATION_VERSION} =~ ${VERSION_REGEX} ]]; then
        if [ "${DIFF_PATH}" == "templates/Evo" ]; then
            TPL_TYPE="evo";
        else
            TPL_TYPE="mail";
        fi

        DIFF_FILE_NAME=${REPO_DIR}/${TPL_TYPE}-${FROM_TAG}-to-${APPLICATION_VERSION}-tpl.diff;
        DIFF_CLEAN_FILE_NAME=${REPO_DIR}/${TPL_TYPE}-${FROM_TAG}-to-${APPLICATION_VERSION}-tplclean.diff;

        git diff --ignore-all-space --ignore-blank-lines --minimal --unified=2 ${FROM_TAG}...${APPLICATION_VERSION} ${DIFF_PATH} > ${DIFF_FILE_NAME};
        filterdiff --exclude='*.css' --exclude='*.txt' --exclude='*.ttf' --exclude='*.md' ${DIFF_FILE_NAME} > ${DIFF_CLEAN_FILE_NAME};
    fi
}