<?php
/**
 * create newsletter cron job
 *
 * @author Clemens Rudolph
 * @created Tue, 28 May 2019 12:32:23 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190528123223
 */
class Migration_20190528123223 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'Create newsletter cron job';

    public function up()
    {
        $this->execute("DELETE FROM tcron WHERE jobType = 'newsletter'");
        $this->execute("INSERT INTO tcron(
                foreignKeyID,
                foreignKey,
                tableName,
                name,
                jobType,
                frequency,
                startDate,
                startTime,
                lastStart,
                lastFinish
            ) VALUES (
                0,
                'kNewsletter',
                'tnewsletter',
                'Newsletter',
                'newsletter',
                2,
                NULL,
                NULL,
                NULL,
                NULL
            )"
        );
    }

    public function down()
    {
        $this->execute("DELETE FROM tcron WHERE jobType = 'newsletter'");
    }
}
