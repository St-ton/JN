<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211111091000
 */
class Migration_20211111091000 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Add target column to tlink';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE tlink ADD COLUMN target VARCHAR(20) NOT NULL DEFAULT '_self'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tlink DROP COLUMN target');
    }
}
