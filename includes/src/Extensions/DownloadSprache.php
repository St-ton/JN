<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

/**
 * Class DownloadSprache
 *
 * @package Extensions
 */
class DownloadSprache
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
     * @param int $kDownload
     * @param int $kSprache
     */
    public function __construct(int $kDownload = 0, int $kSprache = 0)
    {
        if ($kDownload > 0 && $kSprache > 0) {
            $this->loadFromDB($kDownload, $kSprache);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return \Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_DOWNLOADS);
    }

    /**
     * @param int $kDownload
     * @param int $kSprache
     */
    private function loadFromDB(int $kDownload, int $kSprache): void
    {
        $oDownloadSprache = \Shop::Container()->getDB()->select(
            'tdownloadsprache',
            'kDownload',
            $kDownload,
            'kSprache',
            $kSprache
        );
        if ($oDownloadSprache !== null && (int)$oDownloadSprache->kDownload > 0) {
            foreach (\array_keys(\get_object_vars($oDownloadSprache)) as $member) {
                $this->$member = $oDownloadSprache->$member;
            }
            $this->kSprache  = (int)$this->kSprache;
            $this->kDownload = (int)$this->kDownload;
        }
    }

    /**
     * @param bool $bPrimary
     * @return bool|int
     */
    public function save(bool $bPrimary = false)
    {
        $oObj      = $this->kopiereMembers();
        $kDownload = \Shop::Container()->getDB()->insert('tdownloadsprache', $oObj);
        if ($kDownload > 0) {
            return $bPrimary ? $kDownload : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                = new \stdClass();
        $upd->cName         = $this->getName();
        $upd->cBeschreibung = $this->getBeschreibung();

        return \Shop::Container()->getDB()->update(
            'tdownloadsprache',
            ['kDownload', 'kSprache'],
            [$this->getDownload(), $this->getSprache()],
            $upd
        );
    }

    /**
     * @param int $kDownload
     * @return $this
     */
    public function setDownload(int $kDownload): self
    {
        $this->kDownload = $kDownload;

        return $this;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache(int $kSprache): self
    {
        $this->kSprache = $kSprache;

        return $this;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName($cName): self
    {
        $this->cName = $cName;

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
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung()
    {
        return $this->cBeschreibung;
    }

    /**
     * @return \stdClass
     */
    private function kopiereMembers(): \stdClass
    {
        $obj = new \stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $obj->$member = $this->$member;
        }

        return $obj;
    }
}
