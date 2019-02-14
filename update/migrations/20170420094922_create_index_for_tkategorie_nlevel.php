<?php
/**
 * Create index for tkategorie.nLevel
 *
 * @author Falk Prüfer
 * @created Thu, 20 Apr 2017 09:49:22 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\MigrationHelper;

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
class Migration_20170420094922 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for tkategorie.nLevel';

    public function up()
    {
        MigrationHelper::createIndex('tkategorie', ['nLevel'], 'idx_tkategorie_nLevel');
    }

    public function down()
    {
        MigrationHelper::dropIndex('tkategorie', 'idx_tkategorie_nLevel');
    }
}
