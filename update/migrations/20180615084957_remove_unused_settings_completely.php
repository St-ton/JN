<?php
/**
 * remove unused settings completely
 *
 * @author Clemens Rudolph
 * @created Fri, 15 Jun 2018 08:49:57 +0200
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
class Migration_20180615084957 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'remove un-used settings completely';

    public function up()
    {
        $this->removeConfig('bilder_variationen_gross_skalieren');
        $this->removeConfig('bilder_variationen_skalieren');
        $this->removeConfig('bilder_variationen_mini_skalieren');

        $this->removeConfig('bilder_artikel_gross_skalieren');
        $this->removeConfig('bilder_artikel_normal_skalieren');
        $this->removeConfig('bilder_artikel_klein_skalieren');
        $this->removeConfig('bilder_artikel_mini_skalieren');

        $this->removeConfig('bilder_hersteller_klein_skalieren');
        $this->removeConfig('bilder_hersteller_normal_skalieren');

        $this->removeConfig('bilder_merkmal_normal_skalieren');
        $this->removeConfig('bilder_merkmal_klein_skalieren');
        $this->removeConfig('bilder_merkmalwert_normal_skalieren');
        $this->removeConfig('bilder_merkmalwert_klein_skalieren');

        $this->removeConfig('bilder_kategorien_skalieren');
    }

    public function down()
    {
        $this->setConfig(
            'bilder_variationen_gross_skalieren',  // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Variationsbilder Groß skalieren',     // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            127,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_variationen_skalieren',        // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Variationsbilder skalieren',          // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            130,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_variationen_mini_skalieren',   // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Variationsbilder Mini skalieren',     // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            142,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );

        $this->setConfig(
            'bilder_artikel_gross_skalieren',      // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Produktbilder Groß skalieren',        // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            149,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_artikel_normal_skalieren',     // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Produktbilder Normal skalieren',      // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            169,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_artikel_klein_skalieren',      // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Produktbilder Klein skalieren',       // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            189,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_artikel_mini_skalieren',       // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Produktbilder Mini skalieren',        // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            202,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );


        $this->setConfig(
            'bilder_hersteller_klein_skalieren',   // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Herstellerbilder Klein skalieren',    // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            229,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_hersteller_normal_skalieren',  // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Herstellerbilder Normal skalieren',   // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            209,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );


                                                   // $this->removeConfig('bilder_merkmalwert_klein_skalieren');

        $this->setConfig(
            'bilder_merkmal_normal_skalieren',     // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Merkmalbilder Normal skalieren',      // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            249,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_merkmal_klein_skalieren',      // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Merkmalbilder Klein skalieren',       // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            269,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_merkmalwert_normal_skalieren', // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Merkmalwertbilder Normal skalieren',  // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            289,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'bilder_merkmalwert_klein_skalieren',  // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Merkmalwertbilder Klein skalieren',   // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            309,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );


        $this->setConfig(
            'bilder_kategorien_skalieren',         // (teinstellungenconf.cWertName) setting name
            'Y',                                   // (teinstellungenconf.cConf) default value of setting
            CONF_BILDER,                           // (teinstellungenconf.kEinstellungenSektion) section of setting (see: includes/defines_inc.php)
            'Kategoriebilder skalieren',           // (teinstellungenconf.cName) caption of setting in the backend
            'selectbox',                           // (teinstellungenconf.cInputTyp) setting-type
            109,                                   // (teinstellungenconf.nSort) order-position
            (object) [
                'cBeschreibung' => '',             // (teinstellungenconf.cBeschreibung)
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );

    }

}
