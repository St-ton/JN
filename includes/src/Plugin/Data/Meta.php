<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

use DateTime;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class Meta
 * @package JTL\Plugin\Data
 */
class Meta
{
    /**
     * @var string|null
     */
    private ?string $name;

    /**
     * @var string|null
     */
    private ?string $description = null;

    /**
     * @var string|null
     */
    private ?string $author = null;

    /**
     * @var string|null
     */
    private ?string $url = null;

    /**
     * @var string|null
     */
    private ?string $icon = null;

    /**
     * @var string|null
     */
    private ?string $readmeMD = null;

    /**
     * @var string|null
     */
    private ?string $licenseMD = null;

    /**
     * @var string|null
     */
    private ?string $changelogMD = null;

    /**
     * @var DateTime|null
     */
    private ?DateTime $dateLastUpdate = null;

    /**
     * @var DateTime|null
     */
    private ?DateTime $dateInstalled = null;

    /**
     * @var int|string
     */
    private $version;

    /**
     * @var Version|null
     */
    private ?Version $semVer = null;

    /**
     * @var bool|Version
     */
    private $updateAvailable = false;

    /**
     * @var string|null
     */
    private ?string $error = null;

    /**
     * @var string|null
     */
    private ?string $exsID = null;

    /**
     * @param stdClass $data
     * @return $this
     */
    public function loadDBMapping(stdClass $data): self
    {
        $this->author         = \__($data->cAutor);
        $this->description    = \__($data->cBeschreibung);
        $this->name           = \__($data->cName);
        $this->url            = \__($data->cURL);
        $this->icon           = $data->cIcon;
        $this->dateInstalled  = new DateTime($data->dInstalliert === 'NOW()' ? 'now' : $data->dInstalliert);
        $this->dateLastUpdate = new DateTime(
            $data->dZuletztAktualisiert === 'NOW()' ? 'now' : $data->dZuletztAktualisiert
        );
        $this->version        = $data->nVersion;
        $this->semVer         = Version::parse($this->version);
        $this->exsID          = $data->exsID;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string|null
     */
    public function getReadmeMD(): ?string
    {
        return $this->readmeMD;
    }

    /**
     * @param string $readmeMD
     */
    public function setReadmeMD(string $readmeMD): void
    {
        $this->readmeMD = $readmeMD;
    }

    /**
     * @return string|null
     */
    public function getLicenseMD(): ?string
    {
        return $this->licenseMD;
    }

    /**
     * @param string $licenseMD
     */
    public function setLicenseMD(string $licenseMD): void
    {
        $this->licenseMD = $licenseMD;
    }

    /**
     * @return string|null
     */
    public function getChangelogMD(): ?string
    {
        return $this->changelogMD;
    }

    /**
     * @param string $changelogMD
     */
    public function setChangelogMD(string $changelogMD): void
    {
        $this->changelogMD = $changelogMD;
    }

    /**
     * @return DateTime
     */
    public function getDateLastUpdate(): DateTime
    {
        return $this->dateLastUpdate;
    }

    /**
     * @param DateTime $dateLastUpdate
     */
    public function setDateLastUpdate(DateTime $dateLastUpdate): void
    {
        $this->dateLastUpdate = $dateLastUpdate;
    }

    /**
     * @return DateTime
     */
    public function getDateInstalled(): DateTime
    {
        return $this->dateInstalled;
    }

    /**
     * @param DateTime $dateInstalled
     */
    public function setDateInstalled(DateTime $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    /**
     * @return string|int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string|int $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
    }

    /**
     * @return Version
     */
    public function getSemVer(): Version
    {
        return $this->semVer;
    }

    /**
     * @param Version $semVer
     */
    public function setSemVer(Version $semVer): void
    {
        $this->semVer = $semVer;
    }

    /**
     * @return bool
     */
    public function isUpdateAvailable(): bool
    {
        return \is_bool($this->updateAvailable) ? $this->updateAvailable : $this->updateAvailable !== null;
    }

    /**
     * @param bool|Version $updateAvailable
     */
    public function setUpdateAvailable($updateAvailable): void
    {
        $this->updateAvailable = $updateAvailable;
    }

    /**
     * @return bool|Version
     */
    public function getUpdateAvailable()
    {
        return $this->updateAvailable;
    }

    /**
     * @return string|null
     */
    public function getExsID(): ?string
    {
        return $this->exsID;
    }

    /**
     * @param string|null $exsID
     */
    public function setExsID(?string $exsID): void
    {
        $this->exsID = $exsID;
    }
}
