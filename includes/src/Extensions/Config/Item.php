<?php declare(strict_types=1);

namespace JTL\Extensions\Config;

use JsonSerializable;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\select;

/**
 * Class Item
 * @package JTL\Extensions\Config
 */
class Item implements JsonSerializable
{
    /**
     * @var int|null
     */
    protected ?int $kKonfigitem = null;

    /**
     * @var int
     */
    protected int $kArtikel = 0;

    /**
     * @var int
     */
    protected int $nPosTyp = 0;

    /**
     * @var int
     */
    protected int $kKonfiggruppe = 0;

    /**
     * @var int
     */
    protected int $bSelektiert = 0;

    /**
     * @var int
     */
    protected int $bEmpfohlen = 0;

    /**
     * @var int
     */
    protected int $bPreis = 0;

    /**
     * @var int
     */
    protected int $bName = 0;

    /**
     * @var int
     */
    protected int $bRabatt = 0;

    /**
     * @var int
     */
    protected int $bZuschlag = 0;

    /**
     * @var int
     */
    protected int $bIgnoreMultiplier = 0;

    /**
     * @var float|null
     */
    protected $fMin = null;

    /**
     * @var float|null
     */
    protected $fMax = null;

    /**
     * @var float|null
     */
    protected $fInitial = null;

    /**
     * @var ItemLocalization|null
     */
    protected ?ItemLocalization $oSprache = null;

    /**
     * @var ItemPrice|null
     */
    protected ?ItemPrice $oPreis = null;

    /**
     * @var Artikel|null
     */
    protected ?Artikel $oArtikel = null;

    /**
     * @var int
     */
    protected int $kSprache = 0;

    /**
     * @var int
     */
    protected int $kKundengruppe = 0;

    /**
     * @var int
     */
    protected int $nSort = 0;

    /**
     * @var int|float|null
     */
    public $fAnzahl;

    /**
     * @var int|null
     */
    public $fAnzahlWK;

    /**
     * @var bool
     */
    public bool $bAktiv = false;

    /**
     * @var array|null
     */
    public ?array $oEigenschaftwerte_arr = null;

    /**
     * Item constructor.
     * @param int $id
     * @param int $languageID
     * @param int $customerGroupID
     */
    public function __construct(int $id = 0, int $languageID = 0, int $customerGroupID = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id, $languageID, $customerGroupID);
        }
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return select(\array_keys(\get_object_vars($this)), static function ($e): bool {
            return $e !== 'oArtikel';
        });
    }

    /**
     *
     */
    public function __wakeup()
    {
        if ($this->kArtikel > 0) {
            $this->addProduct($this->kKundengruppe, $this->kSprache);
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
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $shortDescription = $this->getKurzBeschreibung();
        $virtual          = ['bAktiv' => $this->bAktiv];
        $override         = [
            'kKonfigitem'   => $this->getKonfigitem(),
            'cName'         => $this->getName(),
            'kArtikel'      => $this->getArtikelKey(),
            'cBeschreibung' => !empty($shortDescription)
                ? $shortDescription
                : $this->getBeschreibung(),

            'bAnzahl'         => $this->getMin() != $this->getMax(),
            'fInitial'        => (float)$this->getInitial(),
            'fMin'            => (float)$this->getMin(),
            'fMax'            => (float)$this->getMax(),
            'cBildPfad'       => $this->getBildPfad(),
            'fPreis'          => [
                (float)$this->getPreis(),
                (float)$this->getPreis(true)
            ],
            'fPreisLocalized' => [
                Preise::getLocalizedPriceString($this->getPreis()),
                Preise::getLocalizedPriceString($this->getPreis(true))
            ]
        ];

        return Text::utf8_convert_recursive(\array_merge($override, $virtual));
    }

    /**
     * Loads database member into class member
     *
     * @param int $id
     * @param int $languageID
     * @param int $customerGroupID
     * @return $this
     */
    private function loadFromDB(int $id = 0, int $languageID = 0, int $customerGroupID = 0): self
    {
        if (!self::checkLicense()) {
            return $this;
        }
        $item = Shop::Container()->getDB()->select('tkonfigitem', 'kKonfigitem', $id);
        if ($item !== null && $item->kKonfigitem > 0) {
            if (!$languageID) {
                $languageID = Shop::getLanguageID() ?? LanguageHelper::getDefaultLanguage()->kSprache;
            }
            if (!$customerGroupID) {
                $customerGroupID = Frontend::getCustomerGroup()->getID();
            }

            return $this->assignData($item, $languageID, $customerGroupID);
        }

        return $this;
    }

    /**
     * @param object $item
     * @param int    $languageID
     * @param int    $customerGroupID
     * @return $this
     */
    public function assignData(object $item, int $languageID, int $customerGroupID): self
    {
        if ($item->kKonfigitem > 0) {
            $this->kKonfiggruppe     = (int)$item->kKonfiggruppe;
            $this->kKonfigitem       = (int)$item->kKonfigitem;
            $this->kArtikel          = (int)$item->kArtikel;
            $this->nPosTyp           = (int)$item->nPosTyp;
            $this->bSelektiert       = (int)$item->bSelektiert;
            $this->bEmpfohlen        = (int)$item->bEmpfohlen;
            $this->bName             = (int)$item->bName;
            $this->bPreis            = (int)$item->bPreis;
            $this->bRabatt           = (int)$item->bRabatt;
            $this->bZuschlag         = (int)$item->bZuschlag;
            $this->bIgnoreMultiplier = (int)$item->bIgnoreMultiplier;
            $this->fMin              = $item->fMin;
            $this->fMax              = $item->fMax;
            $this->fInitial          = $item->fInitial;
            $this->nSort             = (int)$item->nSort;
            $this->kSprache          = $languageID;
            $this->kKundengruppe     = $customerGroupID;
            $this->oSprache          = new ItemLocalization($this->kKonfigitem, $languageID);
            $this->oPreis            = new ItemPrice($this->kKonfigitem, $customerGroupID);
            $this->oArtikel          = null;
            if ($this->kArtikel > 0) {
                $this->addProduct($customerGroupID, $languageID);
            }
        }

        return $this;
    }

    /**
     * @param int $customerGroupID
     * @param int $languageID
     */
    private function addProduct(int $customerGroupID, int $languageID): void
    {
        $options                             = Artikel::getDefaultOptions();
        $options->nKeineSichtbarkeitBeachten = 1;

        $this->oArtikel = new Artikel();
        $this->oArtikel->fuelleArtikel($this->kArtikel, $options, $customerGroupID, $languageID);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !($this->kArtikel > 0 && empty($this->oArtikel->kArtikel));
    }

    /**
     * @param int $groupID
     * @param int $languageID
     * @return Item[]
     */
    public static function fetchAll(int $groupID, int $languageID = 0): array
    {
        $customerGroupID = Frontend::getCustomerGroup()->getID();

        return Shop::Container()->getDB()->getCollection(
            'SELECT *
                FROM tkonfigitem 
                WHERE kKonfiggruppe = :groupID 
                ORDER BY nSort ASC',
            ['groupID' => $groupID]
        )
            ->map(static function (stdClass $item) use ($languageID, $customerGroupID): self {
                return (new self())->assignData($item, $languageID, $customerGroupID);
            })
            ->filter(static function (Item $item): bool {
                return $item->isValid();
            })
            ->toArray();
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setKonfigitem(int $id): self
    {
        $this->kKonfigitem = $id;

        return $this;
    }

    /**
     * @param int $productID
     * @return $this
     */
    public function setArtikelKey(int $productID): self
    {
        $this->kArtikel = $productID;

        return $this;
    }

    /**
     * @param Artikel $product
     * @return $this
     */
    public function setArtikel(Artikel $product): self
    {
        $this->oArtikel = $product;

        return $this;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setPosTyp(int $type): self
    {
        $this->nPosTyp = $type;

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
    public function getKonfiggruppe(): int
    {
        return $this->kKonfiggruppe;
    }

    /**
     * @return int
     */
    public function getArtikelKey(): int
    {
        return $this->kArtikel;
    }

    /**
     * @return Artikel|null
     */
    public function getArtikel(): ?Artikel
    {
        return $this->oArtikel;
    }

    /**
     * @return int|null
     */
    public function getPosTyp(): ?int
    {
        return $this->nPosTyp;
    }

    /**
     * @return int|null
     */
    public function getSelektiert(): ?int
    {
        return $this->bSelektiert;
    }

    /**
     * @return int|null
     */
    public function getEmpfohlen(): ?int
    {
        return $this->bEmpfohlen;
    }

    /**
     * @return ItemLocalization|null
     */
    public function getSprache(): ?ItemLocalization
    {
        return $this->oSprache;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        if ($this->oArtikel && $this->bName) {
            return $this->oArtikel->cName;
        }

        return $this->oSprache
            ? $this->oSprache->getName()
            : '';
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): ?string
    {
        if ($this->oArtikel && $this->bName) {
            return $this->oArtikel->cBeschreibung;
        }

        return $this->oSprache
            ? $this->oSprache->getBeschreibung()
            : '';
    }

    /**
     * @return string|null
     */
    public function getKurzBeschreibung(): ?string
    {
        if ($this->oArtikel && $this->bName) {
            return $this->oArtikel->cKurzBeschreibung;
        }

        return $this->oSprache
            ? $this->oSprache->getBeschreibung()
            : '';
    }

    /**
     * @return string|null
     */
    public function getBildPfad(): ?string
    {
        return $this->oArtikel && $this->oArtikel->Bilder[0]->cPfadKlein !== \BILD_KEIN_ARTIKELBILD_VORHANDEN
            ? $this->oArtikel->Bilder[0]->cPfadKlein
            : null;
    }

    /**
     * @return bool
     */
    public function getUseOwnName(): bool
    {
        return !$this->bName;
    }

    /**
     * @param bool $forceNet
     * @param bool $convertCurrency
     * @return float|int
     */
    public function getPreis(bool $forceNet = false, bool $convertCurrency = false)
    {
        $fVKPreis    = 0.0;
        $isConverted = false;
        if ($this->oArtikel && $this->bPreis) {
            $fVKPreis = $this->oArtikel->Preise->fVKNetto ?? 0;
            $fSpecial = $this->oPreis->getPreis($convertCurrency);
            if ($fSpecial != 0) {
                if ($this->oPreis->getTyp() === ItemPrice::PRICE_TYPE_SUM) {
                    $fVKPreis += $fSpecial;
                } elseif ($this->oPreis->getTyp() === ItemPrice::PRICE_TYPE_PERCENTAGE) {
                    $fVKPreis *= (100 + $fSpecial) / 100;
                }
            }
        } elseif ($this->oPreis) {
            $fVKPreis    = $this->oPreis->getPreis($convertCurrency);
            $isConverted = true;
        }
        if ($convertCurrency && !$isConverted) {
            $fVKPreis *= Frontend::getCurrency()->getConversionFactor();
        }
        if (!$forceNet && !Frontend::getCustomerGroup()->isMerchant()) {
            $fVKPreis = Tax::getGross($fVKPreis, Tax::getSalesTax($this->getSteuerklasse()), 4);
        }

        return $fVKPreis;
    }

    /**
     * @param bool $forceNet
     * @param bool $convertCurrency
     * @param int $totalAmount
     * @return float|int
     */
    public function getFullPrice(bool $forceNet = false, bool $convertCurrency = false, $totalAmount = 1)
    {
        return $this->getPreis($forceNet, $convertCurrency) * $this->fAnzahl * $totalAmount;
    }

    /**
     * @return bool
     */
    public function hasPreis(): bool
    {
        return $this->getPreis(true) != 0;
    }

    /**
     * @return bool
     */
    public function hasRabatt(): bool
    {
        return $this->getRabatt() > 0;
    }

    /**
     * @return float
     */
    public function getRabatt(): float
    {
        $discount = 0.0;
        if ($this->oArtikel && $this->bPreis) {
            $tmp = $this->oPreis->getPreis();
            if ($tmp < 0) {
                $discount = $tmp * -1;
                if ($this->oPreis->getTyp() === ItemPrice::PRICE_TYPE_SUM
                    && !Frontend::getCustomerGroup()->isMerchant()
                ) {
                    $discount = Tax::getGross($discount, Tax::getSalesTax($this->getSteuerklasse()));
                }
            }
        }

        return $discount;
    }

    /**
     * @return bool
     */
    public function hasZuschlag(): bool
    {
        return $this->getZuschlag() > 0;
    }

    /**
     * @return float
     */
    public function getZuschlag(): float
    {
        $fee = 0.0;
        if ($this->oArtikel && $this->bPreis) {
            $tmp = $this->oPreis->getPreis();
            if ($tmp > 0) {
                $fee = $tmp;
                if ($this->oPreis->getTyp() === ItemPrice::PRICE_TYPE_SUM
                    && !Frontend::getCustomerGroup()->isMerchant()
                ) {
                    $fee = Tax::getGross($fee, Tax::getSalesTax($this->getSteuerklasse()));
                }
            }
        }

        return $fee;
    }

    /**
     * @param bool $html
     * @return string
     */
    public function getRabattLocalized(bool $html = true): string
    {
        return $this->oPreis->getTyp() === ItemPrice::PRICE_TYPE_SUM
            ? Preise::getLocalizedPriceString($this->getRabatt(), null, $html)
            : $this->getRabatt() . '%';
    }

    /**
     * @param bool $html
     * @return string
     */
    public function getZuschlagLocalized(bool $html = true): string
    {
        return $this->oPreis->getTyp() === ItemPrice::PRICE_TYPE_SUM
            ? Preise::getLocalizedPriceString($this->getZuschlag(), null, $html)
            : $this->getZuschlag() . '%';
    }

    /**
     * @return int
     */
    public function getSteuerklasse(): int
    {
        $kSteuerklasse = 0;
        if ($this->oArtikel && $this->bPreis) {
            $kSteuerklasse = $this->oArtikel->kSteuerklasse;
        } elseif ($this->oPreis) {
            $kSteuerklasse = $this->oPreis->getSteuerklasse();
        }

        return $kSteuerklasse;
    }

    /**
     * @param bool $html
     * @param bool $signed
     * @param bool $bForceNetto
     * @return string
     */
    public function getPreisLocalized(bool $html = true, bool $signed = true, bool $bForceNetto = false): string
    {
        $localized = Preise::getLocalizedPriceString($this->getPreis($bForceNetto), false, $html);
        if ($signed && $this->getPreis() > 0) {
            $localized = '+' . $localized;
        }

        return $localized;
    }

    /**
     * @param bool $html
     * @param bool $forceNet
     * @param int  $totalAmount
     * @return string
     */
    public function getFullPriceLocalized(bool $html = true, bool $forceNet = false, $totalAmount = 1): string
    {
        return Preise::getLocalizedPriceString($this->getFullPrice($forceNet, false, $totalAmount), 0, $html);
    }

    /**
     * @return float|null
     */
    public function getMin()
    {
        return $this->fMin;
    }

    /**
     * @return float|null
     */
    public function getMax()
    {
        return $this->fMax;
    }

    /**
     * @return float|int
     */
    public function getInitial()
    {
        if ($this->fInitial < 0) {
            $this->fInitial = 0;
        }
        if ($this->fInitial < $this->getMin()) {
            $this->fInitial = $this->getMin();
        }
        if ($this->fInitial > $this->getMax()) {
            $this->fInitial = $this->getMax();
        }

        return $this->fInitial;
    }

    /**
     * @return int|null
     */
    public function showRabatt(): ?int
    {
        return $this->bRabatt;
    }

    /**
     * @return int|null
     */
    public function showZuschlag(): ?int
    {
        return $this->bZuschlag;
    }

    /**
     * @return int|null
     */
    public function ignoreMultiplier(): ?int
    {
        return $this->bIgnoreMultiplier;
    }

    /**
     * @return int|null
     */
    public function getSprachKey(): ?int
    {
        return $this->kSprache;
    }

    /**
     * @return int|null
     */
    public function getKundengruppe(): ?int
    {
        return $this->kKundengruppe;
    }

    /**
     * @return bool
     */
    public function isInStock(): bool
    {
        $tmpPro = $this->getArtikel();
        if ($tmpPro === null) {
            return true;
        }

        return empty($this->kArtikel)
            || (!($tmpPro->cLagerBeachten === 'Y'
                && $tmpPro->cLagerKleinerNull === 'N'
                && (float)$tmpPro->fLagerbestand < $this->fMin));
    }
}
