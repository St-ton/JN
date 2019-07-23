<?php
/**
 * Add sending progress col to tnewsletter
 *
 * @author Clemens Rudolph
 * @created Wed, 10 Jul 2019 09:05:52 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * class Migration_20190710090552
 */
class Migration_20190710090552 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'Add sending progress col to tnewsletter';

    public function up()
    {
        $this->execute("
            ALTER TABLE `tnewsletter`
                ADD COLUMN `dLastSendings`
                    DATETIME
                    DEFAULT NULL
                    COMMENT 'finish time of last sending of this NL'
                    AFTER `dStartZeit`
        ");
    }

    public function down()
    {
        $this->execute('ALTER TABLE `tnewsletter` DROP COLUMN `dLastSendings`');
    }
}
