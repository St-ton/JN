<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use JTL\Smarty\JTLSmarty;

class UpgradeIO
{
    public function __construct(private readonly JTLSmarty $smarty)
    {
    }

    public function updateChannelIO(string $channel): array
    {
        $_SESSION['selected_upgrade_channel'] = $channel;

        return $this->render();
    }

    public function render(): array
    {
        $this->smarty->assign('channels', Channels::getChannels());
        $releaseDownloader = new ReleaseDownloader($this->smarty);
        $filtered = $releaseDownloader->getReleases();

        return [
            'channels' => $this->smarty->fetch('tpl_inc/upgrade_channels.tpl'),
            'upgrades' => $this->smarty->fetch('tpl_inc/upgrade_upgrades.tpl'),
            'filtered' => $filtered
        ];
    }
}
