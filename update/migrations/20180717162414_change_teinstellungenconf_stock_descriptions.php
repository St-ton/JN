<?php
/**
 * change_teinstellungenconf_stock_descriptions
 *
 * @author mh
 * @created Tue, 17 Jul 2018 16:24:14 +0200
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
class Migration_20180717162414 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Change teinstellungenconf stock descriptions';

    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='So wird der Lagerbestand eines Artikels angezeigt. (Einzelansicht)' WHERE kEinstellungenConf = 110");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='So wird der Lagerbestand eines Artikels angezeigt. (z.B. in Suche oder Kategorieübersicht)' WHERE kEinstellungenConf = 118");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='Wenn der Lagerbestand kleiner 0 sein darf (Überverkauf möglich), soll Ampel grün sein?' WHERE kEinstellungenConf = 200");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='Ab diesem Lagerbestand steht die Lagerampel auf grün. Zwischen diesem Wert und Lagerbestandsampel rot steht die Ampel auf gelb.' WHERE kEinstellungenConf = 112");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='Bis zu diesem Lagerbestand steht die Lagerampel auf rot. Zwischen diesem Wert und Lagerbestandsampel grün steht die Ampel auf gelb.' WHERE kEinstellungenConf = 111");
    }

    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='So wird der Lagerbestand eines Artikels angezeigt' WHERE kEinstellungenConf = 110");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='So wird der Lagerbestand eines Artikels angezeigt' WHERE kEinstellungenConf = 118");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='Wenn der Lagerbestand kleiner 0 sein darf, soll Ampel grün sein?' WHERE kEinstellungenConf = 200");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='Ab diesem Lagerbestand steht die Lagerampel auf grün. Zwischen diesem Wert und Lagerampel grün steht die Ampel auf gelb.' WHERE kEinstellungenConf = 112");
        $this->execute("UPDATE teinstellungenconf SET cBeschreibung='Bis zu diesem Lagerbestand steht die Lagerampel auf rot. Zwischen diesem Wert und Lagerampel grün steht die Ampel auf gelb.' WHERE kEinstellungenConf = 111");
    }

}