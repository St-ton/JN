#!/bin/bash

get_last_tag()
{
    GREATEST_MAJOR=0;
    GREATEST_MINOR=0;
    GREATEST_PATCH=0;
    GREATEST_GREEK="";
    GREATEST_PRERELEASENUMBER=0;
    TAGS=$(git ls-remote --refs --tags)
    VCS_REG_TAGS="refs\\/tags\\/v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";
    for tag in ${TAGS}; do
        if [[ ${tag} =~ ${VCS_REG_TAGS} ]]; then
            if [[ ${BASH_REMATCH[1]} -gt ${GREATEST_MAJOR} ]]; then
                GREATEST_MAJOR=${BASH_REMATCH[1]};
                GREATEST_MINOR=${BASH_REMATCH[2]};
                GREATEST_PATCH=${BASH_REMATCH[3]};
                if [[ -v ${BASH_REMATCH[5]} ]]; then
                    GREATEST_GREEK=${BASH_REMATCH[5]};
                    GREATEST_PRERELEASENUMBER=${BASH_REMATCH[7]};
                fi
            fi
            if [[ ${BASH_REMATCH[1]} -eq ${GREATEST_MAJOR} ]] && [ ${BASH_REMATCH[2]} -gt ${GREATEST_MINOR} ]; then
                GREATEST_MINOR=${BASH_REMATCH[2]};
                GREATEST_PATCH=${BASH_REMATCH[3]};
                if [[ -v ${BASH_REMATCH[5]} ]]; then
                    GREATEST_GREEK=${BASH_REMATCH[5]};
                    GREATEST_PRERELEASENUMBER=${BASH_REMATCH[7]};
                fi
            fi
            if [[ ${BASH_REMATCH[1]} -eq ${GREATEST_MAJOR} ]] && [ ${BASH_REMATCH[2]} -eq ${GREATEST_MINOR} ] && [ ${BASH_REMATCH[3]} -gt ${GREATEST_PATCH} ]; then
                GREATEST_PATCH=${BASH_REMATCH[3]};
                if [[ -v ${BASH_REMATCH[5]} ]]; then
                    GREATEST_GREEK=${BASH_REMATCH[5]};
                    GREATEST_PRERELEASENUMBER=${BASH_REMATCH[7]};
                fi
            fi
            GREEK=${BASH_REMATCH[5]};
            PRERELEASENUMBER=${BASH_REMATCH[7]};
            if [[ ! -z "${GREEK}" ]]; then
                if [[ ${BASH_REMATCH[1]} -eq ${GREATEST_MAJOR} ]] && [ ${BASH_REMATCH[2]} -eq ${GREATEST_MINOR} ] && [ ${BASH_REMATCH[3]} -eq ${GREATEST_PATCH} ] && [ "$GREEK" \> "$GREATEST_GREEK" ]; then
                    GREATEST_GREEK=${GREEK};
                    if [[ ! -z "${PRERELEASENUMBER}" ]]; then
                        GREATEST_PRERELEASENUMBER=${PRERELEASENUMBER};
                    fi
                fi
                if [[ ! -z "${PRERELEASENUMBER}" ]]; then
                    if [[ ${BASH_REMATCH[1]} -eq ${GREATEST_MAJOR} ]] && [ ${BASH_REMATCH[2]} -eq ${GREATEST_MINOR} ] && [ ${BASH_REMATCH[3]} -eq ${GREATEST_PATCH} ] && [ "$GREEK" == "$GREATEST_GREEK" ] && [ ${PRERELEASENUMBER} -gt ${GREATEST_PRERELEASENUMBER} ]; then
                        GREATEST_PRERELEASENUMBER=${PRERELEASENUMBER};
                    fi
                fi
            fi
        fi
    done

    LAST_TAG="v${GREATEST_MAJOR}.${GREATEST_MINOR}.${GREATEST_PATCH}";

    if [ ! -z "${GREATEST_GREEK}" ]; then
        LAST_TAG="${LAST_TAG}-${GREATEST_GREEK}";
        if [ ${GREATEST_PRERELEASENUMBER} -gt 0 ]; then
            LAST_TAG="${LAST_TAG}.${GREATEST_PRERELEASENUMBER}";
        fi
    fi
}

get_latest_patch()
{
    MAJOR=$1;
    MINOR=$2;
    GREATEST_LATEST_PATCH=0;
    TAGS=$(git ls-remote --refs --tags)
    VCS_REG_TAGS="refs\\/tags\\/v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";
    for tag in ${TAGS}; do
        if [[ ${tag} =~ ${VCS_REG_TAGS} ]]; then
            if [ ${MAJOR} -eq ${BASH_REMATCH[1]} ] && [ ${MINOR} -eq ${BASH_REMATCH[2]} ]; then
                if [ ${BASH_REMATCH[3]} -gt ${GREATEST_LATEST_PATCH} ]; then
                    GREATEST_LATEST_PATCH=${BASH_REMATCH[3]}
                fi
            fi
        fi
    done
}

create_version_string()
{
    REPO_DIR=$1;
    APPLICATION_VERSION=$2;
    APPLICATION_BUILD_SHA=$3;
    VCS_REG="release\\/([0-9]{1,})\\.([0-9]{1,})";
    VERSION_REGEX="v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";

    if [[ ${APPLICATION_VERSION} =~ ${VERSION_REGEX} ]]; then
        SHOP_VERSION_MAJOR=${BASH_REMATCH[1]};
        SHOP_VERSION_MINOR=${BASH_REMATCH[2]};
        SHOP_VERSION_PATCH=${BASH_REMATCH[3]};
        if [[ ! -z "${BASH_REMATCH[5]}" ]]; then
            SHOP_VERSION_GREEK=${BASH_REMATCH[5]};
            if [[ ! -z "${BASH_REMATCH[7]}" ]]; then
                SHOP_VERSION_PRERELEASENUMBER=${BASH_REMATCH[7]};
            fi
        fi
    else
        if [[ ${APPLICATION_VERSION} =~ ${VCS_REG} ]]; then
            SHOP_VERSION_MAJOR=${BASH_REMATCH[1]};
            SHOP_VERSION_MINOR=${BASH_REMATCH[2]};
            get_latest_patch ${SHOP_VERSION_MAJOR} ${SHOP_VERSION_MINOR};
            SHOP_VERSION_PATCH=$((GREATEST_LATEST_PATCH+1));
        else
            get_last_tag;
            if [[ ${LAST_TAG} =~ ${VERSION_REGEX} ]]; then
                SHOP_VERSION_MAJOR=${BASH_REMATCH[1]};
                SHOP_VERSION_MINOR=${BASH_REMATCH[2]};
                SHOP_VERSION_PATCH=${BASH_REMATCH[3]};
                if [ ! -z "${BASH_REMATCH[5]}" ]; then
                    SHOP_VERSION_GREEK=${BASH_REMATCH[5]};
                    if [ ! -z "${BASH_REMATCH[7]}" ]; then
                        SHOP_VERSION_PRERELEASENUMBER=${BASH_REMATCH[7]};
                    fi
                fi
            fi
        fi
    fi

    export NEW_VERSION="$SHOP_VERSION_MAJOR.$SHOP_VERSION_MINOR.$SHOP_VERSION_PATCH";

    if [ -v SHOP_VERSION_GREEK ] && [ ! -z "${SHOP_VERSION_GREEK}" ]; then
        NEW_VERSION="$NEW_VERSION-$SHOP_VERSION_GREEK";
        if [ -v SHOP_VERSION_PRERELEASENUMBER ] && [ ${SHOP_VERSION_PRERELEASENUMBER} -gt 0 ]; then
            NEW_VERSION="$NEW_VERSION.$SHOP_VERSION_PRERELEASENUMBER";
        fi
    fi

    sed -i "s/'APPLICATION_VERSION', '.*'/'APPLICATION_VERSION', '${NEW_VERSION}'/g" ${REPO_DIR}/includes/defines_inc.php
    sed -i "s/'APPLICATION_BUILD_SHA', '#DEV#'/'APPLICATION_BUILD_SHA', '${APPLICATION_BUILD_SHA}'/g" ${REPO_DIR}/includes/defines_inc.php
}