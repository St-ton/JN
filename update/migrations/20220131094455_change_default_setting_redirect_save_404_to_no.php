<?php declare(strict_types=1);
/**
 * Change default setting redirect_save_404 to No
 *
 * @author cr
 * @created Mon, 31 Jan 2022 09:44:55 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220131094455
 */
class Migration_20220131094455 extends Migration implements IMigration
{
    protected $author = 'cr';
    protected $description = 'Change default setting redirect_save_404 to No';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE `teinstellungen_default` SET cWert = 'N' WHERE cName = 'redirect_save_404';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE `teinstellungen_default` SET cWert = 'Y' WHERE cName = 'redirect_save_404';");
    }
}
