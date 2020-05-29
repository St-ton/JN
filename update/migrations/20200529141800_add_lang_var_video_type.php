<?php
/**
 * Add lang var videoTypeNotSupported
 *
 * @author je
 * @created Fr, 29 May 2020 14:18:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200529141800
 */
class Migration_20200529141800 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Add lang var videoTypeNotSupported';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'errorMessages', 'videoTypeNotSupported', 'Das Videoformat wird nicht unterstützt.');
        $this->setLocalization('eng', 'errorMessages', 'videoTypeNotSupported', 'This video type is not supported.');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeLocalization('videoTypeNotSupported');
    }
}
