<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\MigrationHelper;

/**
 * Class Migration_20220209093200
 */
class Migration_20220209093200 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Make PLZ index unique, remove duplicate entries';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute("
            DELETE t1
            FROM tplz AS t1
                JOIN tplz AS t2 ON
                    t1.cPLZ = t2.cPLZ AND
                    t1.cLandISO = t2.cLandISO AND
                    t1.cOrt = t2.cOrt AND
                    t1.kPLZ < t2.kPLZ
        ");
        MigrationHelper::createIndex('tplz', ['cLandISO', 'cPLZ', 'cOrt'], 'PLZ_ORT_UNIQUE', true);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        MigrationHelper::createIndex('tplz', ['cLandISO', 'cPLZ', 'cOrt'], 'PLZ_ORT_UNIQUE');
    }
}
