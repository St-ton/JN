<?php
/**
 * @author fm
 * @created Thu, 30 Oct 2018 11:06:00 +0200
 */

/**
 * Class Migration_20181030110600
 */
class Migration_20181030110600 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add admin login lock';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tadminlogin` ADD COLUMN `locked_at` DATETIME DEFAULT NULL');
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tadminlogin` DROP COLUMN `locked_at`');
    }
}
