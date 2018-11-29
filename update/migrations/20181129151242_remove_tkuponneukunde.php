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
    protected $description = 'Remove tkuponneukunde';

    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tkuponneukunde`');
    }

    public function down()
    {
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
    }
}