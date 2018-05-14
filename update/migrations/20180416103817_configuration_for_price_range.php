<?php
/**
 * Configuration for price range
 *
 * @author fp
 * @created Mon, 16 Apr 2018 10:38:17 +0200
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
class Migration_20180416103817 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Configuration for price range';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig('articleoverview_pricerange_width', '150', CONF_ARTIKELUEBERSICHT, 'Max. Abweichung (%) für Preis-Range Anzeige', 'number', 372, (object)[
            'cBeschreibung' => 'Überschreitet der Max. Preis den Min. Preis um die angegebenen Prozent, dann wird stattdessen nur ein "ab" angezeigt.',
        ]);
    }

    public function down()
    {
        $this->removeConfig('articleoverview_pricerange_width');
    }
}
