<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220222092000
 */
class Migration_20220222092000 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Show cron option';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET nStandardAnzeigen = 1 WHERE cWertName = 'cron_freq'");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET nStandardAnzeigen = 0 WHERE cWertName = 'cron_freq'");
    }
}
