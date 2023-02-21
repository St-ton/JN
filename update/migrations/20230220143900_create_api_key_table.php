<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230220143900
 */
class Migration_20230220143900 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Create API key table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `api_keys` (
                `id`        INT          NOT NULL AUTO_INCREMENT,
                `key`       VARCHAR(255) NOT NULL,
                `created`   DATETIME     NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `api_keys`');
    }
}
