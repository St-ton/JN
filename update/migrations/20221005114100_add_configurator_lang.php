<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221005114100
 */
class Migration_20221005114100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add configurator lang';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'configIsOptional','Diese Konfigurationsgruppe ist optional.');
        $this->setLocalization('eng', 'productDetails', 'configIsOptional','This configuration group is optional.');
        $this->setLocalization('ger', 'productDetails', 'configIsNotCorrect','Diese Konfigurationsgruppe ist noch nicht richtig eingestellt.');
        $this->setLocalization('eng', 'productDetails', 'configIsNotCorrect','This configuration group must be configured correctly.');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeLocalization('configIsOptional', 'productDetails');
        $this->removeLocalization('configIsNotCorrect', 'productDetails');
    }
}
