<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class Release
 * @package JTL\License
 */
class Release
{
    public const TYPE_SECURITY = 'security';

    public const TYPE_FEATURE = 'feature';

    public const TYPE_BUGFIX = 'bugfix';

    /**
     * @var Version
     */
    private $version;

    /**
     * @var string
     */
    private $type;

    /**
     * @var DateTime
     */
    private $releaseDate;

    /**
     * @var string
     */
    private $shortDescription;

    /**
     * @var string
     */
    private $downloadUrl;

    /**
     * Release constructor.
     * @param stdClass|null $json
     */
    public function __construct(?stdClass $json)
    {
        if ($json !== null) {
            $this->fromJSON($json);
        }
    }

    /**
     * @param stdClass $json
     */
    public function fromJSON(stdClass $json): void
    {
        $this->setVersion(Version::parse($json->version));
        $this->setType($json->type);
        $this->setReleaseDate($json->releaseDate);
        $this->setShortDescription($json->shortDescription);
        $this->setDownloadURL($json->downloadUrl);
    }

    /**
     * @return Version
     */
    public function getVersion(): Version
    {
        return $this->version;
    }

    /**
     * @param Version $version
     */
    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return DateTime
     */
    public function getReleaseDate(): DateTime
    {
        return $this->releaseDate;
    }

    /**
     * @param DateTime|string $releaseDate
     */
    public function setReleaseDate($releaseDate): void
    {
        $this->releaseDate = \is_a(DateTime::class, $releaseDate) ? $releaseDate : new DateTime($releaseDate);
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     */
    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return string
     */
    public function getDownloadURL(): string
    {
        return $this->downloadUrl;
    }

    /**
     * @param string $downloadURL
     */
    public function setDownloadURL(string $downloadURL): void
    {
        $this->downloadUrl = $downloadURL;
    }
}
