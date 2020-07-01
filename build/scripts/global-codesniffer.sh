#!/usr/bin/env bash
export REPO_DIR=$1

echo "Start code quality test"
phpcs -n \
    --extensions=php \
    --standard=${REPO_DIR}/phpcs-gitlab.xml \
    --exclude=PSR1.Methods.CamelCapsMethodName \
    "${REPO_DIR}"

export codeQualityExitCode=$?

#echo "Show code quality information"
#phpcs -n -q \
#    --extensions=php \
#    --standard=${REPO_DIR}/phpcs-gitlab.xml \
#    --exclude=PSR1.Methods.CamelCapsMethodName \
#    --report=info \
#    "${REPO_DIR}"

echo "Save code quality report"
phpcs -n -q \
    --extensions=php \
    --standard=${REPO_DIR}/phpcs-gitlab.xml \
    --exclude=PSR1.Methods.CamelCapsMethodName \
    --report=info \
    --report-file=code-quality-report.txt \
    "${REPO_DIR}"

if [[ ${codeQualityExitCode} -ne 0 ]]; then
    exit 1
fi
