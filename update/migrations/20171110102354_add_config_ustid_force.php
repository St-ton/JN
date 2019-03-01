<?php
/**
 * add_config_ustid_force
 *
 * @author Clemens Rudolph
 * @created Fri, 10 Nov 2017 10:23:54 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

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
class Migration_20171110102354 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'Add vars and settings for the UstID-check via VIES';

    public function up()
    {
        // add config-setting to force remote UstID-check
        $this->setConfig(
            'shop_ustid_force_remote_check',                     // setting name
            'Y',                                                 // default value of setting
            CONF_KUNDEN,                                         // section of setting (see: includes / defines_inc.php)
            'Kundenregistrierung nur mit MIAS-Best&auml;tigung', // caption of setting in the backend
            'selectbox',                                         // setting-type
            430,                                                 // order-position
            (object)[
                'cBeschreibung' => '"JA" stoppt die Kundenregistrierung in jedem Fall (auch wenn das Steueramt des jeweiligen Landes nicht erreichbar ist).',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );

        // modify config-setting "activate check"
        $this->removeConfig('shop_ustid_bzstpruefung');
        $this->setConfig(
            'shop_ustid_bzstpruefung',                                // setting name
            'N',                                                      // default value of setting
            CONF_KUNDEN,                                              // section of setting (see: includes / defines_inc.php)
            'UStID-Nummer Pr&uuml;fung durch MIAS-System aktivieren', // caption of setting in the backend
            'selectbox',                                              // setting-type
            420,                                                      // order-position
            (object)[
                'cBeschreibung' => '&Uuml;berpr&uuml;fung der UstID-Nummer durch das MIAS-System der Europ&auml;ischen Kommission. Dazu ist die Angabe der eigenen USt-ID im Feld dar&uuml;ber n&ouml;tig.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );

        $this->setLocalization('ger', 'global', 'ustIDError100', 'Die UstID-Nummer beginnt nicht mit zwei Großbuchstaben als Länderkennung!');
        $this->setLocalization('eng', 'global', 'ustIDError100', 'Your Sales Tax Identification Number did not start with 2 big letters! ');

        $this->setLocalization('ger', 'global', 'ustIDError110', 'Die UstID-Nummer hat eine ungültige Länge!');
        $this->setLocalization('eng', 'global', 'ustIDError110', 'Your Sales Tax Identification Number has a wrong length!');

        $this->setLocalization('ger', 'global', 'ustIDError120', 'Die UstID-Nummer entspricht nicht den Vorschriften Ihres Landes!<br>Der Fehler trat hier auf: ');
        $this->setLocalization('eng', 'global', 'ustIDError120', 'The Sales Tax Identification Number does not comply with the regulations of your country!<br>The error occurred here: ');

        $this->setLocalization('ger', 'global', 'ustIDError130', 'Es existiert kein Land mit der Kennung ');
        $this->setLocalization('eng', 'global', 'ustIDError130', 'There is no country with the code ');

        $this->setLocalization('ger', 'global', 'ustIDCaseFive', 'Die UstID-Nummer ist laut MIAS-Prüfung ungültig.');
        $this->setLocalization('eng', 'global', 'ustIDCaseFive', 'Your Sales Tax Identification Number is invalid according to the VIES-System.');

        $this->setLocalization('ger', 'global', 'ustIDError200', 'Der MIAS-Dienst Ihres Landes ist nicht erreichbar bis ');
        $this->setLocalization('eng', 'global', 'ustIDError200', 'The VIES-service of your country is not reachable till ');
    }

    public function down()
    {
        $this->removeConfig('shop_ustid_force_remote_check');

        // modify config-setting "activate check"
        $this->removeConfig('shop_ustid_bzstpruefung');
        $this->setConfig(
            'shop_ustid_bzstpruefung',                                               // setting name
            'N',                                                                     // default value of setting
            CONF_KUNDEN,                                                             // section of setting (see: includes / defines_inc.php)
            'USt-ID Pr&uuml;fung des Bundeszentralamts f&uuml;r Steuern aktivieren', // caption of setting in the backend
            'selectbox',                                                             // setting-type
            430,                                                                     // order-position
            (object)[
                'cBeschreibung' => 'Soll die USt-ID Nummer &uuml;berpr&uuml;ft werden? Dazu ist die Angabe der eigenen USt-ID im Feld dar&uuml;ber n&ouml;tig.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );

        $this->setLocalization('ger', 'global', 'ustIDCaseTwo', 'UstID ist nicht im richtigen Format');
        $this->setLocalization('eng', 'global', 'ustIDCaseTwo', 'Your Sales Tax Identification number is not in the right format');

        $this->setLocalization('ger', 'global', 'ustIDCaseTwoB', 'Für Ihr Land sollte die UstID in diesem Format sein:');
        $this->setLocalization('eng', 'global', 'ustIDCaseTwoB', 'For your country the format should look like:');

        $this->removeLocalization('ustIDError100');
        $this->removeLocalization('ustIDError110');
        $this->removeLocalization('ustIDError120');
        $this->removeLocalization('ustIDError130');

        $this->setLocalization('ger', 'global', 'ustIDCaseFive', 'Die UmsatzsteuerID ist durch Prüfung ungültig');
        $this->setLocalization('eng', 'global', 'ustIDCaseFive', 'Your Sales Tax Identification number is invalid');

        $this->removeLocalization('ustIDError200');
    }
}
