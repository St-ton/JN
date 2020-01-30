<?php

/**
 * Menu setting changes
 *
 * @author mh
 * @created Thu, 23 Jan 2020 12:23:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200130101300
 */
class Migration_20200130101300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Menu setting changes';

    protected $settingsItemQuestion   = [
        'configgroup_5_product_question',
        'artikeldetails_fragezumprodukt_anzeigen',
        'artikeldetails_fragezumprodukt_email',
        'produktfrage_abfragen_anrede',
        'produktfrage_abfragen_vorname',
        'produktfrage_abfragen_nachname',
        'produktfrage_abfragen_firma',
        'produktfrage_abfragen_tel',
        'produktfrage_abfragen_fax',
        'produktfrage_abfragen_mobil',
        'produktfrage_kopiekunde',
        'produktfrage_sperre_minuten',
        'produktfrage_abfragen_captcha'
    ];
    protected $settingsItemAvailability = [
        'configgroup_5_product_available',
        'benachrichtigung_nutzen',
        'benachrichtigung_abfragen_vorname',
        'benachrichtigung_abfragen_nachname',
        'benachrichtigung_sperre_minuten',
        'benachrichtigung_abfragen_captcha',
        'benachrichtigung_min_lagernd'
    ];

    /**
     * @return mixed|void
     */
    public function up()
    {
        $this->execute(
            "UPDATE teinstellungenconf
                SET kEinstellungenSektion = " . \CONF_FRAGE_ZUM_PRODUKT . "
                WHERE cWertName IN ('" . implode("','", $this->settingsItemQuestion) . "')"
        );
        $this->execute(
            "UPDATE teinstellungen
                SET kEinstellungenSektion = " . \CONF_FRAGE_ZUM_PRODUKT . "
                WHERE cName IN ('" . implode("','", $this->settingsItemQuestion) . "')"
        );
        $this->execute(
            "INSERT INTO `teinstellungensektion` (`kEinstellungenSektion`, `cName`, `kAdminmenueGruppe`, `nSort`, `cRecht`)
                VALUES (" . \CONF_FRAGE_ZUM_PRODUKT . ", 'Frage zum Produkt', 0, 0, 'SETTINGS_ARTICLEDETAILS_VIEW')"
        );

        $this->execute(
            "UPDATE teinstellungenconf
                SET kEinstellungenSektion = " . \CONF_VERFUEGBARKEITSBENACHRICHTIGUNG . "
                WHERE cWertName IN ('" . implode("','", $this->settingsItemAvailability) . "')"
        );
        $this->execute(
            "UPDATE teinstellungen
                SET kEinstellungenSektion = " . \CONF_VERFUEGBARKEITSBENACHRICHTIGUNG . "
                WHERE cName IN ('" . implode("','", $this->settingsItemAvailability) . "')"
        );
        $this->execute(
            "INSERT INTO `teinstellungensektion` (`kEinstellungenSektion`, `cName`, `kAdminmenueGruppe`, `nSort`, `cRecht`)
                VALUES (" . \CONF_VERFUEGBARKEITSBENACHRICHTIGUNG . ", 'Verfuegbarkeitsbenachrichtigung', 0, 0, 'SETTINGS_ARTICLEDETAILS_VIEW')"
        );
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->execute(
            "UPDATE teinstellungenconf
                SET kEinstellungenSektion = " . \CONF_ARTIKELDETAILS . "
                WHERE cWertName IN ('" . implode("','", $this->settingsItemQuestion) . "')"
        );
        $this->execute(
            "UPDATE teinstellungen
                SET kEinstellungenSektion = " . \CONF_ARTIKELDETAILS . "
                WHERE cName IN ('" . implode("','", $this->settingsItemQuestion) . "')"
        );
        $this->execute(
            'DELETE FROM `teinstellungensektion` WHERE kEinstellungenSektion=' .\CONF_FRAGE_ZUM_PRODUKT
        );

        $this->execute(
            "UPDATE teinstellungenconf
                SET kEinstellungenSektion = " . \CONF_ARTIKELDETAILS . "
                WHERE cWertName IN ('" . implode("','", $this->settingsItemAvailability) . "')"
        );
        $this->execute(
            "UPDATE teinstellungen
                SET kEinstellungenSektion = " . \CONF_ARTIKELDETAILS . "
                WHERE cName IN ('" . implode("','", $this->settingsItemAvailability) . "')"
        );
        $this->execute(
            'DELETE FROM `teinstellungensektion` WHERE kEinstellungenSektion=' . \CONF_VERFUEGBARKEITSBENACHRICHTIGUNG
        );
    }
}
