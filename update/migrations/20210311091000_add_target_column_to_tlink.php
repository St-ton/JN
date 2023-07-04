<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210311091000
 */
class Migration_20210311091000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add target column to tlink';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $columns = $this->getDB()->getSingleObject("SHOW COLUMNS FROM `tlink` LIKE 'target'");
        if ($columns === null) {
            $this->execute("ALTER TABLE tlink ADD COLUMN target VARCHAR(20) NOT NULL DEFAULT '_self'");
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tlink DROP COLUMN target');
    }
}
