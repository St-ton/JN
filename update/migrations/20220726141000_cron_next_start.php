<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220726141000
 */
class Migration_20220726141000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Next start for cron jobs';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tcron` ADD COLUMN `nextStart` DATETIME NULL DEFAULT NULL AFTER `lastStart`');
        foreach ($this->getDB()->getObjects('SELECT * FROM tcron') as $cron) {
            if (!empty($cron->lastFinish)) {
                $this->getDB()->queryPrepared(
                    'UPDATE tcron 
                        SET nextStart = DATE_ADD(ADDTIME(DATE(lastFinish), startTime), INTERVAL frequency HOUR) 
                    WHERE cronID = :id',
                    ['id' => (int)$cron->cronID]
                );
            } else {
                $this->getDB()->queryPrepared(
                    'UPDATE tcron 
                        SET nextStart = DATE_ADD(ADDTIME(DATE(startDate), startTime), INTERVAL frequency HOUR) 
                    WHERE cronID = :id',
                    ['id' => (int)$cron->cronID]
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tcron` DROP COLUMN `nextStart`');
    }
}
