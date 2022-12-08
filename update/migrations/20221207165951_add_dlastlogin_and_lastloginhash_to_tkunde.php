<?php declare(strict_types=1);
/**
 * add dLastLogin and lastLoginHash to tkunde
 *
 * @author sl
 * @created Wed, 07 Dec 2022 16:59:51 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221207165951
 */
class Migration_20221207165951 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'add dLastLogin and lastLoginHash to tkunde';

    public function up()
    {
        $this->execute('ALTER TABLE tkunde ADD COLUMN IF NOT EXISTS dLastLogin DATETIME DEFAULT CURRENT_TIMESTAMP() AFTER nLoginversuche');
        $this->execute('ALTER TABLE tkunde ADD COLUMN IF NOT EXISTS cLoginHash VARCHAR(40) DEFAULT "" AFTER nLoginversuche');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tkunde', 'dLastLogin');
        $this->dropColumn('tkunde', 'cLoginHash');
    }
}
