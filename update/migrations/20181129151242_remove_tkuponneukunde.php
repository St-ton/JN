<?php
/**
 * remove_tkuponneukunde
 *
 * @author mh
 * @created Thu, 29 Nov 2018 15:12:42 +0100
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
class Migration_20181129151242 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Remove tkuponneukunde, add tkuponflag';

    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tkuponneukunde`');

        $this->execute('CREATE TABLE `tkuponflag` (
                          `kKuponFlag` int(10) unsigned NOT NULL AUTO_INCREMENT,
                          `cEmailHash` varchar(255) NOT NULL,
                          `cKuponTyp` varchar(255) NOT NULL,
                          `dErstellt` datetime NOT NULL,
                          PRIMARY KEY (`kKuponFlag`),
                          KEY cEmailHash_cKuponTyp (`cEmailHash`, `cKuponTyp`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        $this->execute('ALTER TABLE `tkuponbestellung` CHANGE COLUMN `cKuponTyp` `cKuponTyp` VARCHAR(255) NOT NULL');


        $this->execute('ALTER TABLE `tkuponkunde`
                          DROP KEY `kKupon`,
                          DROP KEY `kKunde`,
                          ADD UNIQUE KEY `kKupon_cMail` (`kKupon`, `cMail`)');

        $this->setLocalization('ger', 'global', 'couponErr6', 'Fehler: Maximale Verwendungen für den Kupon erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'Error: Maximum usage reached for this coupon.');
    }

    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tkuponflag`');

        $this->execute("CREATE TABLE `tkuponneukunde` (
                          `kKuponNeukunde` int(10) unsigned NOT NULL AUTO_INCREMENT,
                          `kKupon` int(10) unsigned NOT NULL,
                          `cEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                          `cDatenHash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                          `dErstellt` datetime NOT NULL,
                          `cVerwendet` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                          PRIMARY KEY (`kKuponNeukunde`),
                          KEY `cEmail` (`cEmail`,`cDatenHash`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

        $this->execute("ALTER TABLE `tkuponbestellung` CHANGE COLUMN `cKuponTyp` `cKuponTyp` enum('prozent', 'festpreis', 'versand', 'neukunden') COLLATE utf8_unicode_ci DEFAULT NULL");

        $this->execute('ALTER TABLE `tkuponkunde`
                          DROP KEY `kKupon_cMail`,
                          ADD KEY `kKupon` (`kKupon`),
                          ADD KEY `kKunde` (`kKunde`)');

        $this->setLocalization('ger', 'global', 'couponErr6', 'Fehler: Maximale Verwendungen erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'Error: Maximum usage reached');
    }
}
