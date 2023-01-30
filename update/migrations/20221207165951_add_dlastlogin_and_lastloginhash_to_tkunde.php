<?php declare(strict_types=1);
/**
 * add dLastLogin and lastLoginHash to tkunde
 *
 * @author sl
 * @created Wed, 07 Dec 2022 16:59:51 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\DBManager;

/**
 * Class Migration_20221207165951
 */
class Migration_20221207165951 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'add dLastLogin to tkunde';

    public function up()
    {
        $table = 'tkunde';
        if (!array_key_exists('dLastLogin', DBManager::getColumns($table))) {
            $this->execute('ALTER TABLE ' . $table .
            ' ADD COLUMN dLastLogin DATETIME DEFAULT NULL AFTER nLoginversuche');
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tkunde', 'dLastLogin');
    }
}
