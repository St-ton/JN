<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210914080323
 */
class Migration_20210914080323 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Reset fallback payment';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE tzahlungsart SET nNutzbar=0 WHERE cModulId='za_null_jtl'"
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
