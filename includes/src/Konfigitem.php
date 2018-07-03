<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfigitem
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
         * @param int $kKonfigitem - primary key
         * @param int $kSprache
         * @param int $kKundengruppe
         */
        public function __construct(int $kKonfigitem = 0, int $kSprache = 0, int $kKundengruppe = 0)
        {
            if ($kKonfigitem > 0) {
                $this->loadFromDB($kKonfigitem, $kSprache, $kKundengruppe);
            }
        }

        /**
         * Specify data which should be serialized to JSON
         *
         * @return array
         */
        public function jsonSerialize(): array
        {
            $cKurzBeschreibung = $this->getKurzBeschreibung();
            $virtual = [
                'bAktiv' => $this->{"bAktiv"}
            ];
            $override = [
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
            $result = array_merge($override, $virtual);

            return StringHandler::utf8_convert_recursive($result);
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfigitem
         * @param int $kSprache
         * @param int $kKundengruppe
         * @return $this
         */
        private function loadFromDB(int $kKonfigitem = 0, int $kSprache = 0, int $kKundengruppe = 0): self
        {
            $oObj = Shop::Container()->getDB()->select('tkonfigitem', 'kKonfigitem', $kKonfigitem);
            if (isset($oObj->kKonfigitem) && $oObj->kKonfigitem > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }

                if (!$kSprache) {
                    $kSprache = Shop::getLanguageID() ?? Sprache::getDefaultLanguage(true)->kSprache;
                }
                if (!$kKundengruppe) {
                    $kKundengruppe = Session::CustomerGroup()->getID();
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
                $this->kSprache          = $kSprache;
                $this->kKundengruppe     = $kKundengruppe;
                $this->oSprache          = new Konfigitemsprache($this->kKonfigitem, $kSprache);
                $this->oPreis            = new Konfigitempreis($this->kKonfigitem, $kKundengruppe);
                $this->oArtikel          = null;
                if ($this->kArtikel > 0) {
                    $oArtikelOptionen                             = new stdClass();
                    $oArtikelOptionen->nAttribute                 = 1;
                    $oArtikelOptionen->nArtikelAttribute          = 1;
                    $oArtikelOptionen->nVariationKombi            = 1;
                    $oArtikelOptionen->nVariationKombiKinder      = 1;
                    $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
                    $oArtikelOptionen->nVariationen               = 0;

                    $this->oArtikel = new Artikel();
                    $this->oArtikel->fuelleArtikel($this->kArtikel, $oArtikelOptionen, $kKundengruppe, $kSprache);
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
            $oObj                    = new stdClass();
            $oObj->kKonfiggruppe     = $this->kKonfiggruppe;
            $oObj->kArtikel          = $this->kArtikel;
            $oObj->nPosTyp           = $this->nPosTyp;
            $oObj->bSelektiert       = $this->bSelektiert;
            $oObj->bEmpfohlen        = $this->bEmpfohlen;
            $oObj->bName             = $this->bName;
            $oObj->bPreis            = $this->bPreis;
            $oObj->bRabatt           = $this->bRabatt;
            $oObj->bZuschlag         = $this->bZuschlag;
            $oObj->bIgnoreMultiplier = $this->bIgnoreMultiplier;
            $oObj->fMin              = $this->fMin;
            $oObj->fMax              = $this->fMax;
            $oObj->fInitial          = $this->fInitial;
            $oObj->nSort             = $this->nSort;

            $kPrim = Shop::Container()->getDB()->insert('tkonfigitem', $oObj);
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
            $_upd                    = new stdClass();
            $_upd->kKonfiggruppe     = $this->kKonfiggruppe;
            $_upd->kArtikel          = $this->kArtikel;
            $_upd->nPosTyp           = $this->nPosTyp;
            $_upd->bSelektiert       = $this->bSelektiert;
            $_upd->bEmpfohlen        = $this->bEmpfohlen;
            $_upd->bPreis            = $this->bPreis;
            $_upd->bName             = $this->bName;
            $_upd->bRabatt           = $this->bRabatt;
            $_upd->bZuschlag         = $this->bZuschlag;
            $_upd->bIgnoreMultiplier = $this->bIgnoreMultiplier;
            $_upd->fMin              = $this->fMin;
            $_upd->fMax              = $this->fMax;
            $_upd->fInitial          = $this->fInitial;
            $_upd->nSort             = $this->nSort;

            return Shop::Container()->getDB()->update('tkonfigitem', 'kKonfigitem', (int)$this->kKonfigitem, $_upd);
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
         * @return array
         */
        public static function fetchAll(int $kKonfiggruppe): array
        {
            $oItemEx_arr = [];
            $oItem_arr   = Shop::Container()->getDB()->queryPrepared(
                "SELECT kKonfigitem 
                    FROM tkonfigitem 
                    WHERE kKonfiggruppe = :groupID 
                    ORDER BY nSort ASC",
                ['groupID' => $kKonfiggruppe],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oItem_arr as &$oItem) {
                $kKonfigitem = $oItem->kKonfigitem;
                $oItem       = new self($kKonfigitem);
                if ($oItem->isValid()) {
                    $oItemEx_arr[] = $oItem;
                }
            }

            return $oItemEx_arr;
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
         * @param int $kArtikel
         * @return $this
         */
        public function setArtikelKey(int $kArtikel): self
        {
            $this->kArtikel = $kArtikel;

            return $this;
        }

        /**
         * @param Artikel $oArtikel
         * @return $this
         */
        public function setArtikel(Artikel $oArtikel): self
        {
            $this->oArtikel = $oArtikel;

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
        public function getArtikel()
        {
            return $this->oArtikel;
        }

        /**
         * @return int|null
         */
        public function getPosTyp()
        {
            return $this->nPosTyp;
        }

        /**
         * @return int|null
         */
        public function getSelektiert()
        {
            return $this->bSelektiert;
        }

        /**
         * @return int|null
         */
        public function getEmpfohlen()
        {
            return $this->bEmpfohlen;
        }

        /**
         * @return Konfigitemsprache|null
         */
        public function getSprache()
        {
            return $this->oSprache;
        }

        /**
         * @return string|null
         */
        public function getName()
        {
            if ($this->oArtikel && $this->bName) {
                return $this->oArtikel->cName;
            }

            if ($this->oSprache) {
                return $this->oSprache->getName();
            }

            return '';
        }

        /**
         * @return string|null
         */
        public function getBeschreibung()
        {
            if ($this->oArtikel && $this->bName) {
                return $this->oArtikel->cBeschreibung;
            }

            if ($this->oSprache) {
                return $this->oSprache->getBeschreibung();
            }

            return '';
        }

        /**
         * @return string|null
         */
        public function getKurzBeschreibung()
        {
            if ($this->oArtikel && $this->bName) {
                return $this->oArtikel->cKurzBeschreibung;
            }

            if ($this->oSprache) {
                return $this->oSprache->getBeschreibung();
            }

            return '';
        }

        /**
         * @return string|null
         */
        public function getBildPfad()
        {
            if ($this->oArtikel && $this->oArtikel->Bilder[0]->cPfadKlein !== BILD_KEIN_ARTIKELBILD_VORHANDEN) {
                return $this->oArtikel->Bilder[0];
            }

            return null;
        }

        /**
         * @return bool
         */
        public function getUseOwnName(): bool
        {
            return !$this->bName;
        }

        /**
         * @param bool $bForceNetto
         * @param bool $bConvertCurrency
         * @return float|int
         */
        public function getPreis($bForceNetto = false, $bConvertCurrency = false)
        {
            $fVKPreis    = 0.0;
            $isConverted = false;
            if ($this->oArtikel && $this->bPreis) {
                //get price from associated article
                $fVKPreis = $this->oArtikel->Preise->fVKNetto ?? 0;
                // Zuschlag / Rabatt berechnen
                $fSpecial = $this->oPreis->getPreis($bConvertCurrency);
                if ($fSpecial != 0) {
                    // Betrag
                    if ($this->oPreis->getTyp() == 0) {
                        $fVKPreis += $fSpecial;
                    } elseif ($this->oPreis->getTyp() == 1) { // Prozent
                        $fVKPreis *= (100 + $fSpecial) / 100;
                    }
                }
            } elseif ($this->oPreis) {
                $fVKPreis    = $this->oPreis->getPreis($bConvertCurrency);
                $isConverted = true;
            }
            if ($bConvertCurrency && !$isConverted) {
                $fVKPreis *= Session::Currency()->getConversionFactor();
            }
            if (!$bForceNetto && !Session::CustomerGroup()->isMerchant()) {
                $fVKPreis = TaxHelper::getGross($fVKPreis, TaxHelper::getSalesTax($this->getSteuerklasse()), 4);
            }

            return $fVKPreis;
        }

        /**
         * @param bool $bForceNetto
         * @param bool $bConvertCurrency
         * @param int $totalAmount
         * @return float|int
         */
        public function getFullPrice(bool $bForceNetto = false, bool $bConvertCurrency = false, $totalAmount = 1)
        {
            $fVKPreis    = 0.0;
            $isConverted = false;
            if ($this->oArtikel && $this->bPreis) {
                //get price from associated article
                $fVKPreis = $this->oArtikel->Preise->fVKNetto ?? 0;
                // Zuschlag / Rabatt berechnen
                $fSpecial = $this->oPreis->getPreis($bConvertCurrency);
                if ($fSpecial != 0) {
                    // Betrag
                    if ($this->oPreis->getTyp() == 0) {
                        $fVKPreis += $fSpecial;
                    } elseif ($this->oPreis->getTyp() == 1) { // Prozent
                        $fVKPreis *= (100 + $fSpecial) / 100;
                    }
                }
            } elseif ($this->oPreis) {
                $fVKPreis    = $this->oPreis->getPreis($bConvertCurrency);
                $isConverted = true;
            }
            if ($bConvertCurrency && !$isConverted) {
                if (isset($_SESSION['Waehrung'])) {
                    $waehrung = $_SESSION['Waehrung'];
                } else {
                    $waehrung = Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y');
                }
                $fVKPreis *= (float)$waehrung->fFaktor;
            }
            if (!$bForceNetto && !Session::CustomerGroup()->getIsMerchant()) {
                $fVKPreis = TaxHelper::getGross($fVKPreis, TaxHelper::getSalesTax($this->getSteuerklasse()), 4);
            }

            return $fVKPreis * $this->fAnzahl * $totalAmount;
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
            $fRabatt = 0.0;
            if ($this->oArtikel && $this->bPreis) {
                $fTmp = $this->oPreis->getPreis();
                if ($fTmp < 0) {
                    $fRabatt = $fTmp * -1;
                    if ($this->oPreis->getTyp() == 0 && !Session::CustomerGroup()->isMerchant()) {
                        $fRabatt = TaxHelper::getGross($fRabatt, TaxHelper::getSalesTax($this->getSteuerklasse()));
                    }
                }
            }

            return $fRabatt;
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
            $fZuschlag = 0.0;
            if ($this->oArtikel && $this->bPreis) {
                $fTmp = $this->oPreis->getPreis();
                if ($fTmp > 0) {
                    $fZuschlag = $fTmp;
                    if ($this->oPreis->getTyp() == 0 && !Session::CustomerGroup()->isMerchant()) {
                        $fZuschlag = TaxHelper::getGross($fZuschlag, TaxHelper::getSalesTax($this->getSteuerklasse()));
                    }
                }
            }

            return $fZuschlag;
        }

        /**
         * @param bool $bHTML
         * @return string
         */
        public function getRabattLocalized(bool $bHTML = true): string
        {
            if ($this->oPreis->getTyp() == 0) {
                return Preise::getLocalizedPriceString($this->getRabatt(), 0, $bHTML);
            }

            return $this->getRabatt() . '%';
        }

        /**
         * @param bool $bHTML
         * @return string
         */
        public function getZuschlagLocalized(bool $bHTML = true): string
        {
            if ($this->oPreis->getTyp() == 0) {
                return Preise::getLocalizedPriceString($this->getZuschlag(), 0, $bHTML);
            }

            return $this->getZuschlag() . '%';
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
            $cLocalized = Preise::getLocalizedPriceString($this->getPreis($bForceNetto), 0, $bHTML);
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
        public function showRabatt()
        {
            return $this->bRabatt;
        }

        /**
         * @return int|null
         */
        public function showZuschlag()
        {
            return $this->bZuschlag;
        }

        /**
         * @return int|null
         */
        public function ignoreMultiplier()
        {
            return $this->bIgnoreMultiplier;
        }

        /**
         * @return int|null
         */
        public function getSprachKey()
        {
            return $this->kSprache;
        }

        /**
         * @return int|null
         */
        public function getKundengruppe()
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
                    && (float)$tmpPro->fLagerbestand <= 0));
        }
    }
}
