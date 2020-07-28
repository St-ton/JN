<?php
/**
 * Increase versandklasse varchar size
 *
 * @author mh
 * @created Fr, 17 July 2020 12:36:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200717123600
 */
class Migration_20200717123600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Increase versandklasse varchar size';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tversandart` MODIFY COLUMN `cVersandklassen` VARCHAR (8192)');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tversandart` MODIFY COLUMN `cVersandklassen` VARCHAR (255)');
    }
}
