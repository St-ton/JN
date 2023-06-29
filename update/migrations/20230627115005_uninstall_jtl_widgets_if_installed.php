<?php declare(strict_types=1);

/**
 * Uninstall jtl widgets if installed
 *
 * @author sl
 * @created Tue, 27 Jun 2023 11:50:05 +0200
 */

use JTL\Minify\MinifyService;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Cache\JTLCache;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\State;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230627115005
 */
class Migration_20230627115005 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'deactivate jtl widgets plugin if installed';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $widgetPlugin = Helper::getPluginById('jtl_widgets');
        if ($widgetPlugin !== null) {
            $widgetPlugin->selfDestruct(State::DISABLED, $this->getDB());
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
