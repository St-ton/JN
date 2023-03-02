<?php declare(strict_types=1);

namespace JTL\Extensions\Config;

use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class ItemPrice
 * @package JTL\Extensions\Config
 */
class ItemPrice
{
    public const PRICE_TYPE_PERCENTAGE = 1;

    public const PRICE_TYPE_SUM = 0;

    /**
     * @var int|null
     */
    protected ?int $kKonfigitem = null;

    /**
     * @var int
     */
    protected int $kKundengruppe = 0;

    /**
     * @var int
     */
    protected int $kSteuerklasse = 0;

    /**
     * @var float|null
     */
    protected ?float $fPreis = null;

    /**
     * @var int|null
     */
    protected ?int $nTyp = null;

    /**
     * ItemPrice constructor.
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

        if ($item !== null && $item->kKonfigitem > 0 && $item->kKundengruppe > 0) {
            $this->kKonfigitem   = (int)$item->kKonfigitem;
            $this->kKundengruppe = (int)$item->kKundengruppe;
            $this->kSteuerklasse = (int)$item->kSteuerklasse;
            $this->nTyp          = (int)$item->nTyp;
            $this->fPreis        = (float)$item->fPreis;
        }
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
        return $this->kKonfigitem ?? 0;
    }

    /**
     * @return int
     */
    public function getKundengruppe(): int
    {
        return $this->kKundengruppe;
    }

    /**
     * @return int
     */
    public function getSteuerklasse(): int
    {
        return $this->kSteuerklasse;
    }

    /**
     * @param bool $convertCurrency
     * @return float|null
     */
    public function getPreis(bool $convertCurrency = false)
    {
        $price = $this->fPreis;
        if ($convertCurrency && $price > 0) {
            $price *= Frontend::getCurrency()->getConversionFactor();
        }

        return $price;
    }

    /**
     * @return int|null
     */
    public function getTyp(): ?int
    {
        return $this->nTyp;
    }
}
