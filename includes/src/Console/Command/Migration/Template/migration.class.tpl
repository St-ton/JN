<?php declare(strict_types=1);
/**
 * {$description}
 *
 * @author {$author}
 * @created {$created}
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_{$timestamp}
 */
class Migration_{$timestamp} extends Migration implements IMigration
{
    protected $author = '{$author}';
    protected $description = '{$description}';

    /**
     * @inheritdoc
     */
    public function up()
    {
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
