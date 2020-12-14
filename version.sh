#!/usr/bin/env bash

APPLICATION_VERSION_STR="v5.0.0-rc.3";
version=($(php cli build:version_to_array $APPLICATION_VERSION_STR));

# if Prerelease is set e.g(alpha,beta,rc etc.)
if [[ ! -z "${version[3]}" ]]; then
    # set the max version to check e.g.: if current build is v5.0.0-rc.3, get the prerelase version "3" and decrement it to "2"
	max_version=$((${version[4]}-1));
	# ask git to list all tags with pattern e.g: v5.0.0-rc.[0-2] and save the output into a bash array
	lower_versions=($(git tag --list "v${version[0]}.${version[1]}.${version[2]}-${version[3]}.[0-${max_version}]"));
#else if it is not a prerelase and the patch version is greater than 0
elif [[ "$version[2]}" -gt 0 ]]; then
	max_version=$((${version[2]}-1));
	lower_versions=($(git tag --list "v${version[0]}.${version[1]}.[0-${max_version}]"));
fi

if [[ ! -z "${lower_versions}" ]];then
	for v in "${lower_versions[@]}"
	do
	   PATCH_DIR="patch-dir-${v}-to-${APPLICATION_VERSION_STR}";
	   echo $v;
	   echo $PATCH_DIR;
	done
fi





# declare -A lower_versions = (php ${REPOSITORY_DIR}/cli build:version_pre_releases ${APPLICATION_VERSION_STR});