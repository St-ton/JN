<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JsonSerializable;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DB\ReturnType;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Konfigitem
 * @package JTL\Extensions
 */
class Konfigitem implements JsonSerializable
{
    /**
     * @var int
     */
    protected $kKonfigitem;

    /**
     * @var int
     */
    protected $kArtikel;

    /**
     * @var int
     */
    protected $nPosTyp;

    /**
     * @var int
     */
    protected $kKonfiggruppe;

    /**
     * @var int
     */
    protected $bSelektiert;

    /**
     * @var int
     */
    protected $bEmpfohlen;

    /**
     * @var int
     */
    protected $bPreis;

    /**
     * @var int
     */
    protected $bName;

    /**
     * @var int
     */
    protected $bRabatt;

    /**
     * @var int
     */
    protected $bZuschlag;

    /**
     * @var int
     */
    protected $bIgnoreMultiplier;

    /**
     * @var float
     */
    protected $fMin;

    /**
     * @var float
     */
    protected $fMax;

    /**
     * @var float
     */
    protected $fInitial;

    /**
     * @var Konfigitemsprache
     */
    protected $oSprache;

    /**
     * @var Konfigitempreis
     */
    protected $oPreis;

    /**
     * @var Artikel
     */
    protected $oArtikel;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @var int
     */
    protected $nSort = 0;

    /**
     * @var int|null
     */
    public $fAnzahl;

    /**
     * @var int|null
     */
    public $fAnzahlWK;

    /**
     * @var bool|null
     */
    public $bAktiv;

    /**
     * Constructor
     *
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
        $cKurzBeschreibung = $this->getKurzBeschreibung();
        $virtual           = [
            'bAktiv' => $this->bAktiv
        ];
        $override          = [
            'kKonfigitem'       => $this->getKonfigitem(),
            'cName'             => $this->getName(),
            'kArtikel'          => $this->getArtikelKey(),
            'cBeschreibung'     => !empty($cKurzBeschreibung)
                ? $this->getKurzBeschreibung()
                : $this->getBeschreibung(),

            'bAnzahl'           => $this->getMin() != $this->getMax(),
            'fInitial'          => (float)$this->getInitial(),
            'fMin'              => (float)$this->getMin(),
            'fMax'              => (float)$this->getMax(),
            'cBildPfad'         => $this->getBildPfad(),
            'fPreis'            => [
                (float)$this->getPreis(),
                (float)$this->getPreis(true)
            ],
            'fPreisLocalized' => [
                Preise::getLocalizedPriceString($this->getPreis()),
                Preise::getLocalizedPriceString($this->getPreis(true))
            ]
        ];
        $result            = \array_merge($override, $virtual);

        return Text::utf8_convert_recursive($result);
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
        if (isset($item->kKonfigitem) && $item->kKonfigitem > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }

            if (!$languageID) {
                $languageID = Shop::getLanguageID() ?? LanguageHelper::getDefaultLanguage()->kSprache;
            }
            if (!$customerGroupID) {
                $customerGroupID = Frontend::getCustomerGroup()->getID();
            }
            $this->kKonfiggruppe     = (int)$this->kKonfiggruppe;
            $this->kKonfigitem       = (int)$this->kKonfigitem;
            $this->kArtikel          = (int)$this->kArtikel;
            $this->nPosTyp           = (int)$this->nPosTyp;
            $this->nSort             = (int)$this->nSort;
            $this->bSelektiert       = (int)$this->bSelektiert;
            $this->bEmpfohlen        = (int)$this->bEmpfohlen;
            $this->bName             = (int)$this->bName;
            $this->bPreis            = (int)$this->bPreis;
            $this->bRabatt           = (int)$this->bRabatt;
            $this->bZuschlag         = (int)$this->bZuschlag;
            $this->bIgnoreMultiplier = (int)$this->bIgnoreMultiplier;
            $this->kSprache          = $languageID;
            $this->kKundengruppe     = $customerGroupID;
            $this->oSprache          = new Konfigitemsprache($this->kKonfigitem, $languageID);
            $this->oPreis            = new Konfigitempreis($this->kKonfigitem, $customerGroupID);
            $this->oArtikel          = null;
            if ($this->kArtikel > 0) {
                $options                             = new stdClass();
                $options->nAttribute                 = 1;
                $options->nArtikelAttribute          = 1;
                $options->nVariationKombi            = 1;
                $options->nVariationKombiKinder      = 1;
                $options->nKeineSichtbarkeitBeachten = 1;
                $options->nVariationen               = 0;

                $this->oArtikel = new Artikel();
                $this->oArtikel->fuelleArtikel($this->kArtikel, $options, $customerGroupID, $languageID);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !($this->kArtikel > 0 && empty($this->oArtikel->kArtikel));
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins                    = new stdClass();
        $ins->kKonfiggruppe     = $this->kKonfiggruppe;
        $ins->kArtikel          = $this->kArtikel;
        $ins->nPosTyp           = $this->nPosTyp;
        $ins->bSelektiert       = $this->bSelektiert;
        $ins->bEmpfohlen        = $this->bEmpfohlen;
        $ins->bName             = $this->bName;
        $ins->bPreis            = $this->bPreis;
        $ins->bRabatt           = $this->bRabatt;
        $ins->bZuschlag         = $this->bZuschlag;
        $ins->bIgnoreMultiplier = $this->bIgnoreMultiplier;
        $ins->fMin              = $this->fMin;
        $ins->fMax              = $this->fMax;
        $ins->fInitial          = $this->fInitial;
        $ins->nSort             = $this->nSort;

        $kPrim = Shop::Container()->getDB()->insert('tkonfigitem', $ins);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                    = new stdClass();
        $upd->kKonfiggruppe     = $this->kKonfiggruppe;
        $upd->kArtikel          = $this->kArtikel;
        $upd->nPosTyp           = $this->nPosTyp;
        $upd->bSelektiert       = $this->bSelektiert;
        $upd->bEmpfohlen        = $this->bEmpfohlen;
        $upd->bPreis            = $this->bPreis;
        $upd->bName             = $this->bName;
        $upd->bRabatt           = $this->bRabatt;
        $upd->bZuschlag         = $this->bZuschlag;
        $upd->bIgnoreMultiplier = $this->bIgnoreMultiplier;
        $upd->fMin              = $this->fMin;
        $upd->fMax              = $this->fMax;
        $upd->fInitial          = $this->fInitial;
        $upd->nSort             = $this->nSort;

        return Shop::Container()->getDB()->update('tkonfigitem', 'kKonfigitem', (int)$this->kKonfigitem, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkonfigitem', 'kKonfigitem', (int)$this->kKonfigitem);
    }

    /**
     * @param int $kKonfiggruppe
     * @return Konfigitem[]
     */
    public static function fetchAll(int $kKonfiggruppe): array
    {
        $items = [];
        $data  = Shop::Container()->getDB()->queryPrepared(
            'SELECT kKonfigitem 
                FROM tkonfigitem 
                WHERE kKonfiggruppe = :groupID 
                ORDER BY nSort ASC',
            ['groupID' => $kKonfiggruppe],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as &$item) {
            $kKonfigitem = (int)$item->kKonfigitem;
            $item        = new self($kKonfigitem);
            if ($item->isValid()) {
                $items[] = $item;
            }
        }

        return $items;
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
     * @param int $nPosTyp
     * @return $this
     */
    public function setPosTyp(int $nPosTyp): self
    {
        $this->nPosTyp = $nPosTyp;

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
        return (int)$this->kKonfiggruppe;
    }

    /**
     * @return int
     */
    public function getArtikelKey(): int
    {
        return (int)$this->kArtikel;
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
     * @return Konfigitemsprache|null
     */
    public function getSprache(): ?Konfigitemsprache
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
                if ($this->oPreis->getTyp() === Konfigitempreis::PRICE_TYPE_SUM) {
                    $fVKPreis += $fSpecial;
                } elseif ($this->oPreis->getTyp() === Konfigitempreis::PRICE_TYPE_PERCENTAGE) {
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
                if ($this->oPreis->getTyp() === 0 && !Frontend::getCustomerGroup()->isMerchant()) {
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
                if ($this->oPreis->getTyp() == 0 && !Frontend::getCustomerGroup()->isMerchant()) {
                    $fee = Tax::getGross($fee, Tax::getSalesTax($this->getSteuerklasse()));
                }
            }
        }

        return $fee;
    }

    /**
     * @param bool $bHTML
     * @return string
     */
    public function getRabattLocalized(bool $bHTML = true): string
    {
        return $this->oPreis->getTyp() === 0
            ? Preise::getLocalizedPriceString($this->getRabatt(), null, $bHTML)
            : $this->getRabatt() . '%';
    }

    /**
     * @param bool $bHTML
     * @return string
     */
    public function getZuschlagLocalized(bool $bHTML = true): string
    {
        return $this->oPreis->getTyp() === 0
            ? Preise::getLocalizedPriceString($this->getZuschlag(), null, $bHTML)
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
     * @param bool $bHTML
     * @param bool $bSigned
     * @param bool $bForceNetto
     * @return string
     */
    public function getPreisLocalized(bool $bHTML = true, bool $bSigned = true, bool $bForceNetto = false): string
    {
        $cLocalized = Preise::getLocalizedPriceString($this->getPreis($bForceNetto), false, $bHTML);
        if ($bSigned && $this->getPreis() > 0) {
            $cLocalized = '+' . $cLocalized;
        }

        return $cLocalized;
    }

    /**
     * @param bool $bHTML
     * @param bool $bForceNetto
     * @param int $totalAmount
     * @return string
     */
    public function getFullPriceLocalized(bool $bHTML = true, bool $bForceNetto = false, $totalAmount = 1): string
    {
        return Preise::getLocalizedPriceString($this->getFullPrice($bForceNetto, false, $totalAmount), 0, $bHTML);
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

        return empty($this->kArtikel)
            || (!($tmpPro->cLagerBeachten === 'Y'
                && $tmpPro->cLagerKleinerNull === 'N'
                && (float)$tmpPro->fLagerbestand < $this->fMin));
    }
}
