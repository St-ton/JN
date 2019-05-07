<?php
/**
 * remove_product_tags
 *
 * @author mh
 * @created Tue, 07 May 2019 09:54:30 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190507095444
 */
class Migration_20190507095444 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove tag tables';

    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `ttag`');
        $this->execute('DROP TABLE IF EXISTS `ttagartikel`');
        $this->execute('DROP TABLE IF EXISTS `ttagkunde`');
        $this->execute('DROP TABLE IF EXISTS `ttagmapping`');
    }

    public function down()
    {
        $this->execute('
            CREATE TABLE `ttag` (
              `kTag` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kSprache` tinyint(4) unsigned NOT NULL,
              `cName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `cSeo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `nAktiv` tinyint(1) NOT NULL,
              PRIMARY KEY (`kTag`),
              KEY `cSeo` (`cSeo`),
              KEY `kSprache` (`kSprache`,`cName`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );

        $this->execute('
            CREATE TABLE `ttagartikel` (
              `kTag` int(10) unsigned NOT NULL,
              `kArtikel` int(10) unsigned NOT NULL,
              `nAnzahlTagging` int(10) unsigned NOT NULL,
              PRIMARY KEY (`kArtikel`,`kTag`),
              KEY `kTag` (`kTag`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );

        $this->execute('
            CREATE TABLE `ttagkunde` (
              `kTagKunde` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kTag` int(10) unsigned NOT NULL,
              `kKunde` int(10) unsigned NOT NULL,
              `cIP` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `dZeit` datetime NOT NULL,
              PRIMARY KEY (`kTagKunde`),
              KEY `kKunde` (`kKunde`),
              KEY `cIP` (`cIP`),
              KEY `dZeit` (`dZeit`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );

        $this->execute('
            CREATE TABLE `ttagmapping` (
              `kTagMapping` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kSprache` tinyint(4) unsigned NOT NULL,
              `cName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `cNameNeu` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`kTagMapping`),
              KEY `cName` (`kSprache`,`cName`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }
}
