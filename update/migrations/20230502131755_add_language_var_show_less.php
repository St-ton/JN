<?php declare(strict_types=1);

/**
 * Add new language variable called 'less' used mainly for bootstrap collapse elements.
 *
 * @author timniko
 * @created Tue, 02 May 2023 13:17:55 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230502131755
 */
class Migration_20230502131755 extends Migration implements IMigration
{
    protected $author = 'timniko';
    protected $description = 'Add new language variable called less used mainly for bootstrap collapse elements.';

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'less', 'weniger');
        $this->setLocalization('eng', 'global', 'less', 'less');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 1 AND `cName` = 'less';");
    }
}
