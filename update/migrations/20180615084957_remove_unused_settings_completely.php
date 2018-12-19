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
    protected $author       = 'Clemens Rudolph';
    protected $description  = 'remove unused settings completely';

    private $vSettingNameID = [
              'bilder_variationen_gross_skalieren'  // 1427
            , 'bilder_variationen_skalieren'        // 1428
            , 'bilder_variationen_mini_skalieren'   // 1429

            , 'bilder_artikel_gross_skalieren'      // 1430
            , 'bilder_artikel_normal_skalieren'     // 1431
            , 'bilder_artikel_klein_skalieren'      // 1432
            , 'bilder_artikel_mini_skalieren'       // 1433

            , 'bilder_hersteller_klein_skalieren'   // 1435
            , 'bilder_hersteller_normal_skalieren'  // 1434

            , 'bilder_merkmal_normal_skalieren'     // 1436
            , 'bilder_merkmal_klein_skalieren'      // 1437
            , 'bilder_merkmalwert_normal_skalieren' // 1438
            , 'bilder_merkmalwert_klein_skalieren'  // 1439

            , 'bilder_kategorien_skalieren'         // 1426
    ];


    public function up()
    {
        foreach ($this->vSettingNameID as $szSettingName) {
            $this->removeConfig($szSettingName);
        }
    }

    public function down()
    {
        // bilder_variationen_gross_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_variationen_gross_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1427","9","Variationsbilder Groß skalieren","","bilder_variationen_gross_skalieren","selectbox","","127","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1427","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1427","Nein","N","2")');

        // bilder_variationen_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_variationen_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1428","9","Variationsbilder skalieren","","bilder_variationen_skalieren","selectbox","","130","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1428","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1428","Nein","N","2")');

        // bilder_variationen_mini_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_variationen_mini_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1429","9","Variationsbilder Mini skalieren","","bilder_variationen_mini_skalieren","selectbox","","142","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1429","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1429","Nein","N","2")');

        // bilder_artikel_gross_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_artikel_gross_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1430","9","Produktbilder Groß skalieren","","bilder_artikel_gross_skalieren","selectbox","","149","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1430","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1430","Nein","N","2")');

        // bilder_artikel_normal_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_artikel_normal_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1431","9","Produktbilder Normal skalieren","","bilder_artikel_normal_skalieren","selectbox","","169","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1431","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1431","Nein","N","2")');

        // bilder_artikel_klein_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_artikel_klein_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1432","9","Produktbilder Klein skalieren","","bilder_artikel_klein_skalieren","selectbox","","189","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1432","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1432","Nein","N","2")');

        // bilder_artikel_mini_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_artikel_mini_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1433","9","Produktbilder Mini skalieren","","bilder_artikel_mini_skalieren","selectbox","","202","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1433","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1433","Nein","N","2")');

        // bilder_hersteller_klein_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_hersteller_klein_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1435","9","Herstellerbilder Klein skalieren","","bilder_hersteller_klein_skalieren","selectbox","","229","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1435","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1435","Nein","N","2")');

        // bilder_hersteller_normal_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_hersteller_normal_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1434","9","Herstellerbilder Normal skalieren","","bilder_hersteller_normal_skalieren","selectbox","","209","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1434","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1434","Nein","N","2")');

        // bilder_merkmal_normal_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_merkmal_normal_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1436","9","Merkmalbilder Normal skalieren","","bilder_merkmal_normal_skalieren","selectbox","","249","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1436","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1436","Nein","N","2")');

        // bilder_merkmal_klein_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_merkmal_klein_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1437","9","Merkmalbilder Klein skalieren","","bilder_merkmal_klein_skalieren","selectbox","","269","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1437","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1437","Nein","N","2")');

        // bilder_merkmalwert_normal_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_merkmalwert_normal_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1438","9","Merkmalwertbilder Normal skalieren","","bilder_merkmalwert_normal_skalieren","selectbox","","289","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1438","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1438","Nein","N","2")');

        // bilder_merkmalwert_klein_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_merkmalwert_klein_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1439","9","Merkmalwertbilder Klein skalieren","","bilder_merkmalwert_klein_skalieren","selectbox","","309","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1439","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1439","Nein","N","2")');

        // bilder_kategorien_skalieren
        $this->execute('INSERT INTO teinstellungen(kEinstellungenSektion,cName,cWert,cModulId) VALUES("9","bilder_kategorien_skalieren","","")');
        $this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf) VALUES("1426","9","Kategoriebilder skalieren","","bilder_kategorien_skalieren","selectbox","","109","1","0","Y")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1426","Ja","Y","1")');
        $this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1426","Nein","N","2")');
    }

}
