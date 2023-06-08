<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use DateTime;
use Exception;
use JTL\Plugin\Admin\Markdown;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class Release
 * @package JTL\Backend\Upgrade
 * @since 5.3.0
 */
class Release
{
    public Version $version;

    public bool $isNewer;

    public string $channel;

    public string $downloadURL;

    public string $checksum;

    public string $hash = 'sha1';

    public DateTime $date;

    public int $id;

    public string $changelog;

    public function __construct(stdClass $data)
    {
        $md                = new Markdown();
        $this->channel     = $data->channel;
        $this->changelog   = $md->text($data->changelog ?? '');
        $this->id          = $data->id;
        $this->downloadURL = $data->downloadUrl;
        $this->checksum    = $data->sha1;
        $this->date        = new DateTime($data->last_modified);
        try {
            $this->version = Version::parse($data->reference);
            $this->version->setPrefix('');
        } catch (Exception) {
            $this->version = Version::parse('0.0.0');
        }
        $this->isNewer = $this->version->greaterThan(Version::parse(\APPLICATION_VERSION));
    }
}
