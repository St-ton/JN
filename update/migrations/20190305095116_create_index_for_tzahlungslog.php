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
 * Class Migration_20190305095116
 */
class Migration_20190305095116 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for tzahlungslog';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tzahlungslog ADD INDEX idx_tzahlungslog_module (cModulId, nLevel)'
        );
    }


    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tzahlungslog DROP INDEX idx_tzahlungslog_module'
        );
    }
}
