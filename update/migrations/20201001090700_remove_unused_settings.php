<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201001090700
 */
class Migration_20201001090700 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove unused skalieren settings';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('bilder_kategorien_skalieren');
        $this->removeConfig('bilder_variationen_gross_skalieren');
        $this->removeConfig('bilder_variationen_skalieren');
        $this->removeConfig('bilder_variationen_mini_skalieren');
        $this->removeConfig('bilder_artikel_gross_skalieren');
        $this->removeConfig('bilder_artikel_normal_skalieren');
        $this->removeConfig('bilder_artikel_klein_skalieren');
        $this->removeConfig('bilder_artikel_mini_skalieren');
        $this->removeConfig('bilder_hersteller_normal_skalieren');
        $this->removeConfig('bilder_hersteller_klein_skalieren');
        $this->removeConfig('bilder_merkmal_normal_skalieren');
        $this->removeConfig('bilder_merkmal_klein_skalieren');
        $this->removeConfig('bilder_merkmalwert_normal_skalieren');
        $this->removeConfig('bilder_merkmalwert_klein_skalieren');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
