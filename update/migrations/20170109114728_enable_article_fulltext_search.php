<?php
/**
 * Enable article fulltext search
 *
 * @author Falk Prüfer
 * @created Mon, 09 Jan 2017 11:47:28 +0100
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
class Migration_20170109114728 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Enable article fulltext search';

    public function up()
    {
        try {
            $ft_enabled = $this->execute(
                "ALTER table tartikel ADD FULLTEXT KEY idx_tartikel_fulltext (cName, cSeo, cSuchbegriffe, cArtNr, cKurzBeschreibung, cBeschreibung, cBarcode, cHAN, cISBN)"
            ) && $this->execute(
                "ALTER TABLE tartikelsprache ADD FULLTEXT KEY idx_tartikelsprache_fulltext (cSeo, cName, cBeschreibung, cKurzBeschreibung)"
            );
        } catch (Exception $e) {
            $ft_enabled = false;
        }
        if ($ft_enabled) {
            $this->setConfig('suche_fulltext', 'Y', 4, 'Volltextsuche verwenden', 'selectbox', 105, (object)[
                'cBeschreibung' => 'F&uuml;r die Volltextsuche werden spezielle Indizes angelegt. Dies muss von der verwendeten Datenbankversion unterst&uuml;tzt werden.',
                'inputOptions' => [
                    'N' => 'Standardsuche verwenden',
                    'Y' => 'Volltextsuche verwenden',
                ],
            ]);

            $this->setConfig('suche_min_zeichen', '4', 4, 'Mindestzeichenanzahl des Suchausdrucks', 'number', 180, (object)[
                'cBeschreibung' => 'Unter dieser Zeichenanzahlgrenze wird die Suche nicht ausgef&uuml;hrt. (Bei Verwendung der Volltextsuche sollte dieser Wert an den Datenbankparameter ft_min_word_len angepasst werden.)',
            ], true);
        }
    }

    public function down()
    {
        $this->setConfig('suche_min_zeichen', '4', 4, 'Mindestzeichenanzahl des Suchausdrucks', 'number', 180, (object)[
            'cBeschreibung' => 'Unter dieser Zeichenanzahlgrenze wird die Suche nicht ausgef&uuml;hrt',
        ], true);
        $this->removeConfig('suche_fulltext');

        try {
            $this->execute(
                "DROP INDEX idx_tartikel_fulltext ON tartikel"
            );
            $this->execute(
                "DROP INDEX idx_tartikelsprache_fulltext ON tartikelsprache"
            );
        } catch (Exception $e) {
            /* Fehler beim Löschen der Indizes werden ignoriert... */
            null;
        }
    }
}
