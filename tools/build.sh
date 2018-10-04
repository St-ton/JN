#!/bin/bash

# $1 target build version
export APPLICATION_VERSION=$1;
# $2 last commit sha
export APPLICATION_BUILD_SHA=$2;
# $3 repository dir
export REPO_DIR=$3;
# start tag for tpl diff
export FROM_TAG="v4.06.9";

export SCRIPT_DIR="${REPO_DIR}/tools/scripts";
export VERSION_REGEX="v?([0-9]{1,})\\.([0-9]{1,})\\.([0-9]{1,})(-(alpha|beta|rc)(\\.([0-9]{1,}))?)?";

source ${SCRIPT_DIR}/create_template_diff.sh
source ${SCRIPT_DIR}/create_version_string.sh
source ${SCRIPT_DIR}/tools.sh

echo "${fgYellow}Usage:${C}"
echo "  build.sh <tag> <tag_sha> <repo_dir>"
echo ""
echo "${fgYellow}Actions:${C}"
echo "  ${fgGreen}- Create file version${C}"
echo "  ${fgGreen}- Create evo tpl diff${C}"
echo "  ${fgGreen}- Create mail tpl diff${C}"

echo ""

create_version_string;
create_tpl_diff "templates/Evo";
create_tpl_diff "admin/mailtemplates";