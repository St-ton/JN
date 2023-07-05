<?php

namespace JTL\Catalog\Product;

use DateTime;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Preisverlauf
 * @package JTL\Catalog\Product
 */
class Preisverlauf
{
    /**
     * @var int
     */
    public $kPreisverlauf;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var float
     */
    public $fPreisPrivat;

    /**
     * @var float
     */
    public $fPreisHaendler;

    /**
     * @var string
     */
    public $dDate;

    /**
     * @param int                    $id
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     */
    public function __construct(int $id = 0, private ?DbInterface $db = null, private ?JTLCacheInterface $cache = null)
    {
        $this->db    = $this->db ?? Shop::Container()->getDB();
        $this->cache = $this->cache ?? Shop::Container()->getCache();
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $productID
     * @param int $customerGroupID
     * @param int $month
     * @return array
     */
    public function gibPreisverlauf(int $productID, int $customerGroupID, int $month): array
    {
        $cacheID = 'gpv_' . $productID . '_' . $customerGroupID . '_' . $month;
        if (($data = $this->cache->get($cacheID)) === false) {
            $data     = $this->db->getObjects(
                'SELECT tpreisverlauf.fVKNetto, tartikel.fMwst, UNIX_TIMESTAMP(tpreisverlauf.dDate) AS timestamp
                    FROM tpreisverlauf 
                    JOIN tartikel
                        ON tartikel.kArtikel = tpreisverlauf.kArtikel
                    WHERE tpreisverlauf.kArtikel = :pid
                        AND tpreisverlauf.kKundengruppe = :cgid
                        AND DATE_SUB(NOW(), INTERVAL :mnth MONTH) < tpreisverlauf.dDate
                    ORDER BY tpreisverlauf.dDate DESC',
                ['pid' => $productID, 'cgid' => $customerGroupID, 'mnth' => $month]
            );
            $currency = Frontend::getCurrency();
            $dt       = new DateTime();
            $merchant = Frontend::getCustomerGroup()->isMerchant();
            foreach ($data as $pv) {
                if (!isset($pv->timestamp)) {
                    continue;
                }
                $dt->setTimestamp((int)$pv->timestamp);
                $pv->date     = $dt->format('d.m.Y');
                $pv->fPreis   = $merchant
                    ? \round($pv->fVKNetto * $currency->getConversionFactor(), 2)
                    : Tax::getGross($pv->fVKNetto * $currency->getConversionFactor(), $pv->fMwst);
                $pv->currency = $currency->getCode();
            }
            $this->cache->set(
                $cacheID,
                $data,
                [\CACHING_GROUP_ARTICLE, \CACHING_GROUP_ARTICLE . '_' . $productID]
            );
        }

        return $data;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $item = $this->db->select('tpreisverlauf', 'kPreisverlauf', $id);
        if ($item !== null) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kPreisverlauf = (int)$this->kPreisverlauf;
            $this->kArtikel      = (int)$this->kArtikel;
            $this->kKundengruppe = (int)$this->kKundengruppe;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $ins = GeneralObject::copyMembers($this);
        unset($ins->kPreisverlauf);
        $this->kPreisverlauf = $this->db->insert('tpreisverlauf', $ins);

        return $this->kPreisverlauf;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $upd = GeneralObject::copyMembers($this);

        return $this->db->update('tpreisverlauf', 'kPreisverlauf', $upd->kPreisverlauf, $upd);
    }
}
