#!/bin/bash

# $1 target build version
APPLICATION_VERSION=$1;
# $2 last commit sha
APPLICATION_BUILD_SHA=$2;
# $3 repository dir
REPOSITORY_DIR=$3;
# start tag for tpl diff
TPL_DIFF_START_TAG="v4.06.9";

SCRIPT_DIR="${REPOSITORY_DIR}/tools/scripts";

source ${SCRIPT_DIR}/create_template_diff.sh
source ${SCRIPT_DIR}/create_version_string.sh

echo "Usage:"
echo "  build.sh <tag> <tag_sha> <repo_dir>"
echo ""
echo "Actions:"
echo "  - Create file version"
echo "  - Create evo tpl diff$"
echo "  - Create mail tpl diff$"

echo ""

create_version_string ${REPOSITORY_DIR} ${APPLICATION_VERSION} ${APPLICATION_BUILD_SHA};
create_tpl_diff ${REPOSITORY_DIR} "templates/Evo" ${TPL_DIFF_START_TAG} ${APPLICATION_VERSION};
create_tpl_diff ${REPOSITORY_DIR} "admin/mailtemplates" ${TPL_DIFF_START_TAG} ${APPLICATION_VERSION};