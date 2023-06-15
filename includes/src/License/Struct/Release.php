<?php declare(strict_types=1);

namespace JTL\License\Struct;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class Release
 * @package JTL\License
 */
class Release
{
    public const PHP_VERSION_OK = 0;

    public const PHP_VERSION_LOW = -1;

    public const PHP_VERSION_HIGH = 1;

    public const TYPE_SECURITY = 'security';

    public const TYPE_FEATURE = 'feature';

    public const TYPE_BUGFIX = 'bugfix';

    /**
     * @var Version
     */
    private Version $version;

    /**
     * @var string
     */
    private string $type;

    /**
     * @var DateTime
     */
    private DateTime $releaseDate;

    /**
     * @var string
     */
    private string $shortDescription;

    /**
     * @var string
     */
    private string $downloadUrl;

    /**
     * @var string - sha1 checksum
     */
    private string $checksum;

    /**
     * @var Version|null
     */
    private ?Version $phpMinVersion = null;

    /**
     * @var Version|null
     */
    private ?Version $phpMaxVersion = null;

    /**
     * @var int
     */
    private int $phpVersionOK = self::PHP_VERSION_OK;

    /**
     * @var bool
     */
    private bool $includesSecurityFixes = false;

    /**
     * Release constructor.
     * @param stdClass|null $json
     */
    public function __construct(?stdClass $json = null)
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
        $this->setReleaseDate($json->release_date);
        $this->setShortDescription($json->short_description);
        $this->setDownloadURL($json->download_url);
        $this->setChecksum($json->checksum ?? '');
        $this->setIncludesSecurityFixes($json->includes_security_fixes ?? false);
        if (isset($json->min_php_version)) {
            $this->setPhpMinVersion(Version::parse($json->min_php_version));
        }
        if (isset($json->max_php_version)) {
            $this->setPhpMaxVersion(Version::parse($json->max_php_version));
        }
        $this->checkPhpVersion();
    }

    private function checkPhpVersion(): void
    {
        $php     = Version::parse(\PHP_VERSION);
        $version = new Version();
        $version->setMajor($php->getMajor());
        $version->setMinor($php->getMinor());
        if ($this->getPhpMaxVersion() !== null && $version->greaterThan($this->getPhpMaxVersion())) {
            $this->setPhpVersionOK(self::PHP_VERSION_HIGH);
        } elseif ($this->getPhpMinVersion() !== null && $version->smallerThan($this->getPhpMinVersion())) {
            $this->setPhpVersionOK(self::PHP_VERSION_LOW);
        }
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
     * @throws \Exception
     */
    public function setReleaseDate(DateTime|string $releaseDate): void
    {
        $this->releaseDate = \is_a($releaseDate, DateTime::class)
            ? $releaseDate
            : Carbon::createFromTimeString($releaseDate, 'UTC')
                ->toDateTime()
                ->setTimezone(new DateTimeZone(\SHOP_TIMEZONE));
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
     * @return string|null
     */
    public function getDownloadURL(): ?string
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

    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * @param string $checksum
     */
    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }

    /**
     * @return bool
     */
    public function includesSecurityFixes(): bool
    {
        return $this->includesSecurityFixes;
    }

    /**
     * @param bool $includesSecurityFixes
     */
    public function setIncludesSecurityFixes(bool $includesSecurityFixes): void
    {
        $this->includesSecurityFixes = $includesSecurityFixes;
    }

    /**
     * @return Version|null
     */
    public function getPhpMinVersion(): ?Version
    {
        return $this->phpMinVersion;
    }

    /**
     * @param Version|null $phpMinVersion
     */
    public function setPhpMinVersion(?Version $phpMinVersion): void
    {
        $this->phpMinVersion = $phpMinVersion;
    }

    /**
     * @return Version|null
     */
    public function getPhpMaxVersion(): ?Version
    {
        return $this->phpMaxVersion;
    }

    /**
     * @param Version|null $phpMaxVersion
     */
    public function setPhpMaxVersion(?Version $phpMaxVersion): void
    {
        $this->phpMaxVersion = $phpMaxVersion;
    }

    /**
     * @return int
     */
    public function getPhpVersionOK(): int
    {
        return $this->phpVersionOK;
    }

    /**
     * @param int $phpVersionOK
     */
    public function setPhpVersionOK(int $phpVersionOK): void
    {
        $this->phpVersionOK = $phpVersionOK;
    }
}
