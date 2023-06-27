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
use JTL\Plugin\InstallCode;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230627115005
 */
class Migration_20230627115005 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Uninstall jtl widgets if installed';

    /**
     * @inheritdoc
     */
    public function up()
    {
        /** @var JTL\Cache\JTLCache $cache */
        $cache       = new JTLCache([]);
        $uninstaller = new Uninstaller($this->db, $cache);
        $plugin      = $this->db->select('tplugin', 'cPluginID', 'jtl_widgets');
        $ok          = false;
        if ($plugin !== null && $plugin->kPlugin > 0) {
            switch ($uninstaller->uninstall((int)$plugin->kPlugin, false, null, true, true)) {
                case InstallCode::WRONG_PARAM:
                    $this->errorMessage = \__('errorAtLeastOnePlugin');
                    break;
                case InstallCode::SQL_ERROR:
                    $this->errorMessage = \__('errorPluginDeleteSQL');
                    break;
                case InstallCode::NO_PLUGIN_FOUND:
                    $this->errorMessage = \__('errorPluginNotFound');
                    break;
                case InstallCode::OK:
                default:
                    $minifyService = new MinifyService();
                    $minifyService->flushCache();
                break;
            }
        }
        //stackoverflow
        $dir   = PFAD_ROOT . 'plugins/jtl_widgets';
        $it    = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
