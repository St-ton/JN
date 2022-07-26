<?php

namespace JTL\Extensions\Download;

use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class History
 * @package JTL\Extensions\Download
 */
class History
{
    /**
     * @var int|null
     */
    protected ?int $kDownloadHistory = null;

    /**
     * @var int|null
     */
    protected ?int $kDownload = null;

    /**
     * @var int|null
     */
    protected ?int $kKunde = null;

    /**
     * @var int|null
     */
    protected ?int $kBestellung = null;

    /**
     * @var string|null
     */
    protected ?string $dErstellt = null;

    /**
     * History constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
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
     * @param int $id
     */
    private function loadFromDB(int $id): void
    {
        $history = Shop::Container()->getDB()->select(
            'tdownloadhistory',
            'kDownloadHistory',
            $id
        );
        if ($history !== null && $history->kDownloadHistory > 0) {
            $this->kDownload        = (int)$history->kDownload;
            $this->kDownloadHistory = (int)$history->kDownloadHistory;
            $this->kKunde           = (int)$history->kKunde;
            $this->kBestellung      = (int)$history->kBestellung;
            $this->dErstellt        = $history->dErstellt;
        }
    }

    /**
     * @param int $downloadID
     * @return array
     */
    public static function getHistory(int $downloadID): array
    {
        return Shop::Container()->getDB()->getCollection(
            'SELECT kDownloadHistory AS id 
                FROM tdownloadhistory
                WHERE kDownload = :dlid
                ORDER BY dErstellt DESC',
            ['dlid' => $downloadID]
        )->map(static function (stdClass $e): self {
            return new self((int)$e->id);
        })->toArray();
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

            $data = Shop::Container()->getDB()->getObjects(
                'SELECT kDownload, kDownloadHistory
                     FROM tdownloadhistory
                     WHERE ' . $where . '
                     ORDER BY dErstellt DESC'
            );
            foreach ($data as $item) {
                $item->kDownload = (int)$item->kDownload;
                if (!isset($history[$item->kDownload]) || !\is_array($history[$item->kDownload])) {
                    $history[$item->kDownload] = [];
                }
                $history[$item->kDownload][] = new self((int)$item->kDownloadHistory);
            }
        }

        return $history;
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = false)
    {
        $ins = $this->kopiereMembers();
        unset($ins->kDownloadHistory);

        $historyID = Shop::Container()->getDB()->insert('tdownloadhistory', $ins);
        if ($historyID > 0) {
            return $primary ? $historyID : true;
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
            $this->kDownloadHistory,
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
        $obj = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $obj->$member = $this->$member;
        }

        return $obj;
    }
}
