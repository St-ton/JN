<?php
/**
 * changed some language-values for assets
 *
 * @author Clemens Rudolph
 * @created Fri, 20 Apr 2018 12:35:20 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180420123520
 */
class Migration_20180420123520 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'changed language-values for assets';

    public function up()
    {
        $this->setLocalization('ger', 'account data', 'useCredit', 'Guthaben verrechnet');
    }

    public function down()
    {
        $this->setLocalization('ger', 'account data', 'useCredit', 'Guthaben verrechnen');
    }
}
