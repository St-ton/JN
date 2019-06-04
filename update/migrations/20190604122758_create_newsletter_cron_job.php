<?php
/**
 * create newsletter cron job
 *
 * @author Clemens Rudolph
 * @created Tue, 04 Jun 2019 12:27:58 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190604122758
 */
class Migration_20190604122758 extends Migration implements IMigration
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
