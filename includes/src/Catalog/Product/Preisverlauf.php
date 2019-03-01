<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Product;

use DateTime;
use JTL\DB\ReturnType;
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
     * Preisverlauf constructor.
     *
     * @param int $kPreisverlauf
     */
    public function __construct(int $kPreisverlauf = 0)
    {
        if ($kPreisverlauf > 0) {
            $this->loadFromDB($kPreisverlauf);
        }
    }

    /**
     * @param int $kArtikel
     * @param int $kKundengruppe
     * @param int $nMonat
     * @return mixed
     */
    public function gibPreisverlauf(int $kArtikel, int $kKundengruppe, int $nMonat)
    {
        $cacheID = 'gpv_' . $kArtikel . '_' . $kKundengruppe . '_' . $nMonat;
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data     = Shop::Container()->getDB()->query(
                'SELECT tpreisverlauf.fVKNetto, tartikel.fMwst, UNIX_TIMESTAMP(tpreisverlauf.dDate) AS timestamp
                    FROM tpreisverlauf 
                    LEFT JOIN tartikel
                        ON tartikel.kArtikel = tpreisverlauf.kArtikel
                    WHERE tpreisverlauf.kArtikel = ' . $kArtikel . '
                        AND tpreisverlauf.kKundengruppe = ' . $kKundengruppe . '
                        AND DATE_SUB(NOW(), INTERVAL ' . $nMonat . ' MONTH) < tpreisverlauf.dDate
                    ORDER BY tpreisverlauf.dDate DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $currency = Frontend::getCurrency();
            $dt       = new DateTime();
            foreach ($data as &$pv) {
                if (isset($pv->timestamp)) {
                    $dt->setTimestamp((int)$pv->timestamp);
                    $pv->date     = $dt->format('d.m.');
                    $pv->fPreis   = Frontend::getCustomerGroup()->isMerchant()
                        ? \round($pv->fVKNetto * $currency->getConversionFactor(), 2)
                        : Tax::getGross($pv->fVKNetto * $currency->getConversionFactor(), $pv->fMwst);
                    $pv->currency = $currency->getCode();
                }
            }
            unset($pv);
            Shop::Container()->getCache()->set(
                $cacheID,
                $data,
                [\CACHING_GROUP_ARTICLE, \CACHING_GROUP_ARTICLE . '_' . $kArtikel]
            );
        }

        return $data;
    }

    /**
     * @param int $kPreisverlauf
     * @return $this
     */
    public function loadFromDB(int $kPreisverlauf): self
    {
        $item = Shop::Container()->getDB()->select('tpreisverlauf', 'kPreisverlauf', $kPreisverlauf);
        if ($item !== null) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
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
        $this->kPreisverlauf = Shop::Container()->getDB()->insert('tpreisverlauf', $ins);

        return $this->kPreisverlauf;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $upd = GeneralObject::copyMembers($this);

        return Shop::Container()->getDB()->update('tpreisverlauf', 'kPreisverlauf', $upd->kPreisverlauf, $upd);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }
}
