<?php

/**
 * Add lang shipping info
 *
 * @author mh
 * @created Wed, 27 Nov 2019 10:48:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191127104800
 */
class Migration_20191127104800 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang shipping info';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'productDetails',
            'shippingInformation',
            'Angegebene Lieferzeiten gelten für den Versand innerhalb Deutschlands. Die Lieferzeiten' .
            ' für den Versand ins Ausland finden Sie unter unseren <a href=\'%s\'>Versandinformationen</a>'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'shippingInformation',
            'Angegebene Lieferzeiten gelten für den Versand innerhalb Deutschlands. Die Lieferzeiten' .
            ' für den Versand ins Ausland finden Sie unter unseren <a href=\'%s\'>Versandinformationen</a>'
        );
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeLocalization('shippingInformation');
    }
}
