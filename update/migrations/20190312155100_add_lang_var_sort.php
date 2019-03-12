<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;
/**
 * add_lang_var_sort
 *
 * @author ms
 * @created Tue, 12 Mar 2019 15:51:00 +0100
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
class Migration_20190312155100 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Add lang var for sort';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'filterAndSort', 'Filter & Sortierung');
        $this->setLocalization('eng', 'global', 'filterAndSort', 'filters & sorting');
    }

    public function down()
    {
        $this->removeLocalization('filterAndSort');
    }
}
