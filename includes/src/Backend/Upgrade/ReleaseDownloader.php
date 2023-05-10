<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use Illuminate\Support\Collection;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use JTLShop\SemVer\Version;
use stdClass;

class ReleaseDownloader
{
    public const API_URL = 'http://localhost:8080/versions.json';

    private Collection $releases;

    public function __construct(private readonly JTLSmarty $smarty)
    {
        $json           = Request::http_get_contents(self::API_URL);
        $available      = \json_decode($json, false, 512, \JSON_THROW_ON_ERROR);
        $this->releases = \collect((array)$available)->mapInto(Release::class);
    }

    public function getReleases(): Collection
    {
        $channels       = Channels::getChannels();
        $activeChannel  = Channels::getActiveChannel();
        $currentVersion = Version::parse(\APPLICATION_VERSION);

        $filtered = $this->releases->filter(static function (Release $item) use ($activeChannel) {
            return $item->channel === $activeChannel;
        });

        $this->smarty->assign('availableVersions', $filtered)
            ->assign('channel', $activeChannel)
            ->assign('channels', $channels)
            ->assign('currentVersion', $currentVersion);

        return $filtered;
    }

    public function getReleaseByID(int $id): ?Release
    {
        return $this->releases->first(static function (Release $release) use ($id) {
            return $release->id === $id;
        });
    }
}
