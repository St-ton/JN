<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230616141800
 */
class Migration_20230616141800 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add redis user config';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setConfig(
            'caching_redis_user',
            '',
            CONF_CACHING,
            'Username für Redis',
            'text',
            48,
            (object)['nStandardAnzeigen' => 0]
        );
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->removeConfig('caching_redis_user');
    }
}
