<?php
/**
 * remove global html entity config
 *
 * @author fm
 * @created Mon, 11 Mar 2019 12:28:00 +0100
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
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190311122800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'remove global html entity config';

    public function up()
    {
        $this->removeConfig('global_artikelname_htmlentities');
    }

    public function down()
    {
        $this->setConfig(
            'global_artikelname_htmlentities',
            'N',
            \CONF_GLOBAL,
            'HTML-Code Umwandlung bei Artikelnamen',
            'selectbox',
            280,
            (object)[
                'cBeschreibung' => 'Sollen Sonderzeichen im Artikelnamen in HTML Entities umgewandelt werden\'',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
        ]);
    }
}
