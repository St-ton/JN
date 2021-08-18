<?php declare(strict_types=1);
/**
 * Add 'google two-factor-authentication'
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210817130200
 */
class Migration_20210817130200 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE tkunde ADD b2FAauth tinyint(1) default 0, ADD c2FAauthSecret varchar(100) default ''");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tkunde', 'b2FAauth');
        $this->dropColumn('tkunde', 'c2FAauthSecret');
    }
}
