<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Konfigitempreis
 * @package JTL\Extensions
 */
class Konfigitempreis
{
    public const PRICE_TYPE_PERCENTAGE = 1;

    public const PRICE_TYPE_SUM = 0;

    /**
     * @var int
     */
    protected $kKonfigitem;

    /**
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @var int
     */
    protected $kSteuerklasse;

    /**
     * @var float
     */
    protected $fPreis;

    /**
     * @var int
     */
    protected $nTyp;

    /**
     * Konfigitempreis constructor.
     * @param int $configItemID
     * @param int $customerGroupID
     */
    public function __construct(int $configItemID = 0, int $customerGroupID = 0)
    {
        if ($configItemID > 0 && $customerGroupID > 0) {
            $this->loadFromDB($configItemID, $customerGroupID);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_KONFIGURATOR);
    }

    /**
     * @param int $configItemID
     * @param int $customerGroupID
     */
    private function loadFromDB(int $configItemID = 0, int $customerGroupID = 0): void
    {
        $item = Shop::Container()->getDB()->select(
            'tkonfigitempreis',
            'kKonfigitem',
            $configItemID,
            'kKundengruppe',
            $customerGroupID
        );

        if (isset($item->kKonfigitem, $item->kKundengruppe)
            && $item->kKonfigitem > 0
            && $item->kKundengruppe > 0
        ) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kKonfigitem   = (int)$this->kKonfigitem;
            $this->kKundengruppe = (int)$this->kKundengruppe;
            $this->kSteuerklasse = (int)$this->kSteuerklasse;
            $this->nTyp          = (int)$this->nTyp;
        }
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = true)
    {
        $ins = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $ins->$member = $this->$member;
        }
        unset($ins->kKonfigitem, $ins->kKundengruppe);

        $kPrim = Shop::Container()->getDB()->insert('tkonfigitempreis', $ins);

        if ($kPrim > 0) {
            return $primary ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                = new stdClass();
        $upd->kSteuerklasse = $this->getSteuerklasse();
        $upd->fPreis        = $this->fPreis;
        $upd->nTyp          = $this->getTyp();

        return Shop::Container()->getDB()->update(
            'tkonfigitempreis',
            ['kKonfigitem', 'kKundengruppe'],
            [$this->getKonfigitem(), $this->getKundengruppe()],
            $upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete(
            'tkonfigitempreis',
            ['kKonfigitem', 'kKundengruppe'],
            [(int)$this->kKonfigitem, (int)$this->kKundengruppe]
        );
    }

    /**
     * @param int $kKonfigitem
     * @return $this
     */
    public function setKonfigitem(int $kKonfigitem): self
    {
        $this->kKonfigitem = $kKonfigitem;

        return $this;
    }

    /**
     * @param int $customerGroupID
     * @return $this
     */
    public function setKundengruppe(int $customerGroupID):self
    {
        $this->kKundengruppe = $customerGroupID;

        return $this;
    }

    /**
     * @param int $kSteuerklasse
     * @return $this
     */
    public function setSteuerklasse(int $kSteuerklasse): self
    {
        $this->kSteuerklasse = $kSteuerklasse;

        return $this;
    }

    /**
     * @param float $fPreis
     * @return $this
     */
    public function setPreis($fPreis): self
    {
        $this->fPreis = (float)$fPreis;

        return $this;
    }

    /**
     * @return int
     */
    public function getKonfigitem(): int
    {
        return (int)$this->kKonfigitem;
    }

    /**
     * @return int
     */
    public function getKundengruppe(): int
    {
        return (int)$this->kKundengruppe;
    }

    /**
     * @return int
     */
    public function getSteuerklasse(): int
    {
        return (int)$this->kSteuerklasse;
    }

    /**
     * @param bool $bConvertCurrency
     * @return float|null
     */
    public function getPreis(bool $bConvertCurrency = false)
    {
        $fPreis = $this->fPreis;
        if ($bConvertCurrency && $fPreis > 0) {
            $fPreis *= Frontend::getCurrency()->getConversionFactor();
        }

        return $fPreis;
    }

    /**
     * @return int|null
     */
    public function getTyp(): ?int
    {
        return $this->nTyp;
    }
}
