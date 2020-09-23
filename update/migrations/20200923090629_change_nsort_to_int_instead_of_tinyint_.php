<?php declare(strict_types=1);
/**
 * Change nSort to INT instead of TINYINT.
 *
 * @author fp
 * @created Wed, 23 Sep 2020 09:06:29 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200923090629
 */
class Migration_20200923090629 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Change nSort to INT instead of TINYINT.';

    public function up()
    {
        $this->execute('ALTER TABLE tsuchcachetreffer MODIFY nSort int signed DEFAULT 0 NOT NULL');
    }

    public function down()
    {
        $this->execute('ALTER TABLE tsuchcachetreffer MODIFY nSort tinyint unsigned DEFAULT 0 NOT NULL');
    }
}
