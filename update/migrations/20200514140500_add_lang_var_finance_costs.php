<?php

/**
 * @author ms
 * @created Thu, 14 May 2020 14:05:00 +0200
 */

/**
 * Class Migration_20200514140500
 */
class Migration_20200514140500 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'Add lang var for finance costs';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {

        $this->setLocalization('ger', 'order', 'financeCosts', 'zzgl. Finanzierungskosten');
        $this->setLocalization('eng', 'order', 'financeCosts', 'plus finance costs');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeLocalization('financeCosts');
    }
}
