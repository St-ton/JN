<?php
/**
 * Add canary islands
 *
 * @author mh
 * @created Fr, 17 July 2020 12:16:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200717121600
 */
class Migration_20200717121600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add canary islands';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
       $this->execute("INSERT INTO tland VALUES ('IC', 'Kanarische Inseln', 'Canary Islands', 1, 'Europa')");
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute("DELETE FROM tland WHERE cISO = 'IC'");
    }
}
