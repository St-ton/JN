<?php
/**
 * adding language variable for download-order-date
 *
 * @author cr
 * @created Mon, 10 Feb 2020 12:30:41 +0100
 */

/**
 * Class Migration_20200210123041
 */
class Migration_20200210123041 extends Migration implements IMigration
{
    protected $author = 'cr';
    protected $description = 'Add lang var download order date';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'downloadOrderDate', 'Bestellt am');
        $this->setLocalization('eng', 'global', 'downloadOrderDate', 'Ordered on');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeLocalization('downloadOrderDate');
    }
}
