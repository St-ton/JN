<?php
/**
 * Remove survey
 *
 * @author mh
 * @created Fri, 13 Dec 2019 08:33:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191213083300
 */
class Migration_20191213083300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove survey';

    public function up()
    {
        $this->removeConfig('configgroup_115_poll');
        $this->removeConfig('umfrage_nutzen');
        $this->removeConfig('umfrage_einloggen');
        $this->removeConfig('umfrage_box_anzahl');

        $this->execute('DROP TABLE IF EXISTS `tumfrage`');
        $this->execute('DROP TABLE IF EXISTS `tumfragedurchfuehrung`');
        $this->execute('DROP TABLE IF EXISTS `tumfragedurchfuehrungantwort`');
        $this->execute('DROP TABLE IF EXISTS `tumfragefrage`');
        $this->execute('DROP TABLE IF EXISTS `tumfragefrageantwort`');
        $this->execute('DROP TABLE IF EXISTS `tumfragematrixoption`');

        $this->getDB()->delete('tseo', 'cKey', 'kUmfrage');
    }

    public function down()
    {
        $this->setConfig(
            'configgroup_115_poll',
            'Umfragesystem Einstellungen',
            115,
            'Umfragesystem Einstellungen',
            null,
            10,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'umfrage_nutzen',
            'Y',
            115,
            'Umfragesystem benutzen',
            'selectbox',
            20,
            (object)[
                'cBeschreibung' => 'Wollen Sie das Umfragesystem im Shop nutzen?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'umfrage_einloggen',
            'y',
            30,
            'Muss man eingeloggt sein',
            'selectbox',
            30,
            (object)[
                'cBeschreibung' => 'Sollen sich Besucher vorher im Shop einloggen damit sie an einer Umfrage teilnehmen k?nnen?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'umfrage_box_anzahl',
            'N',
            115,
            'Anzahl Umfragen in der Box',
            'number',
            40,
            (object)[
                'cBeschreibung' => 'Wieviele Umfragen sollen in der Box angezeigt werden? Greift nur wenn die Option Box eingeschaltet ist.',
            ]
        );

        $this->execute('
            CREATE TABLE `tumfrage` (
              `kUmfrage`      int(10) unsigned    NOT NULL AUTO_INCREMENT,
              `kSprache`      int(10) unsigned    NOT NULL,
              `kKupon`        int(10) unsigned             DEFAULT NULL,
              `cKundengruppe` varchar(255)        NOT NULL,
              `cName`         varchar(255)        NOT NULL,
              `cSeo`          varchar(255)        NOT NULL,
              `cBeschreibung` text                NOT NULL,
              `fGuthaben`     double unsigned              DEFAULT NULL,
              `nBonuspunkte`  int(10) unsigned             DEFAULT NULL,
              `nAktiv`        tinyint(3) unsigned NOT NULL,
              `dGueltigVon`   datetime            NOT NULL,
              `dGueltigBis`   datetime                     DEFAULT NULL,
              `dErstellt`     datetime            NOT NULL,
              PRIMARY KEY (`kUmfrage`),
              KEY `kSprache` (`kSprache`, `nAktiv`, `kUmfrage`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
        $this->execute('
            CREATE TABLE `tumfragedurchfuehrung` (
              `kUmfrageDurchfuehrung` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kKunde`                int(10) unsigned NOT NULL,
              `kUmfrage`              int(10) unsigned NOT NULL,
              `cIP`                   varchar(255)     NOT NULL,
              `dDurchgefuehrt`        datetime         NOT NULL,
              PRIMARY KEY (`kUmfrageDurchfuehrung`),
              KEY `kUmfrage` (`kUmfrage`, `kKunde`),
              KEY `cIP` (`cIP`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
        $this->execute('
            CREATE TABLE `tumfragedurchfuehrungantwort` (
              `kUmfrageDurchfuehrungAntwort` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kUmfrageDurchfuehrung`        int(10) unsigned NOT NULL,
              `kUmfrageFrage`                int(10) unsigned NOT NULL,
              `kUmfrageFrageAntwort`         int(10) unsigned NOT NULL,
              `kUmfrageMatrixOption`         int(10) unsigned NOT NULL,
              `cText`                        text             NOT NULL,
              PRIMARY KEY (`kUmfrageDurchfuehrungAntwort`),
              KEY `kUmfrageFrageAntwort` (`kUmfrageFrageAntwort`, `kUmfrageFrage`),
              KEY `kUmfrageFrage` (`kUmfrageFrage`, `kUmfrageMatrixOption`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
        $this->execute('
            CREATE TABLE `tumfragefrage` (
              `kUmfrageFrage` int(10) unsigned    NOT NULL AUTO_INCREMENT,
              `kUmfrage`      int(10) unsigned    NOT NULL,
              `cTyp`          varchar(255)        NOT NULL,
              `cName`         varchar(255)        NOT NULL,
              `cBeschreibung` text                NOT NULL,
              `nSort`         tinyint(3) unsigned NOT NULL,
              `nFreifeld`     tinyint(3) unsigned NOT NULL,
              `nNotwendig`    tinyint(3) unsigned NOT NULL,
              PRIMARY KEY (`kUmfrageFrage`),
              KEY `kUmfrage` (`kUmfrage`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
        $this->execute('
            CREATE TABLE `tumfragefrageantwort` (
              `kUmfrageFrageAntwort` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kUmfrageFrage`        int(10) unsigned NOT NULL,
              `cName`                varchar(255)     NOT NULL,
              `nSort`                int(10) unsigned NOT NULL,
              PRIMARY KEY (`kUmfrageFrageAntwort`),
              KEY `kUmfrageFrage` (`kUmfrageFrage`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
        $this->execute('
            CREATE TABLE `tumfragematrixoption` (
              `kUmfrageMatrixOption` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kUmfrageFrage`        int(10) unsigned NOT NULL,
              `cName`                varchar(255)     NOT NULL,
              `nSort`                int(10) unsigned NOT NULL,
              PRIMARY KEY (`kUmfrageMatrixOption`),
              KEY `kUmfrageFrage` (`kUmfrageFrage`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
    }
}
