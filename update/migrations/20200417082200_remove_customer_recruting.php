<?php
/**
 * Remove customer recruting
 *
 * @author mh
 * @created Thu, 19 Mar 2020 16:25:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200417082200
 */
class Migration_20200417082200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove customer recruting';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->removeConfig('kwk_nutzen');
        $this->removeConfig('kwk_neukundenguthaben');
        $this->removeConfig('kwk_bestandskundenguthaben');
        $this->removeConfig('kwk_kundengruppen');
        $this->removeConfig('configgroup_116_customer_recruit_customer');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {

    }
}
