<?php
/**
 * add_table_tbestellungkampagne
 *
 * @author mh
 * @created Wed, 11 Jul 2018 14:12:35 +0200
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
class Migration_20180711141235 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Add table tbestellungkampagne';

    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tbestellungkampagne` (
                `kBestellungKampagne` INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
                `kBestellung`         INT(11)      UNSIGNED NOT NULL,
                `kKampagne`           INT(11)      UNSIGNED NOT NULL,
                `cParameter`          VARCHAR(255) NOT NULL,
                `cWert`               VARCHAR(255) NOT NULL,
                PRIMARY KEY (`kBestellungKampagne`),
                UNIQUE INDEX `tbestellungkampagne_uq` (`kBestellung` ASC, `kKampagne` ASC)
            ) ENGINE = InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE `tbestellungkampagne`");
    }
}
