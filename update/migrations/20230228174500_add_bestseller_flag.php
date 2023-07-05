<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230228174500
 */
class Migration_20230228174500 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add flag to tbestseller, that indicates if it is considered as such';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tbestseller ADD COLUMN isBestseller TINYINT NOT NULL DEFAULT 0');
        $this->execute('UPDATE tbestseller SET isBestseller = 1');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute('ALTER TABLE tbestseller DROP COLUMN isBestseller');
    }
}
