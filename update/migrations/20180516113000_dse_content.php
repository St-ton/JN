<?php
/**
 * DSE
 *
 * @author fm
 * @created Wed, 16 May 2018 11:30:00 +0200
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
 */
class Migration_20180516113000 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute(
            "ALTER TABLE ttext 
                ADD COLUMN cDSEContentText TEXT DEFAULT '',
                ADD COLUMN cDSEContentHtml TEXT DEFAULT ''"
        );
        $this->execute(
            "ALTER TABLE temailvorlage 
                ADD COLUMN nDSE TINYINT(3) UNSIGNED NOT NULL DEFAULT 0"
        );
        $this->execute(
            "ALTER TABLE tpluginemailvorlage 
                ADD COLUMN nDSE TINYINT(3) UNSIGNED NOT NULL DEFAULT 0"
        );
        $this->execute(
            "ALTER TABLE temailvorlageoriginal 
                ADD COLUMN nDSE TINYINT(3) UNSIGNED NOT NULL DEFAULT 0"
        );
        $this->setLocalization('ger', 'global', 'dse', 'Datenschutzerklärung');
        $this->setLocalization('eng', 'global', 'dse', 'Data privacy policy');
    }

    public function down()
    {
        $this->dropColumn('ttext', 'cDSEContentText');
        $this->dropColumn('ttext', 'cDSEContentHtml');
        $this->dropColumn('temailvorlage', 'nDSE');
        $this->dropColumn('tpluginemailvorlage', 'nDSE');
        $this->dropColumn('temailvorlageoriginal', 'nDSE');
        $this->execute("DELETE FROM tsprachwerte WHERE cName = 'dse' AND kSprachsektion = 1");
    }
}
