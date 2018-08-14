<?php
/**
 * remove_favicon
 *
 * @author mh
 * @created Tue, 14 Aug 2018 08:54:15 +0200
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
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180814085415 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'remove favicon upload';

    public function up()
    {
        $this->execute("DELETE FROM ttemplateeinstellungen WHERE cTemplate='Evo' AND cName='favicon'");
    }

    public function down()
    {
        $this->execute(
            "INSERT INTO ttemplateeinstellungen (cTemplate, cSektion, cName, cWert)
                VALUES ('Evo', 'theme', 'favicon', 'favicon.ico')"
        );
    }
}
