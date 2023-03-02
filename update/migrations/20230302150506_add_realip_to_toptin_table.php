<?php declare(strict_types=1);
/**
 * Add realIP to toptin table
 *
 * @author sl
 * @created Thu, 02 Mar 2023 15:05:06 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\DBManager;

/**
 * Class Migration_20230302150506
 */
class Migration_20230302150506 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Add realIP to toptin table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $table = 'toptin';
        if (!array_key_exists('cIP', DBManager::getColumns($table))) {
            $this->execute('ALTER TABLE ' . $table .
                ' ADD COLUMN cIP VARCHAR(255) DEFAULT NULL AFTER cMail');
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('toptin', 'cIP');
    }
}
