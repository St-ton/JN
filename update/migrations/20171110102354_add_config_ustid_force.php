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
              'shop_ustid_force_remote_check'      // setting name
            , 'N'                                  // default value of setting
            , CONF_KUNDEN                          // section of setting (see: includes/defines_inc.php)
            //, 'Abfrage bei MIAS-System erzwingen'  // shown setting-name in the backend
            , 'Kundenregistrierung nur mit MIAS-Bestätigung'  // shown setting-name in the backend
            , 'selectbox'                          // setting-type
            , 500                                  // order-position
            , (object) [
                'cBeschreibung' => '"JA" stoppt die Kundenregistrierung, wenn keine positive Best&auml;tigung vom MwSt-Informationsaustauschsystem (MIAS) der Europ&auml;ischen Kommission'
                                   .' erfragt werden konnte, oder das Steueramt im jeweiligen Land zur Zeit nicht erreichbar ist.'
                , 'inputOptions' => [
                      'Y' => 'Ja'
                    , 'N' => 'Nein'
                ]
            ]
        );

        // --TO-CHECK--  maybe we should split this in two migrations ...
        //
/*
 *        $this->setLocalization('ger', 'global', 'ustIDCaseTwo', '');
 *        $this->setLocalization('eng', 'global', 'ustIDCaseTwo', '');
 *
 *        $this->setLocalization('ger', 'global', 'ustIDCaseTwoB', '');
 *        $this->setLocalization('eng', 'global', 'ustIDCaseTwoB', '');
 *
 *        $this->setLocalization('ger', 'global', 'ustIDCaseFive', '');
 *        $this->setLocalization('eng', 'global', 'ustIDCaseFive', '');
 */

        $this->setLocalization('ger', 'global', 'ustIDCaseTwo', 'UstID ist nicht im richtigen Format');
        $this->setLocalization('eng', 'global', 'ustIDCaseTwo', 'Your Sales Tax Identification number is not in the right format');

        $this->setLocalization('ger', 'global', 'ustIDCaseTwoB', 'Für Ihr Land sollte die UstID in diesem Format sein:');
        $this->setLocalization('eng', 'global', 'ustIDCaseTwoB', 'For your country the format should look like:');


        $this->setLocalization('ger', 'global', 'ustIDError100', 'Die UstID beginnt nicht mit zwei Großbuchstaben als Länderkennung!');
        $this->setLocalization('ger', 'global', 'ustIDError110', 'Die UstID hat eine ungültige Länge!');
        $this->setLocalization('ger', 'global', 'ustIDError120', 'Die UstID entspricht nicht den Vorschriften des betreffenden Landes!');
        $this->setLocalization('ger', 'global', 'ustIDError130', 'Es Existiert kein Land mit der Kennung ');

        $this->setLocalization('ger', 'global', 'ustIDCaseFive', 'Die UmsatzsteuerID laut MIAS-Prüfung ungültig.');
        $this->setLocalization('eng', 'global', 'ustIDCaseFive', 'Your Sales Tax Identification number is invalid according to the VIES-System.');
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
    }
}
