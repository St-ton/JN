#!/bin/bash

REPOSITORY_DIR='/shared/httpd/jtl5.jan/htdocs';
export APPLICATION_VERSION_STR=`cat ${REPOSITORY_DIR}/VERSION`; 
echo $APPLICATION_VERSION_STR;