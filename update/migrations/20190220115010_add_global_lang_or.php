<?php
/**
 * add_global_lang_or
 *
 * @author mh
 * @created Wed, 20 Feb 2019 11:50:10 +0100
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
class Migration_20190220115010 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add global lang var or';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'or', 'oder');
        $this->setLocalization('eng', 'global', 'or', 'or');
    }

    public function down()
    {
        $this->removeLocalization('or');
    }
}
