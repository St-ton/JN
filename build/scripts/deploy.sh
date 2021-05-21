#!/usr/bin/env bash

PROJECT_NAME=$1;
TAG=$2;
VERSION="${TAG//[\/\.]/-}";
FILENAME="shop-${VERSION}.zip";
ARCHIVE_PATH="${3}/${FILENAME}";

echo "";
echo "] create zip of build '${TAG}'...";

# first, exclude all unneeded files and create MAIN archive
#zip -r -q ${ARCHIVE_PATH} . -x@/creation/build/scripts/archive_main_excludes.lst;
# second, add additional files to the previous created MAIN archive
zip -r -q ${ARCHIVE_PATH} . -x \*.git* \*.idea* \*build/* \*docs/* \*patch-dir-* \*templates_c/* \*tests/* \*.asset_cs \*.php_cs \*.travis.yml \*phpunit.xml;
zip -r -q ${ARCHIVE_PATH} . -i \*templates_c/.htaccess \*templates_c/min/.htaccess admin/templates_c/.gitkeep

chmod g+w ${ARCHIVE_PATH}
echo "  ${FILENAME}";
echo "";

if [[ ! -z $(find . -maxdepth 1 -type d -regex '^./patch-dir-.*') ]]; then
    echo "] create zip of patch(es)...";
    while read -r path;
    do
        PATCH_REGEX="./patch-dir-(.*)-to-(.*)";
        [[ ${path} =~ $PATCH_REGEX ]];
        LOWER_VERSION=${BASH_REMATCH[1]};
        LOWER_VERSION_STR="${LOWER_VERSION//[\.]/-}";
        HIGHER_VERSION=${BASH_REMATCH[2]};
        HIGHER_VERSION_STR="${HIGHER_VERSION//[\.]/-}";
        PATCH_FILENAME="shop-${LOWER_VERSION_STR}-to-${HIGHER_VERSION_STR}.zip";
        PATCH_ARCHIVE_PATH="${3}/${PATCH_FILENAME}";
        CUR_PWD=$(pwd);
        echo "] patch: '${LOWER_VERSION}' to '${HIGHER_VERSION}'";
        cd ${path};
		ls -la
        zip -r -q ${PATCH_ARCHIVE_PATH} . -x \*.git* \*.idea* \*build/* \*docs/* \*install/* \*patch-dir-* \*templates_c/* \*tests/* \*.asset_cs \*.php_cs \*.travis.yml \*phpunit.xml; 
		# zip -r -q ${PATCH_ARCHIVE_PATH} . -x@/creation/build/scripts/archive_patch_excludes.lst;
		zip -r -q ${PATCH_ARCHIVE_PATH} . -i \*templates_c/.htaccess \*templates_c/min/.htaccess;
		
        chmod g+w ${PATCH_ARCHIVE_PATH}
        echo "] filename: ${PATCH_FILENAME}";
        cd ${CUR_PWD};
    done< <(find . -maxdepth 1 -type d -regex '^./patch-dir-.*');
fi
