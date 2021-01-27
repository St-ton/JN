<?php declare(strict_types=1);
/**
 * Remove UK from EU in tland
 *
 * @author cr
 * @created Tue, 19 Jan 2021 12:14:17 +0100
 */

/**
 * Class Migration_20210119102105
 */
class Migration_20210119102105 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Remove UK from EU in tland';

    /**
     * @return bool|void
     */
    public function up()
    {
        $this->execute("UPDATE tland SET nEU=0 WHERE cISO='GB'");
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->execute("UPDATE tland SET nEU=1 WHERE cISO='GB'");
    }
}
