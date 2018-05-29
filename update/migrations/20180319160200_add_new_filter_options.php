<?php
/**
 * Add boolean mode for fulltext search
 *
 * @author ms
 * @created Mon, 19 Mar 2018 16:02:00 +0100
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
class Migration_20180319160200 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds options for new filters';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute(
            "INSERT INTO tboxvorlage 
                  (kCustomID, eTyp, cName, cVerfuegbar, cTemplate) 
                VALUES ('0', 'tpl', 'Filter (Hersteller)', '2', 'box_filter_manufacturer.tpl'),
                       ('0', 'tpl', 'Filter (Suchspecial)', '2', 'box_filter_search_special.tpl'),
                       ('0', 'tpl', 'Filter (Kategorie)', '2', 'box_filter_category.tpl')"
        );

        $this->execute("UPDATE teinstellungenconf SET cName='Typ des Kategoriefilters' WHERE cWertName ='category_filter_type';");

        // Bewertungsfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='bewertungsfilter_benutzen') AND cWert='N';");

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='bewertungsfilter_benutzen'), 'Ja, im Contentbereich und der Navigationsbox', 'Y', 3);"
        );

        // Herstellerfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_herstellerfilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_herstellerfilter_benutzen') AND cWert='N';");

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_herstellerfilter_benutzen'), 'Ja, im Contentbereich', 'content', 1),
                       ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_herstellerfilter_benutzen'), 'Ja, in Navigationsbox', 'box', 2);"
        );

        // Suchspecials - besondere Produkte
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_suchspecialfilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_suchspecialfilter_benutzen') AND cWert='N';");

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_suchspecialfilter_benutzen'), 'Ja, im Contentbereich', 'content', 1),
                       ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_suchspecialfilter_benutzen'), 'Ja, in Navigationsbox', 'box', 2);
            "
        );

        // Kategoriefilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_kategoriefilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_kategoriefilter_benutzen') AND cWert='N';");

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_kategoriefilter_benutzen'), 'Ja, im Contentbereich', 'content', 1),
                       ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_kategoriefilter_benutzen'), 'Ja, in Navigationsbox', 'box', 2);
            "
        );

        // Tagfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_tagfilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_tagfilter_benutzen') AND cWert='N';");

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_tagfilter_benutzen'), 'Ja, im Contentbereich', 'content', 1),
                       ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_tagfilter_benutzen'), 'Ja, in Navigationsbox', 'box', 2);
            "
        );

        // Merkmalfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='merkmalfilter_verwenden') AND cWert='N';");

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='merkmalfilter_verwenden'), 'Ja, im Contentbereich und der Navigationsbox', 'Y', 3);
            "
        );

        // Preisspannenfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='preisspannenfilter_benutzen') AND cWert='N';");

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='preisspannenfilter_benutzen'), 'Ja, im Contentbereich und der Navigationsbox', 'Y', 3);
            "
        );
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute(
            "DELETE FROM tboxvorlage WHERE cTemplate='box_filter_manufacturer.tpl' || cTemplate='box_filter_search_special.tpl' || cTemplate='box_filter_category.tpl';"
        );
        $this->execute(
            "DELETE FROM tboxen WHERE cTitel='Filter (Hersteller)' || cTitel='Filter (Suchspecial)' || cTitel='Filter (Kategorie)';"
        );

        // Bewertungsfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=3 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='bewertungsfilter_benutzen') AND cWert='N';"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte  
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='bewertungsfilter_benutzen') AND cWert='Y';"
        );

        // Herstellerfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_herstellerfilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_herstellerfilter_benutzen') AND cWert='N';");

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_herstellerfilter_benutzen') AND (cWert='box' OR cWert='content');"
        );

        // Suchspecials - besondere Produkte
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_suchspecialfilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_suchspecialfilter_benutzen') AND cWert='N';");

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_suchspecialfilter_benutzen') AND (cWert='box' OR cWert='content');"
        );

        // Kategoriefilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_kategoriefilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_kategoriefilter_benutzen') AND cWert='N';");

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_kategoriefilter_benutzen') AND (cWert='box' OR cWert='content');"
        );

        // Tagfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_tagfilter_benutzen') AND cWert='Y';");

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_tagfilter_benutzen') AND cWert='N';");

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='allgemein_tagfilter_benutzen') AND (cWert='box' OR cWert='content');"
        );

        // Merkmalfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=3 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='merkmalfilter_verwenden') AND cWert='N';");

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='merkmalfilter_verwenden') AND cWert='Y';"
        );

        // Preisspannenfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=3 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='preisspannenfilter_benutzen') AND cWert='N';");

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='preisspannenfilter_benutzen') AND cWert='Y';"
        );

    }
}