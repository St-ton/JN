<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class DownloadLocalization
 * @package JTL\Extensions
 */
class DownloadLocalization
{
    /**
     * @var int
     */
    protected $kDownload;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var string
     */
    protected $cBeschreibung;

    /**
     * DownloadLocalization constructor.
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
        if ($localized !== null && (int)$localized->kDownload > 0) {
            foreach (\array_keys(\get_object_vars($localized)) as $member) {
                $this->$member = $localized->$member;
            }
            $this->kSprache  = (int)$this->kSprache;
            $this->kDownload = (int)$this->kDownload;
        }
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = false)
    {
        $data = $this->kopiereMembers();
        $id   = Shop::Container()->getDB()->insert('tdownloadsprache', $data);
        if ($id > 0) {
            return $primary ? $id : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                = new stdClass();
        $upd->cName         = $this->getName();
        $upd->cBeschreibung = $this->getBeschreibung();

        return Shop::Container()->getDB()->update(
            'tdownloadsprache',
            ['kDownload', 'kSprache'],
            [$this->getDownload(), $this->getSprache()],
            $upd
        );
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
    public function setName($name): self
    {
        $this->cName = $name;

        return $this;
    }

    /**
     * @param string $cBeschreibung
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownload(): int
    {
        return (int)$this->kDownload;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return (int)$this->kSprache;
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

    /**
     * @return stdClass
     */
    private function kopiereMembers(): stdClass
    {
        $obj = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $obj->$member = $this->$member;
        }

        return $obj;
    }
}
