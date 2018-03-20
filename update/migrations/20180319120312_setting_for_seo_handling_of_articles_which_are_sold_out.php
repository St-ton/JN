<?php
/**
 * Setting for SEO handling of articles which are sold out
 *
 * @author fp
 * @created Mon, 19 Mar 2018 12:03:12 +0100
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
class Migration_20180319120312 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Setting for SEO handling of articles which are sold out';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'artikel_artikelanzeigefilter_seo',
            'seo',
            CONF_GLOBAL,
            'Direktaufruf ausverkaufter Artikel',
            'selectbox',
            215,
            (object)[
                'cBeschreibung' => 'Methode beim Direktaufruf (über Artikel-URL) ausverkaufter Artikel. (Ist nur wirksam, wenn "Artikelanzeigefilter" aktiv ist.)',
                'inputOptions'  => [
                    '301' => 'Weiterleitung zur Startseite (301 Redirect)',
                    '404' => 'Seite nicht gefunden (404 Not Found)',
                    'seo' => 'Artikel-Detailseite bleibt erreichbar',
                ],
            ]
        );
    }

    public function down()
    {
        $this->removeConfig('artikel_artikelanzeigefilter_seo');
    }
}
