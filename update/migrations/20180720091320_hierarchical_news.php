<?php
/**
 * hierarchical_news
 *
 * @author mh
 * @created Fri, 20 Jul 2018 09:13:20 +0200
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
class Migration_20180720091320 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Hierarchical news';

    public function up()
    {
        $this->execute(
            "ALTER TABLE `tnewskategorie`
                ADD COLUMN `kParent` INT(10) NOT NULL DEFAULT 0 AFTER `cBeschreibung`"
        );
        $this->execute("ALTER TABLE tnewskategorie ADD INDEX kParent (kParent)");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tnewskategorie`DROP COLUMN `kParent`");
        $this->execute("ALTER TABLE tnewskategorie DROP INDEX kParent");
    }

}
