#!/bin/bash

FROM_REFSPEC=$1
FROM_ZIPFILE=$2
TO_REFSPEC=$3
TO_ZIPFILE=$4
TMP_PATH=/tmp/shoppatchbuild

if [ $# -lt 4 ]; then
   echo "Usage: $0 <from-tag> <from-zipfile> <to-tag> <to-zipfile>"
   exit 1
fi

if [ ! -f $FROM_ZIPFILE ]; then
   echo "$FROM_ZIPFILE does not exist"
   exit 1
fi

if [ ! -f $TO_ZIPFILE ]; then
   echo "$TO_ZIPFILE does not exist"
   exit 1
fi

rm -rf $TMP_PATH

mkdir -p $TMP_PATH/src
unzip -qq $FROM_ZIPFILE -d $TMP_PATH/src

mkdir -p $TMP_PATH/target
unzip -qq $TO_ZIPFILE -d $TMP_PATH/target

git fetch

mkdir -p $TMP_PATH/patch
DIFF_LIST=$(git diff --name-only $FROM_REFSPEC $TO_REFSPEC)

pushd $TMP_PATH/target
for i in $DIFF_LIST; do
    rsync -Rv $i ../patch/
done
popd

echo "includes/defines_inc.php and admin/includes/shopmd5files/ will be overwritten later" 

cp $TMP_PATH/target/includes/defines_inc.php $TMP_PATH/patch/includes/defines_inc.php

cp -R $TMP_PATH/patch/* $TMP_PATH/src/

MD5_DB_FILENAME=$(ls $TMP_PATH/src/admin/includes/shopmd5files/*.csv)
echo "Generate MD5 database in ${MD5_DB_FILENAME}..."
pushd $TMP_PATH/src
#find . -type f ! -name robots.txt ! -name rss.xml ! -name shopinfo.xml ! -name .htaccess ! -name \*.md ! -samefile includes/defines.php ! -samefile includes/defines_inc.php ! -samefile includes/config.JTL-Shop.ini.initial.php ! -samefile .gitignore | sed -e 's@^\./@@g' | grep -v -E '.git|.settings|.project|admin/gfx|admin/includes/emailpdfs|admin/includes/shopmd5files|admin/templates/gfx|admin/templates_c/|build|bilder|downloads|export|gfx|includes/plugins|install|jtllogs|mediafiles|templates|templates_c|uploads|phpunit' | xargs md5sum | awk '{ print $2";"$1; }' | sort > $MD5_DB_FILENAME
find . -type f ! -name robots.txt ! -name rss.xml ! -name shopinfo.xml ! -name .htaccess ! -samefile includes/defines.php ! -samefile includes/defines_inc.php ! -samefile includes/config.JTL-Shop.ini.initial.php -printf '%P\n' | grep -v -E '.git/|/.gitkeep|admin/gfx|admin/includes/emailpdfs|admin/includes/shopmd5files|admin/templates/gfx|admin/templates_c/|bilder/|downloads/|gfx/|includes/plugins|install/|jtllogs/|mediafiles/|templates/|templates_c/|uploads/|export/|shopinfo.xml|sitemap_index.xml' | xargs md5sum | awk '{ print $2";"$1; }' | sort > $MD5_DB_FILENAME
popd

mkdir -p $TMP_PATH/patch/admin/includes/shopmd5files
cp $MD5_DB_FILENAME $TMP_PATH/patch/admin/includes/shopmd5files/

cp $MD5_DB_FILENAME $TMP_PATH/src/admin/includes/shopmd5files/

FROM_FILENAME=`echo $FROM_REFSPEC | sed -e "s/\//_/g"`
TO_FILENAME=`echo $TO_REFSPEC | sed -e "s/\//_/g"`

cd $TMP_PATH/patch
zip -r $TMP_PATH/patch_${FROM_FILENAME}_to_${TO_FILENAME}.zip *
cd $TMP_PATH/src
zip -r $TMP_PATH/jtlshop_${TO_FILENAME}_patched_from_${FROM_FILENAME}.zip * .htaccess
