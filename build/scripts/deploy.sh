#!/usr/bin/env bash

PROJECT_NAME=$1;
TAG=$2;
VERSION="${TAG//[\/\.]/-}";
FILENAME="shop-${VERSION}.zip";
ARCHIVE_PATH="${3}/${FILENAME}";

echo "";
echo "Create zip of build '${TAG}'...";

zip -r -q ${ARCHIVE_PATH} . -x \*.git* \*.idea* \*build/* \*docs/* \*patch-dir-* \*tests/* \*.asset_cs \*.php_cs \*.travis.yml \*phpunit.xml \*includes/package.json \*includes/package-lock.json;
echo "  ${FILENAME}";
echo "";

if [[ ! -z $(find . -maxdepth 1 -type d -regex '^./patch-dir-.*') ]]; then
    echo "Create zip of patch(es)...";
    while read -r path;
    do
        PATCH_REGEX="./patch-dir-(.*)-to-(.*)";
        [[ ${path} =~ $PATCH_REGEX ]];
        LOWER_VERSION=${BASH_REMATCH[1]};
        LOWER_VERSION_STR="${LOWER_VERSION//[\.]/-}";
        HIGHER_VERSION=${BASH_REMATCH[2]};
        HIGHER_VERSION_STR="${HIGHER_VERSION//[\.]/-}";
        PATCH_FILENAME="${PROJECT_NAME}-${LOWER_VERSION_STR}-to-${HIGHER_VERSION_STR}.zip";
        PATCH_ARCHIVE_PATH="${3}/${PATCH_FILENAME}";
        CUR_PWD=$(pwd);
        echo "  Patch '${LOWER_VERSION}' to '${HIGHER_VERSION}'";
        cd ${path};
        zip -r -q ${PATCH_ARCHIVE_PATH} . -x  \*.git* \*.idea* \*docs/* \*patch-dir-* \*tests/* \*tools/* \*.asset_cs \*.php_cs \*.travis.yml \*phpunit.xml \*includes/package.json \*includes/package-lock.json;
        echo "    ${PATCH_FILENAME}";
        cd ${CUR_PWD};
    done< <(find . -maxdepth 1 -type d -regex '^./patch-dir-.*');
fi