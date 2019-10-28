#!/usr/bin/env bash

export REPO_DIR=$1

echo "Execute composer install"
composer install -o -q -d ${REPO_DIR}
echo ""

if [[ -f ${REPO_DIR}/includes/vendor/bin/phpcs ]]; then
    echo "Start code quality test"
    ${REPO_DIR}/includes/vendor/bin/phpcs -n\
        --extensions=php \
        --standard=${REPO_DIR}/phpcs-gitlab.xml \
        --exclude=PSR1.Methods.CamelCapsMethodName \
        ${REPO_DIR}

    export codeQualityExitCode=$?

    echo "Show code quality information"
    ${REPO_DIR}/includes/vendor/bin/phpcs -n -q \
        --extensions=php \
        --standard=${REPO_DIR}/phpcs-gitlab.xml \
        --exclude=PSR1.Methods.CamelCapsMethodName \
        --report=info \
        ${REPO_DIR}

    echo "Save code quality report"
    ${REPO_DIR}/includes/vendor/bin/phpcs -n -q \
        --extensions=php \
        --standard=${REPO_DIR}/phpcs-gitlab.xml \
        --exclude=PSR1.Methods.CamelCapsMethodName \
        --report=info \
        --report-file=${REPO_DIR}/code-quality-report.txt \
        ${REPO_DIR}

    if [[ ${codeQualityExitCode} -ne 0 ]]; then
        exit 1
    fi
else
    echo "Wrong path for phpcs file!"
    exit 1
fi