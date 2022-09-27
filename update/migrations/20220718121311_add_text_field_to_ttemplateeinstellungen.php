<?php declare(strict_types=1);
/**
 * Add text field to ttemplateeinstellungen.
 *
 * @author fp
 * @created Mon, 18 Jul 2022 12:13:11 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220718121311
 */
class Migration_20220718121311 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Add text field to ttemplateeinstellungen';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE ttemplateeinstellungen MODIFY COLUMN cWert MEDIUMTEXT NULL');
        $this->execute('ALTER TABLE teinstellungen MODIFY COLUMN cWert MEDIUMTEXT NULL');
        $this->execute('ALTER TABLE tplugineinstellungen MODIFY COLUMN cWert MEDIUMTEXT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE ttemplateeinstellungen MODIFY COLUMN cWert VARCHAR(255) NULL');
        $this->execute('ALTER TABLE teinstellungen MODIFY COLUMN cWert VARCHAR(255) NULL');
        $this->execute('ALTER TABLE tplugineinstellungen MODIFY COLUMN cWert VARCHAR(255) NULL');
    }
}
