<?php
/**
 * new table tgratisgeschenk
 *
 * @author mh
 * @created Wed, 25 Jul 2018 09:02:28 +0200
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
class Migration_20180725090228 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'New table tgratisgeschenk';

    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tgratisgeschenk` (
                `kGratisGeschenk` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `kArtikel`        INT(10) UNSIGNED NOT NULL,
                `kWarenkorb`      INT(10) UNSIGNED NOT NULL,
                `nAnzahl`         INT(10) UNSIGNED NOT NULL,
                 PRIMARY KEY (`kGratisGeschenk`),        
                 INDEX `kWarenkorb` (`kWarenkorb`)
            ) ENGINE = InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE `tgratisgeschenk`");
    }
}