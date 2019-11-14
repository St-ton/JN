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
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_{$timestamp} extends Migration implements IMigration
{
    protected $author = '{$author}';
    protected $description = '{$description}';

    public function up()
    {
    }

    public function down()
    {
    }
}
