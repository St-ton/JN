<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210505144200
 */
class Migration_20210505144200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add filter search lang';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->setLocalization('ger', 'productOverview', 'filterSearchPlaceholder', 'Suchen in %s');
        $this->setLocalization('eng', 'productOverview', 'filterSearchPlaceholder', 'Search in %s');
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->removeLocalization('filterSearchPlaceholder', 'productOverview');
    }
}
