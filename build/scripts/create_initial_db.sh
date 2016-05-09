#!/bin/bash

INITIALSHOPDB=$1;
DBUSER=$2;
DBPASS=$3;
TMPDB="tmp_initial";

if [ $# -lt 3 ]; then
   echo "Usage: $0 <database> <database-user> <database-password>"
   exit 1
fi

source ../version.conf
export SHOPVERSION_MAJOR SHOPVERSION_MINOR SHOPVERSION_BUILD

TARGETFILE=../sql/$SHOPVERSION_MAJOR.$SHOPVERSION_MINOR.sql

DUMPCMD="mysqldump -u $DBUSER -p$DBPASS --default-character-set=latin1 --skip-add-locks  --skip-add-drop-table --skip-comments"
MYSQLCMD="mysql -u $DBUSER -p$DBPASS"
DBVERSION="$SHOPVERSION_MAJOR$SHOPVERSION_MINOR"; 

$DUMPCMD $INITIALSHOPDB > initialshop.sql

$MYSQLCMD -e "DROP DATABASE $TMPDB; CREATE DATABASE $TMPDB DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;";

$MYSQLCMD $TMPDB < initialshop.sql

$MYSQLCMD $TMPDB -e "TRUNCATE TABLE tsynclogin; TRUNCATE TABLE tadminlogin;TRUNCATE TABLE tbesucher; TRUNCATE TABLE tbesucherarchiv; TRUNCATE TABLE tbesuchteseiten; TRUNCATE TABLE tbrocken; TRUNCATE TABLE tfirma; TRUNCATE TABLE tsprachlog; TRUNCATE TABLE tredirect; TRUNCATE TABLE tredirectreferer;TRUNCATE TABLE tjtllog;TRUNCATE TABLE tsuchanfragencache;TRUNCATE TABLE tsuchanfrageerfolglos;TRUNCATE TABLE ttrustedshopskundenbewertung;TRUNCATE TABLE teinheit;" > initialshopdb.log;
$MYSQLCMD $TMPDB -e "UPDATE tversion SET nVersion=$DBVERSION; UPDATE tbesucherzaehler SET nZaehler=0; UPDATE tnummern SET nNummer = 10000 WHERE nArt=1; UPDATE tnummern SET dAktualisiert='0000-00-00 00:00:00';UPDATE tmigration SET dExecuted=NOW();";

rm $TARGETFILE;

echo "Export schema to  $TARGETFILE"

$DUMPCMD $TMPDB > $TARGETFILE