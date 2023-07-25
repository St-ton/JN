<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220304093500
 */
class Migration_20220304093500 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Add "internal" flag to campaigns';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tkampagne
                ADD nInternal INT DEFAULT 0 NOT NULL;');
        $this->execute('UPDATE tkampagne SET nInternal=1 WHERE kKampagne < 1000');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tkampagne
                DROP COLUMN nInternal');
    }
}
