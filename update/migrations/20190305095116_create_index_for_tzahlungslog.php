<?php
/**
 * Create index for tzahlungslog
 *
 * @author fp
 * @created Tue, 05 Mar 2019 09:51:16 +0100
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
class Migration_20190305095116 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for tzahlungslog';

    public function up()
    {
        $this->execute(
            'ALTER TABLE tzahlungslog ADD INDEX idx_tzahlungslog_module (cModulId, nLevel)'
        );
    }


    public function down()
    {
        $this->execute(
            'ALTER TABLE tzahlungslog DROP INDEX idx_tzahlungslog_module'
        );
    }
}
