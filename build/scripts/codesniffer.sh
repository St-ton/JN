#!/usr/bin/env sh

export REPO_DIR=$1


if [ -f ${REPO_DIR}/includes/vendor/bin/phpcs ]; then
    echo "] start code quality test.."
    ${REPO_DIR}/includes/vendor/bin/phpcs -n\
        --extensions=php \
        --standard=${REPO_DIR}/phpcs-gitlab.xml \
        --exclude=PSR1.Methods.CamelCapsMethodName \
        --file-list=filelisting.txt \
        --ignore=${REPO_DIR}/includes/class_aliases \
        --report-info=${REPO_DIR}/code-quality-report.txt \
        --report-full

else
    echo "] phpcs executable not found!"
    exit 1
fi
