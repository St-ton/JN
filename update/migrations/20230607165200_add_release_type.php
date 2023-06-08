<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * @since 5.3.0
 */
class Migration_20230607165200 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add release type to version table';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
            "ALTER TABLE `tversion` 
                ADD COLUMN `releaseType` ENUM('BETA', 'STABLE', 'BLEEDINGEDGE') NOT NULL DEFAULT 'STABLE'"
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute('ALTER TABLE `tversion` DROP COLUMN `releaseType`');
    }
}
