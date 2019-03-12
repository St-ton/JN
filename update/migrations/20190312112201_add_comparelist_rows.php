<?php
/**
 * add_comparelist_rows
 *
 * @author mh
 * @created Tue, 12 Mar 2019 11:22:01 +0100
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
class Migration_20190312112201 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add comparelist rows';

    public function up()
    {
        $this->setConfig('vergleichsliste_verfuegbarkeit', 7, 106, 'Anzeigepriorität Verfügbarkeit', 'number', 113);
        $this->setConfig('vergleichsliste_lieferzeit', 6, 106, 'Anzeigepriorität Lieferzeit', 'number', 116);

        $this->setLocalization('ger', 'global', 'showNone', 'Alle ausblenden');
        $this->setLocalization('eng', 'global', 'showNone', 'Show none');
    }

    public function down()
    {
        $this->removeConfig('vergleichsliste_verfuegbarkeit');
        $this->removeConfig('vergleichsliste_lieferzeit');

        $this->removeLocalization('showNone');
    }
}
