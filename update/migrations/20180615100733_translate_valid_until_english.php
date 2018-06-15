<?php
/**
 * translate validUntil english
 *
 * @author mh
 * @created Fri, 15 Jun 2018 10:07:33 +0200
 */

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180615100733 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Translate validUntil global english';

    public function up()
    {
        $this->execute("UPDATE tsprachwerte SET cWert='valid until' WHERE cName='validUntil' AND kSprachISO=2");
    }

    public function down()
    {
        $this->execute("UPDATE tsprachwerte SET cWert='G&uuml;ltig bis' WHERE cName='validUntil' AND kSprachISO=2");
    }
}