<?php
/**
 * remove rma special page
 *
 * @author fm
 * @created Wed, 09 May 2018 15:21:00 +0200
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
class Migration_20180509152100 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'removes rma special page';

    public function up()
    {
        $this->execute("DELETE FROM tspezialseite WHERE cDateiname = 'rma.php'");
    }

    public function down()
    {
        $this->execute("INSERT INTO tspezialseite VALUES (23,0,'Warenrücksendung','rma.php',28,28)");
    }
}
