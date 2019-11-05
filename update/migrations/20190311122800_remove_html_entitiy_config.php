<?php
/**
 * Remove setting for productname entity encoding
 *
 * @author fp
 * @created Tue, 09 Jul 2019 17:08:19 +0200
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
class Migration_20190311122800 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Remove setting for productname entity encoding';

    /**
     * @return bool|void
     */
    public function up()
    {
        $this->removeConfig('global_artikelname_htmlentities');
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->setConfig(
            'global_artikelname_htmlentities',
            'N',
            CONF_GLOBAL,
            'HTML-Code Umwandlung bei Artikelnamen',
            'selectbox',
            280,
            (object)[
                'cBeschreibung' => 'Sollen Sonderzeichen im Artikelnamen in HTML Entities umgewandelt werden',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }
}
