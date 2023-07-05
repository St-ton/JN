<?php declare(strict_types=1);
/**
 * Create table for user depended category discount
 *
 * @author fp
 * @created Wed, 05 Jul 2023 12:32:41 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230705123241
 */
class Migration_20230705123241 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'New table for user dependent category discount';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
            'CREATE TABLE `category_customerdiscount` (
                id          INT     NOT NULL AUTO_INCREMENT PRIMARY KEY,
                customerId  INT     NOT NULL,
                categoryId  INT     NOT NULL,
                discount    FLOAT   NOT NULL DEFAULT 0,
                UNIQUE KEY `idx_category_customer_uq` (customerId, categoryId)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute(
            'DROP TABLE IF EXISTS `category_customerdiscount`'
        );
    }
}
