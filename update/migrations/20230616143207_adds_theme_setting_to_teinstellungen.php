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
    protected $description = 'Adds theme mode setting to teinstellungen';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'backend_theme',
            'auto',
            CONF_GLOBAL,
            'Light- oder Darkmode',
            'text',
            1517,
            (object)['nStandardAnzeigen' => 0]
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('backend_theme');
    }
}
