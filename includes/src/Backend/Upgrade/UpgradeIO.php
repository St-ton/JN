<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTLShop\SemVer\Version;

/**
 * Class UpgradeIO
 * @package JTL\Backend\Upgrade
 * @since 5.3.0
 */
class UpgradeIO
{
    public function __construct(private readonly JTLSmarty $smarty, private readonly DbInterface $db)
    {
    }

    public function updateChannelIO(string $channel): array
    {
        $_SESSION['selected_upgrade_channel'] = $channel;

        return $this->render();
    }

    public function render(): array
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/upgrade');
        $activeChannel     = Channels::getActiveChannel();
        $releaseDownloader = new ReleaseDownloader($this->db);
        $filtered          = $releaseDownloader->getReleases($activeChannel);
        $this->smarty->assign('channels', Channels::getChannels())
            ->assign('activeChannel', $activeChannel)
            ->assign('availableVersions', $filtered)
            ->assign('currentVersion', Version::parse(\APPLICATION_VERSION));

        return [
            'channels' => $this->smarty->fetch('tpl_inc/upgrade_channels.tpl'),
            'upgrades' => $this->smarty->fetch('tpl_inc/upgrade_upgrades.tpl'),
            'filtered' => $filtered
        ];
    }
}
