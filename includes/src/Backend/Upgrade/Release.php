<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use DateTime;
use Exception;
use JTLShop\SemVer\Version;
use stdClass;

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

    public function __construct(stdClass $data)
    {
        $this->channel     = $data->channel;
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
//        $this->isNewer = $this->version->greaterThan(Version::parse('5.1.0'));
    }
}
