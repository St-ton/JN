<?php declare(strict_types=1);

namespace JTL\Extensions\Download;

use JTL\Nice;
use JTL\Shop;

/**
 * Class Localization
 * @package JTL\Extensions\Download
 */
class Localization
{
    /**
     * @var int|null
     */
    protected ?int $kDownload = null;

    /**
     * @var int|null
     */
    protected ?int $kSprache = null;

    /**
     * @var string|null
     */
    protected ?string $cName = null;

    /**
     * @var string|null
     */
    protected ?string $cBeschreibung = null;

    /**
     * Localization constructor.
     * @param int $downloadID
     * @param int $languageID
     */
    public function __construct(int $downloadID = 0, int $languageID = 0)
    {
        if ($downloadID > 0 && $languageID > 0) {
            $this->loadFromDB($downloadID, $languageID);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_DOWNLOADS);
    }

    /**
     * @param int $downloadID
     * @param int $languageID
     */
    private function loadFromDB(int $downloadID, int $languageID): void
    {
        $localized = Shop::Container()->getDB()->select(
            'tdownloadsprache',
            'kDownload',
            $downloadID,
            'kSprache',
            $languageID
        );
        if ($localized !== null && $localized->kDownload > 0) {
            $this->kSprache      = (int)$localized->kSprache;
            $this->kDownload     = (int)$localized->kDownload;
            $this->cName         = $localized->cName;
            $this->cBeschreibung = $localized->cBeschreibung;
        }
    }

    /**
     * @param int $downloadID
     * @return $this
     */
    public function setDownload(int $downloadID): self
    {
        $this->kDownload = $downloadID;

        return $this;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setSprache(int $languageID): self
    {
        $this->kSprache = $languageID;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->cName = $name;

        return $this;
    }

    /**
     * @param string $decription
     * @return $this
     */
    public function setBeschreibung(string $decription): self
    {
        $this->cBeschreibung = $decription;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownload(): int
    {
        return $this->kDownload ?? 0;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return $this->kSprache ?? 0;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): ?string
    {
        return $this->cBeschreibung;
    }
}
