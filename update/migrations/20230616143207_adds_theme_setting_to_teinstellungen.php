<?php declare(strict_types=1);
/**
 * Adds theme setting to teinstellungen
 *
 * @author Tim Niko Tegtmeyer
 * @created Fri, 16 Jun 2023 14:32:07 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230616143207
 */
class Migration_20230616143207 extends Migration implements IMigration
{
    protected $author = 'Tim Niko Tegtmeyer';
    protected $description = 'Adds theme mode setting to tadminlogin';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE `tadminlogin` ADD theme VARCHAR(5) NOT NULL DEFAULT 'auto'");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tadminlogin` DROP COLUMN theme');
    }
}
