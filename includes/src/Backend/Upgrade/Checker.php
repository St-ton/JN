<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTLShop\SemVer\Version;

/**
 * Class Checker
 * @package JTL\Backend\Upgrade
 * @since 5.3.0
 */
class Checker
{
    private Collection $newerReleases;

    public function __construct(private readonly DbInterface $db)
    {
        $this->newerReleases = new Collection();
        $this->check();
    }

    public function check(): void
    {
        $downloader = new ReleaseDownloader($this->db);
        $channel    = $this->db->select('tversion', [], [])->releaseType;
        $version    = Version::parse(\APPLICATION_VERSION);

        $this->newerReleases = $downloader->getReleases($channel)
            ->filter(static function (Release $release) use ($version) {
                return $release->version->greaterThan($version);
            })
            ->sort(static function (Release $a, Release $b) {
                return $a->version->greaterThan($b->version) ? 1 : -1;
            });
    }

    public function hasUpgrade(): bool
    {
        return $this->newerReleases->count() > 0;
    }

    public function getNextUpgrade(): ?Release
    {
        return $this->newerReleases->first();
    }

    public function getLatestUpgrade(): ?Release
    {
        return $this->newerReleases->last();
    }
}
