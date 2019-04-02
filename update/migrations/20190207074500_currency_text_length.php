<?php
/**
 * Increase text fiels length for currencies
 *
 * @author Felix Moche
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190207074500
 */
class Migration_20190207074500 extends Migration implements IMigration
{
    protected $author      = 'Felix Moche';
    protected $description = 'Increase currency table text fields length';

    public function up()
    {
        $this->execute('ALTER TABLE `twaehrung` 
            CHANGE COLUMN `cName` `cName` VARCHAR(255) NULL DEFAULT NULL,
            CHANGE COLUMN `cNameHTML` `cNameHTML` VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down()
    {
        $this->execute('ALTER TABLE `twaehrung` 
            CHANGE COLUMN `cName` `cName` VARCHAR(20) NULL DEFAULT NULL,
            CHANGE COLUMN `cNameHTML` `cNameHTML` VARCHAR(20) NULL DEFAULT NULL');
    }
}
