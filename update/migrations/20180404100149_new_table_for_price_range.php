<?php
/**
 * New table for price range
 *
 * @author fp
 * @created Wed, 04 Apr 2018 10:01:49 +0200
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
class Migration_20180404100149 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'New table for price range';

    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tpricerange` (
                `kPriceRange`     INT(11)    UNSIGNED NOT NULL AUTO_INCREMENT,
                `kArtikel`        INT(11)    UNSIGNED NOT NULL,
                `kKundengruppe`   INT(11)    UNSIGNED NOT NULL,
                `kKunde`          INT(11)    UNSIGNED     NULL,
                `nRangeType`      TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
                `fVKNettoMin`     DOUBLE              NOT NULL DEFAULT 0,
                `fVKNettoMax`     DOUBLE              NOT NULL DEFAULT 0,
                `nLagerAnzahlMax` DOUBLE                  NULL,
                `dStart`          DATE                    NULL,
                `dEnde`           DATE                    NULL,
                PRIMARY KEY (`kPriceRange`),
                UNIQUE INDEX `tpricerange_uq` (`kArtikel` ASC, `kKundengruppe` ASC, `kKunde` ASC, `nRangeType` ASC)
            ) ENGINE = InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE `tpricerange`");
    }
}
