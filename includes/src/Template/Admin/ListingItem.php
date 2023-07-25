<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use DateTime;
use InvalidArgumentException;
use JTL\Backend\FileCheck;
use JTL\Plugin\State;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTLShop\SemVer\Version;

/**
 * Class ListingItem
 * @package JTL\Template\Admin
 */
class ListingItem
{
    /**
     * @var string
     */
    private string $path = '';

    /**
     * @var string
     */
    private string $dir = '';

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var Version|null
     */
    private ?Version $version = null;

    /**
     * @var Version|null
     */
    private ?Version $maxShopVersion = null;

    /**
     * @var Version|null
     */
    private ?Version $minShopVersion = null;

    /**
     * @var string
     */
    private string $description = '';

    /**
     * @var string
     */
    private string $author = '';

    /**
     * @var string|null
     */
    private ?string $preview = '';

    /**
     * @var string|null
     */
    private ?string $url = '';

    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var string|null
     */
    private ?string $framework = '';

    /**
     * @var string|null
     */
    private ?string $exsid = '';

    /**
     * @var int
     */
    private int $errorCode = 0;

    /**
     * @var string
     */
    private string $errorMessage = '';

    /**
     * @var bool
     */
    private bool $hasError = false;

    /**
     * @var bool
     */
    private bool $available = true;

    /**
     * @var bool
     */
    private bool $active = false;

    /**
     * @var int
     */
    private int $state = State::NONE;

    /**
     * @var bool|Version
     */
    private $updateAvailable = false;

    /**
     * @var bool
     */
    private bool $hasLicenseCheck = false;

    /**
     * @var bool
     */
    private bool $isChild = false;

    /**
     * @var string
     */
    private string $license = '';

    /**
     * @var string|null
     */
    private ?string $updateFromDir;

    /**
     * @var DateTime|null
     */
    private ?DateTime $dateInstalled;

    /**
     * @var int
     */
    private int $langVarCount = 0;

    /**
     * @var int
     */
    private int $linkCount = 0;

    /**
     * @var int
     */
    private int $optionsCount = 0;

    /**
     * @var string|null
     */
    private ?string $readmeMD;

    /**
     * @var string|null
     */
    private ?string $licenseMD;

    /**
     * @var string|null
     */
    private ?string $parent;

    /**
     * @var array|bool|null
     */
    private $checksums;

    /**
     * @var bool
     */
    private bool $isPreview = false;

    /**
     * @param array $xml
     * @param int   $validationResult
     * @return ListingItem
     */
    public function parseXML(array $xml, int $validationResult): self
    {
        $this->name = $xml['cVerzeichnis'];
        $this->dir  = $xml['cVerzeichnis'];
        $node       = $xml['Template'][0] ?? null;
        if ($validationResult !== TemplateValidator::RES_OK) {
            return $this->fail($validationResult);
        }
        if ($node !== null) {
            $this->name           = $node['Name'];
            $this->description    = $node['Description'] ?? '';
            $this->exsid          = $node['ExsID'] ?? '';
            $this->author         = $node['Author'] ?? '';
            $this->url            = $node['URL'] ?? null;
            $this->preview        = $node['Preview'] ?? null;
            $this->framework      = $node['Framework'] ?? null;
            $this->isChild        = isset($node['Parent']);
            $this->parent         = $node['Parent'] ?? null;
            $version              = $node['Version'] ?? $node['ShopVersion'];
            $this->optionsCount   = ($this->isChild() || isset($node['Settings'][0])) ? 1 : 0;
            $this->maxShopVersion = Version::parse($node['MaxShopVersion'] ?? '0.0.0');
            $this->minShopVersion = Version::parse($node['MinShopVersion'] ?? $node['ShopVersion'] ?? '5.0.0');
            $this->addChecksums();
            try {
                $this->version = Version::parse($version);
            } catch (InvalidArgumentException) {
                $xml['cFehlercode'] = TemplateValidator::RES_SHOP_VERSION_NOT_FOUND;
            }
        }
        if ($xml['cFehlercode'] !== TemplateValidator::RES_OK) {
            return $this->fail($xml['cFehlercode']);
        }

        return $this;
    }

    /**
     * @param int $code
     */
    private function generateErrorMessage(int $code): void
    {
        $msg = match ($code) {
            TemplateValidator::RES_OK                     => '',
            TemplateValidator::RES_PARENT_NOT_FOUND       => \__('errorParentNotFound'),
            TemplateValidator::RES_SHOP_VERSION_NOT_FOUND => \__('errorShopVersionNotFound'),
            TemplateValidator::RES_XML_NOT_FOUND          => \__('errorXmlNotFound'),
            TemplateValidator::RES_XML_PARSE_ERROR        => \__('errorXmlParse'),
            TemplateValidator::RES_NAME_NOT_FOUND         => \__('errorNameNotFound'),
            TemplateValidator::RES_INVALID_VERSION        => \__('errorInvalidVersion'),
            TemplateValidator::RES_INVALID_NAMESPACE      => \__('errorInvalidNamespace'),
            default                                       => \__('errorUnknown'),
        };
        $this->setErrorMessage($msg);
    }

    private function addChecksums(): void
    {
        $files       = [];
        $errorsCount = 0;
        $base        = \PFAD_ROOT . \PFAD_TEMPLATES . \basename($this->dir) . '/';
        $checker     = new FileCheck();
        $res         = $checker->validateCsvFile($base . 'checksums.csv', $files, $errorsCount, $base);
        if ($res === FileCheck::ERROR_INPUT_FILE_MISSING || $res === FileCheck::ERROR_NO_HASHES_FOUND) {
            $this->checksums = null;

            return;
        }
        $this->checksums = $errorsCount === 0 ? true : $files;
    }

    /**
     * @param int $errorCode
     * @return $this
     */
    private function fail(int $errorCode): self
    {
        $this->version        = $this->version ?? Version::parse('0.0.0');
        $this->maxShopVersion = $this->maxShopVersion ?? Version::parse('0.0.0');
        $this->minShopVersion = $this->minShopVersion ?? Version::parse('5.0.0');
        $this->setAvailable(false);
        $this->setHasError(true);
        $this->setErrorCode($errorCode);
        $this->generateErrorMessage($errorCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
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
     * @return string|null
     */
    public function getPreview(): ?string
    {
        return $this->preview;
    }

    /**
     * @param string|null $preview
     */
    public function setPreview(?string $preview): void
    {
        $this->preview = $preview;
    }

    /**
     * @return string|null
     */
    public function getFramework(): ?string
    {
        return $this->framework;
    }

    /**
     * @param string|null $framework
     */
    public function setFramework(?string $framework): void
    {
        $this->framework = $framework;
    }

    /**
     * @return string|null
     */
    public function getExsID(): ?string
    {
        return $this->exsid;
    }

    /**
     * @param string|null $exsid
     */
    public function setExsID(?string $exsid): void
    {
        $this->exsid = $exsid;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    public function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @param bool $hasError
     */
    public function setHasError(bool $hasError): void
    {
        $this->hasError = $hasError;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     */
    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return bool|Version
     */
    public function isUpdateAvailable()
    {
        return $this->updateAvailable;
    }

    /**
     * @param bool|Version $updateAvailable
     */
    public function setUpdateAvailable($updateAvailable): void
    {
        $this->updateAvailable = $updateAvailable;
    }

    /**
     * @return string|null
     */
    public function getUpdateFromDir(): ?string
    {
        return $this->updateFromDir;
    }

    /**
     * @param string|null $updateFromDir
     */
    public function setUpdateFromDir(?string $updateFromDir): void
    {
        $this->updateFromDir = $updateFromDir;
    }

    /**
     * @return DateTime|null
     */
    public function getDateInstalled(): ?DateTime
    {
        return $this->dateInstalled;
    }

    /**
     * @param DateTime|null $dateInstalled
     */
    public function setDateInstalled(?DateTime $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    /**
     * @return int
     */
    public function getLangVarCount(): int
    {
        return $this->langVarCount;
    }

    /**
     * @param int $langVarCount
     */
    public function setLangVarCount(int $langVarCount): void
    {
        $this->langVarCount = $langVarCount;
    }

    /**
     * @return bool
     */
    public function hasLicenseCheck(): bool
    {
        return $this->hasLicenseCheck;
    }

    /**
     * @param bool $hasLicenseCheck
     */
    public function setHasLicenseCheck(bool $hasLicenseCheck): void
    {
        $this->hasLicenseCheck = $hasLicenseCheck;
    }

    /**
     * @return string
     */
    public function getLicenseKey(): string
    {
        return $this->license;
    }

    /**
     * @param string $license
     */
    public function setLicenseKey(string $license): void
    {
        $this->license = $license;
    }

    /**
     * @return int
     */
    public function getLinkCount(): int
    {
        return $this->linkCount;
    }

    /**
     * @param int $linkCount
     */
    public function setLinkCount(int $linkCount): void
    {
        $this->linkCount = $linkCount;
    }

    /**
     * @return int
     */
    public function getOptionsCount(): int
    {
        return $this->optionsCount;
    }

    /**
     * @param int $optionsCount
     */
    public function setOptionsCount(int $optionsCount): void
    {
        $this->optionsCount = $optionsCount;
    }

    /**
     * @return string|null
     */
    public function getReadmeMD(): ?string
    {
        return $this->readmeMD;
    }

    /**
     * @param string|null $readmeMD
     */
    public function setReadmeMD(?string $readmeMD): void
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
     * @param string|null $licenseMD
     */
    public function setLicenseMD(?string $licenseMD): void
    {
        $this->licenseMD = $licenseMD;
    }

    /**
     * @return Version
     */
    public function getMaxShopVersion(): Version
    {
        return $this->maxShopVersion;
    }

    /**
     * @param Version $maxShopVersion
     */
    public function setMaxShopVersion(Version $maxShopVersion): void
    {
        $this->maxShopVersion = $maxShopVersion;
    }

    /**
     * @return Version
     */
    public function getMinShopVersion(): Version
    {
        return $this->minShopVersion;
    }

    /**
     * @param Version $minShopVersion
     */
    public function setMinShopVersion(Version $minShopVersion): void
    {
        $this->minShopVersion = $minShopVersion;
    }

    /**
     * @return string
     */
    public function displayVersionRange(): string
    {
        $min = null;
        $max = null;
        if ($this->minShopVersion !== null && $this->minShopVersion->greaterThan('0.0.0')) {
            $min = (string)$this->minShopVersion;
        }
        if ($this->maxShopVersion !== null && $this->maxShopVersion->greaterThan('0.0.0')) {
            $max = (string)$this->maxShopVersion;
        }
        if ($min === null && $max !== null) {
            return '<= ' . $max;
        }
        if ($min !== null && $max === null) {
            return '>= ' . $min;
        }
        if ($min !== null && $max !== null) {
            return $min === $max ? $min : $min . ' &dash; ' . $max;
        }

        return '?';
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setURL(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function isChild(): bool
    {
        return $this->isChild;
    }

    /**
     * @param bool $isChild
     */
    public function setIsChild(bool $isChild): void
    {
        $this->isChild = $isChild;
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * @param string|null $parent
     */
    public function setParent(?string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return array|bool|null
     */
    public function getChecksums()
    {
        return $this->checksums;
    }

    /**
     * @param array|bool|null $checksums
     */
    public function setChecksums($checksums): void
    {
        $this->checksums = $checksums;
    }

    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->isPreview;
    }

    /**
     * @param bool $isPreview
     */
    public function setIsPreview(bool $isPreview): void
    {
        $this->isPreview = $isPreview;
    }
}
