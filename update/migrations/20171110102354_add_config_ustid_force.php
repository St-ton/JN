<?php
/**
 * add_config_ustid_force
 *
 * @author Clemens Rudolph
 * @created Fri, 10 Nov 2017 10:23:54 +0100
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
class Migration_20171110102354 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'Add config-setting to force remote UstID-check';

    public function up()
    {
        $this->setConfig(
              'shop_ustid_force_remote_check'                     // setting name
            , 'N'                                                 // default value of setting
            , CONF_KUNDEN                                         // section of setting (see: includes / defines_inc.php)
            , 'Kundenregistrierung nur mit MIAS-Best&auml;tigung' // shown setting-name in the backend
            , 'selectbox'                                         // setting-type
            , 500                                                 // order-position
            , (object) [
                  'cBeschreibung' => '"JA" stoppt die Kundenregistrierung auch wenn das Steueramt des jeweiligen Landes nicht erreichbar ist.'
                , 'inputOptions'  => [
                      'Y' => 'Ja'
                    , 'N' => 'Nein'
                ]
            ]
        );

        // --TO-CHECK--  maybe we should split this in two migrations ...
        //

        $this->setLocalization('ger', 'global', 'ustIDCaseTwo', 'UstID ist nicht im richtigen Format');
        $this->setLocalization('eng', 'global', 'ustIDCaseTwo', 'Your Sales Tax Identification number is not in the right format');

        $this->setLocalization('ger', 'global', 'ustIDCaseTwoB', 'Für Ihr Land sollte die UstID in diesem Format sein:');
        $this->setLocalization('eng', 'global', 'ustIDCaseTwoB', 'For your country the format should look like:');

        $this->setLocalization('ger', 'global', 'ustIDError100', 'Die UstID beginnt nicht mit zwei Großbuchstaben als Länderkennung!');
        $this->setLocalization('eng', 'global', 'ustIDError100', 'Your Sales Tax Identification Number did not start with 2 big letters! ');

        $this->setLocalization('ger', 'global', 'ustIDError110', 'Die UstID hat eine ungültige Länge!');
        $this->setLocalization('eng', 'global', 'ustIDError110', 'Your Sales Tax Identification Number has a wrong length!');

        $this->setLocalization('ger', 'global', 'ustIDError120', 'Die UstID entspricht nicht den Vorschriften Ihres Landes!<br>Der Fehler trat hier auf: ');
        $this->setLocalization('eng', 'global', 'ustIDError120', 'The Sales Tax Identification Number does not comply with the regulations of your country!<br>The error occurred here: ');

        $this->setLocalization('ger', 'global', 'ustIDError130', 'Es Existiert kein Land mit der Kennung ');
        $this->setLocalization('eng', 'global', 'ustIDError130', 'There is no country with the code ');

        $this->setLocalization('ger', 'global', 'ustIDCaseFive', 'Die UmsatzsteuerID ist laut MIAS-Prüfung ungültig.');
        $this->setLocalization('eng', 'global', 'ustIDCaseFive', 'Your Sales Tax Identification Number is invalid according to the VIES-System.');

        $this->setLocalization('ger', 'global', 'ustIDError200', 'Der MIAS-Dienst Ihres Landes ist nicht erreichbar bis ');
        $this->setLocalization('eng', 'global', 'ustIDError200', 'The VIES-service of your country not reachable till ');
    }

    public function down()
    {
        $this->removeConfig('shop_ustid_force_remote_check');

        // --TO-CHECK--  maybe we should split this in two migrations ...
        //
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
