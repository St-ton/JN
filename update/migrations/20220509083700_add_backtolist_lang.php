<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220308152700
 */
class Migration_20220509083700 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add Back-to-list language variable';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger',
            'global',
            'goBackToList',
            'Zurück zur Liste'
        );
        $this->setLocalization('eng',
            'global',
            'goBackToList',
            'Back to list'
        );
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('goBackToList', 'global');
    }
}
