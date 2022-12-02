<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221118111100
 */
class Migration_20221118111100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Rename consent reject button lang';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'consent', 'close','Ablehnen');
        $this->setLocalization('eng', 'consent', 'close','Reject');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->setLocalization('ger', 'consent', 'close','Schließen');
        $this->setLocalization('eng', 'consent', 'close','Close');
    }
}
