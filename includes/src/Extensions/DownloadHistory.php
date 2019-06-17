<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\DB\ReturnType;
use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class DownloadHistory
 * @package JTL\Extensions
 */
class DownloadHistory
{
    /**
     * @var int
     */
    protected $kDownloadHistory;

    /**
     * @var int
     */
    protected $kDownload;

    /**
     * @var int
     */
    protected $kKunde;

    /**
     * @var int
     */
    protected $kBestellung;

    /**
     * @var string
     */
    protected $dErstellt;

    /**
     * @param int $kDownloadHistory
     */
    public function __construct(int $kDownloadHistory = 0)
    {
        if ($kDownloadHistory > 0) {
            $this->loadFromDB($kDownloadHistory);
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
     * @param int $kDownloadHistory
     */
    private function loadFromDB(int $kDownloadHistory): void
    {
        $history = Shop::Container()->getDB()->select(
            'tdownloadhistory',
            'kDownloadHistory',
            $kDownloadHistory
        );
        if ($history !== null && (int)$history->kDownloadHistory > 0) {
            $members = \array_keys(\get_object_vars($history));
            if (\is_array($members) && \count($members) > 0) {
                foreach ($members as $member) {
                    $this->$member = $history->$member;
                }
                $this->kDownload        = (int)$this->kDownload;
                $this->kDownloadHistory = (int)$this->kDownloadHistory;
                $this->kKunde           = (int)$this->kKunde;
                $this->kBestellung      = (int)$this->kBestellung;
            }
        }
    }

    /**
     * @param int $kDownload
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getHistorys(int $kDownload): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::getHistory($kDownload);
    }

    /**
     * @param int $kDownload
     * @return array
     */
    public static function getHistory(int $kDownload): array
    {
        $history = [];
        if ($kDownload > 0) {
            $data = Shop::Container()->getDB()->selectAll(
                'tdownloadhistory',
                'kDownload',
                $kDownload,
                'kDownloadHistory',
                'dErstellt DESC'
            );
            foreach ($data as $item) {
                $history[] = new self((int)$item->kDownloadHistory);
            }
        }

        return $history;
    }

    /**
     * @param int $customerID
     * @param int $orderID
     * @return array
     */
    public static function getOrderHistory(int $customerID, int $orderID = 0): array
    {
        $history = [];
        if ($orderID > 0 || $customerID > 0) {
            $where = 'kBestellung = ' . $orderID;
            if ($orderID > 0) {
                $where .= ' AND kKunde = ' . $customerID;
            }

            $data = Shop::Container()->getDB()->query(
                'SELECT kDownload, kDownloadHistory
                     FROM tdownloadhistory
                     WHERE ' . $where . '
                     ORDER BY dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($data as $item) {
                if (!isset($history[$item->kDownload]) || !\is_array($history[$item->kDownload])) {
                    $history[$item->kDownload] = [];
                }
                $history[$item->kDownload][] = new self((int)$item->kDownloadHistory);
            }
        }

        return $history;
    }

    /**
     * @param bool $bPrimary
     * @return bool|int
     */
    public function save(bool $bPrimary = false)
    {
        $ins = $this->kopiereMembers();
        unset($ins->kDownloadHistory);

        $historyID = Shop::Container()->getDB()->insert('tdownloadhistory', $ins);
        if ($historyID > 0) {
            return $bPrimary ? $historyID : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd              = new stdClass();
        $upd->kDownload   = $this->kDownload;
        $upd->kKunde      = $this->kKunde;
        $upd->kBestellung = $this->kBestellung;
        $upd->dErstellt   = $this->dErstellt;

        return Shop::Container()->getDB()->update(
            'tdownloadhistory',
            'kDownloadHistory',
            (int)$this->kDownloadHistory,
            $upd
        );
    }

    /**
     * @param int $kDownloadHistory
     * @return $this
     */
    public function setDownloadHistory(int $kDownloadHistory): self
    {
        $this->kDownloadHistory = $kDownloadHistory;

        return $this;
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
     * @param int $customerID
     * @return $this
     */
    public function setKunde(int $customerID): self
    {
        $this->kKunde = $customerID;

        return $this;
    }

    /**
     * @param int $orderID
     * @return $this
     */
    public function setBestellung(int $orderID): self
    {
        $this->kBestellung = $orderID;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = $dErstellt;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownloadHistory(): int
    {
        return (int)$this->kDownloadHistory;
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
    public function getKunde(): int
    {
        return (int)$this->kKunde;
    }

    /**
     * @return int
     */
    public function getBestellung(): int
    {
        return (int)$this->kBestellung;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @return stdClass
     */
    private function kopiereMembers(): stdClass
    {
        $obj     = new stdClass();
        $members = \array_keys(\get_object_vars($this));
        if (\is_array($members) && \count($members) > 0) {
            foreach ($members as $member) {
                $obj->$member = $this->$member;
            }
        }

        return $obj;
    }
}
