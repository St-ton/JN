#!/usr/bin/env bash

PROJECT_NAME=$1;
TAG=$2;
VERSION="${TAG//[\/\.]/-}";
FILENAME="$PROJECT_NAME-$VERSION.zip";
ARCHIVE_PATH="$3/$FILENAME";

echo "";
echo "Create zip of build files...";
zip -r ${ARCHIVE_PATH} . -x *.git*\* .idea*\* tools*\* tests*\* .travis.yml\* phpunit.xml\*;
echo "";