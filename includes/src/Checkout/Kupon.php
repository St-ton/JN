<?php

namespace JTL\Checkout;

use DateTime;
use JTL\Cart\CartHelper;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DB\DbInterface;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Product;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\select;

/**
 * Class Kupon
 * @package JTL\Checkout
 */
class Kupon
{
    /**
     * @var int
     */
    public $kKupon;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kSteuerklasse;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var float
     */
    public $fWert;

    /**
     * @var string
     */
    public $cWertTyp;

    /**
     * @var string
     */
    public $dGueltigAb;

    /**
     * @var string
     */
    public $dGueltigBis;

    /**
     * @var float
     */
    public $fMindestbestellwert;

    /**
     * @var string
     */
    public $cCode;

    /**
     * @var int
     */
    public $nVerwendungen;

    /**
     * @var int
     */
    public $nVerwendungenBisher;

    /**
     * @var int
     */
    public $nVerwendungenProKunde;

    /**
     * @var string
     */
    public $cArtikel;

    /**
     * @var string
     */
    public $cHersteller;

    /**
     * @var string
     */
    public $cKategorien;

    /**
     * @var string
     */
    public $cKunden;

    /**
     * @var string
     */
    public $cKuponTyp;

    /**
     * @var string
     */
    public $cLieferlaender;

    /**
     * @var string
     */
    public $cZusatzgebuehren;

    /**
     * @var string
     */
    public $cAktiv;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var int
     */
    public $nGanzenWKRabattieren;

    /**
     * @var array
     */
    public $translationList;

    /**
     * @var stdClass|null
     */
    public $massCreationCoupon;

    /**
     * @var string|null
     */
    public $cLocalizedWert;

    /**
     * @var string|null
     */
    public $cLocalizedMBW;

    /**
     * @var string|null
     */
    public $AngezeigterName;

    /**
     * @var string|null
     */
    public $cGueltigAbShort;

    /**
     * @var string|null
     */
    public $cGueltigAbLong;

    /**
     * @var string|null
     */
    public $cGueltigBisShort;

    /**
     * @var string|null
     */
    public $cKundengruppe;

    /**
     * @var string|null
     */
    public $cArtikelInfo;

    /**
     * @var string|null
     */
    public $cHerstellerInfo;

    /**
     * @var string|null
     */
    public $cKategorieInfo;

    /**
     * @var string|null
     */
    public $cKundenInfo;

    /**
     * @var string|null|DateTime
     */
    public $dLastUse;

    /**
     * @var string|null
     */
    public $cGueltigBisLong;

    /**
     * @var bool
     */
    public $bOpenEnd = false;

    /**
     * @var Kategorie[]
     */
    public $Kategorien = [];

    /**
     * @var Artikel[]
     */
    public $Artikel = [];

    /**
     * @var Hersteller[]
     */
    public $Hersteller = [];

    /**
     * @var DbInterface
     */
    private $db;

    public const TYPE_STANDARD    = 'standard';
    public const TYPE_SHIPPING    = 'versandkupon';
    public const TYPE_NEWCUSTOMER = 'neukundenkupon';

    /**
     * @param string $name
     * @return string|null
     */
    public function __get($name)
    {
        if ($name === 'cLocalizedMbw') {
            return $this->cLocalizedMBW;
        }
        if ($name === 'cLocalizedValue') {
            return $this->cLocalizedWert;
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if ($name === 'cLocalizedMbw') {
            $this->cLocalizedMBW = $value;
        }
        if ($name === 'cLocalizedValue') {
            $this->cLocalizedWert = $value;
        }
    }
    /**
     * @inheritDoc
     */
    public function __sleep(): array
    {
        return select(\array_keys(\get_object_vars($this)), static function ($e): bool {
            return $e !== 'db';
        });
    }

    /**
     * @inheritDoc
     */
    public function __wakeup(): void
    {
        $this->db = Shop::Container()->getDB();
    }

    /**
     * Kupon constructor.
     *
     * @param int              $id
     * @param DbInterface|null $db
     */
    public function __construct(int $id = 0, DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return bool|Kupon
     */
    private function loadFromDB(int $id = 0)
    {
        $couponResult = $this->db->select('tkupon', 'kKupon', $id);

        if ($couponResult !== null && $couponResult->kKupon > 0) {
            $couponResult->translationList = $this->getTranslation((int)$couponResult->kKupon);
            foreach (\array_keys(\get_object_vars($couponResult)) as $member) {
                $this->$member = $couponResult->$member;
            }
            $this->kKupon                = (int)$this->kKupon;
            $this->kKundengruppe         = (int)$this->kKundengruppe;
            $this->kSteuerklasse         = (int)$this->kSteuerklasse;
            $this->nVerwendungen         = (int)$this->nVerwendungen;
            $this->nVerwendungenBisher   = (int)$this->nVerwendungenBisher;
            $this->nVerwendungenProKunde = (int)$this->nVerwendungenProKunde;
            $this->nGanzenWKRabattieren  = (int)$this->nGanzenWKRabattieren;

            return $this;
        }

        return false;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins                        = new stdClass();
        $ins->kKundengruppe         = $this->kKundengruppe;
        $ins->kSteuerklasse         = $this->kSteuerklasse;
        $ins->cName                 = $this->cName;
        $ins->fWert                 = $this->fWert;
        $ins->cWertTyp              = $this->cWertTyp;
        $ins->dGueltigAb            = $this->dGueltigAb;
        $ins->dGueltigBis           = $this->dGueltigBis;
        $ins->fMindestbestellwert   = $this->fMindestbestellwert;
        $ins->cCode                 = $this->cCode;
        $ins->nVerwendungen         = $this->nVerwendungen;
        $ins->nVerwendungenBisher   = $this->nVerwendungenBisher;
        $ins->nVerwendungenProKunde = $this->nVerwendungenProKunde;
        $ins->cArtikel              = $this->cArtikel;
        $ins->cHersteller           = $this->cHersteller;
        $ins->cKategorien           = $this->cKategorien;
        $ins->cKunden               = $this->cKunden;
        $ins->cKuponTyp             = $this->cKuponTyp;
        $ins->cLieferlaender        = $this->cLieferlaender;
        $ins->cZusatzgebuehren      = $this->cZusatzgebuehren;
        $ins->cAktiv                = $this->cAktiv;
        $ins->dErstellt             = $this->dErstellt;
        $ins->nGanzenWKRabattieren  = $this->nGanzenWKRabattieren;
        if (empty($ins->dGueltigBis)) {
            $ins->dGueltigBis = '_DBNULL_';
        }

        $kPrim = $this->db->insert('tkupon', $ins);

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
        $upd                        = new stdClass();
        $upd->kKundengruppe         = $this->kKundengruppe;
        $upd->kSteuerklasse         = $this->kSteuerklasse;
        $upd->cName                 = $this->cName;
        $upd->fWert                 = $this->fWert;
        $upd->cWertTyp              = $this->cWertTyp;
        $upd->dGueltigAb            = $this->dGueltigAb;
        $upd->dGueltigBis           = empty($this->dGueltigBis) ? '_DBNULL_' : $this->dGueltigBis;
        $upd->fMindestbestellwert   = $this->fMindestbestellwert;
        $upd->cCode                 = $this->cCode;
        $upd->nVerwendungen         = $this->nVerwendungen;
        $upd->nVerwendungenBisher   = $this->nVerwendungenBisher;
        $upd->nVerwendungenProKunde = $this->nVerwendungenProKunde;
        $upd->cArtikel              = $this->cArtikel;
        $upd->cHersteller           = $this->cHersteller;
        $upd->cKategorien           = $this->cKategorien;
        $upd->cKunden               = $this->cKunden;
        $upd->cKuponTyp             = $this->cKuponTyp;
        $upd->cLieferlaender        = $this->cLieferlaender;
        $upd->cZusatzgebuehren      = $this->cZusatzgebuehren;
        $upd->cAktiv                = $this->cAktiv;
        $upd->dErstellt             = $this->dErstellt;
        $upd->nGanzenWKRabattieren  = $this->nGanzenWKRabattieren;

        return $this->db->update('tkupon', 'kKupon', (int)$this->kKupon, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return $this->db->delete('tkupon', 'kKupon', (int)$this->kKupon);
    }

    public function augment(): void
    {
        $this->cLocalizedWert = $this->cWertTyp === 'festpreis'
            ? Preise::getLocalizedPriceString($this->fWert)
            : '';
        $this->cLocalizedMBW  = $this->fMindestbestellwert !== null
            ? Preise::getLocalizedPriceString($this->fMindestbestellwert)
            : '';
        $this->bOpenEnd       = $this->dGueltigBis === null;

        if (\date_create($this->dGueltigAb) !== false) {
            $this->cGueltigAbShort = \date_create($this->dGueltigAb)->format('d.m.Y');
            $this->cGueltigAbLong  = \date_create($this->dGueltigAb)->format('d.m.Y H:i');
        } else {
            $this->cGueltigAbShort = 'ungültig';
            $this->cGueltigAbLong  = 'ungültig';
        }

        if ($this->bOpenEnd) {
            $this->cGueltigBisShort = 'open-end';
            $this->cGueltigBisLong  = 'open-end';
        } elseif (\date_create($this->dGueltigBis) === false) {
            $this->cGueltigBisShort = 'ungültig';
            $this->cGueltigBisLong  = 'ungültig';
        } elseif ($this->dGueltigBis === '') {
            $this->cGueltigBisShort = '';
            $this->cGueltigBisLong  = '';
        } else {
            $this->cGueltigBisShort = \date_create($this->dGueltigBis)->format('d.m.Y');
            $this->cGueltigBisLong  = \date_create($this->dGueltigBis)->format('d.m.Y H:i');
        }
        $this->cKundengruppe = '';
        if ((int)$this->kKundengruppe !== -1) {
            $customerGroup       = $this->db->getSingleObject(
                'SELECT cName 
                    FROM tkundengruppe 
                    WHERE kKundengruppe = :cgid',
                ['cgid' => $this->kKundengruppe]
            );
            $this->cKundengruppe = $customerGroup->cName ?? '';
        }

        $artNos       = Text::parseSSKint($this->cArtikel);
        $manufactuers = Text::parseSSKint($this->cHersteller);
        $categories   = Text::parseSSKint($this->cKategorien);
        $customers    = Text::parseSSKint($this->cKunden);

        $this->cArtikelInfo    = ($this->cArtikel === '')
            ? ''
            : (string)\count($artNos);
        $this->cHerstellerInfo = (empty($this->cHersteller) || $this->cHersteller === '-1')
            ? ''
            : (string)\count($manufactuers);
        $this->cKategorieInfo  = (empty($this->cKategorien) || $this->cKategorien === '-1')
            ? ''
            : (string)\count($categories);
        $this->cKundenInfo     = (empty($this->cKunden) || $this->cKunden === '-1')
            ? ''
            : (string)\count($customers);

        $maxCreated     = $this->db->getSingleObject(
            'SELECT MAX(dErstellt) AS dLastUse
                FROM tkuponkunde
                WHERE kKupon = :cid',
            ['cid' => (int)$this->kKupon]
        );
        $this->dLastUse = \date_create(
            $maxCreated !== null && \is_string($maxCreated->dLastUse)
                ? $maxCreated->dLastUse
                : ''
        );
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setKupon(int $id): self
    {
        $this->kKupon = $id;

        return $this;
    }

    /**
     * @param int $customerGroupID
     * @return $this
     */
    public function setKundengruppe(int $customerGroupID): self
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
     * @param string $name
     * @return $this
     */
    public function setName($name): self
    {
        $this->cName = $name;

        return $this;
    }

    /**
     * @param float $fWert
     * @return $this
     */
    public function setWert($fWert): self
    {
        $this->fWert = (float)$fWert;

        return $this;
    }

    /**
     * @param string $cWertTyp
     * @return $this
     */
    public function setWertTyp($cWertTyp): self
    {
        $this->cWertTyp = $cWertTyp;

        return $this;
    }

    /**
     * @param string $dGueltigAb
     * @return $this
     */
    public function setGueltigAb($dGueltigAb): self
    {
        $this->dGueltigAb = $dGueltigAb;

        return $this;
    }

    /**
     * @param string $dGueltigBis
     * @return $this
     */
    public function setGueltigBis($dGueltigBis): self
    {
        $this->dGueltigBis = $dGueltigBis;

        return $this;
    }

    /**
     * @param float $fMindestbestellwert
     * @return $this
     */
    public function setMindestbestellwert($fMindestbestellwert): self
    {
        $this->fMindestbestellwert = (float)$fMindestbestellwert;

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code): self
    {
        $this->cCode = $code;

        return $this;
    }

    /**
     * @param int $nVerwendungen
     * @return $this
     */
    public function setVerwendungen(int $nVerwendungen): self
    {
        $this->nVerwendungen = $nVerwendungen;

        return $this;
    }

    /**
     * @param int $nVerwendungenBisher
     * @return $this
     */
    public function setVerwendungenBisher(int $nVerwendungenBisher): self
    {
        $this->nVerwendungenBisher = $nVerwendungenBisher;

        return $this;
    }

    /**
     * @param int $nVerwendungenProKunde
     * @return $this
     */
    public function setVerwendungenProKunde(int $nVerwendungenProKunde): self
    {
        $this->nVerwendungenProKunde = $nVerwendungenProKunde;

        return $this;
    }

    /**
     * @param string $cArtikel
     * @return $this
     */
    public function setArtikel($cArtikel): self
    {
        $this->cArtikel = $cArtikel;

        return $this;
    }

    /**
     * @param string $cHersteller
     * @return $this
     */
    public function setHersteller($cHersteller): self
    {
        $this->cHersteller = $cHersteller;

        return $this;
    }

    /**
     * @param string $cKategorien
     * @return $this
     */
    public function setKategorien($cKategorien): self
    {
        $this->cKategorien = $cKategorien;

        return $this;
    }

    /**
     * @param string $cKunden
     * @return $this
     */
    public function setKunden($cKunden): self
    {
        $this->cKunden = $cKunden;

        return $this;
    }

    /**
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp): self
    {
        $this->cKuponTyp = $cKuponTyp;

        return $this;
    }

    /**
     * @param string $cLieferlaender
     * @return $this
     */
    public function setLieferlaender($cLieferlaender): self
    {
        $this->cLieferlaender = $cLieferlaender;

        return $this;
    }

    /**
     * @param string $cZusatzgebuehren
     * @return $this
     */
    public function setZusatzgebuehren($cZusatzgebuehren): self
    {
        $this->cZusatzgebuehren = $cZusatzgebuehren;

        return $this;
    }

    /**
     * @param string $cAktiv
     * @return $this
     */
    public function setAktiv($cAktiv): self
    {
        $this->cAktiv = $cAktiv;

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
     * @param int $nGanzenWKRabattieren
     * @return $this
     */
    public function setGanzenWKRabattieren(int $nGanzenWKRabattieren): self
    {
        $this->nGanzenWKRabattieren = $nGanzenWKRabattieren;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getKupon(): ?int
    {
        return $this->kKupon;
    }

    /**
     * @return int|null
     */
    public function getKundengruppe(): ?int
    {
        return $this->kKundengruppe;
    }

    /**
     * @return int|null
     */
    public function getSteuerklasse(): ?int
    {
        return $this->kSteuerklasse;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|float|null
     */
    public function getWert()
    {
        return $this->fWert;
    }

    /**
     * @return string|null
     */
    public function getWertTyp(): ?string
    {
        return $this->cWertTyp;
    }

    /**
     * @return string|null
     */
    public function getGueltigAb(): ?string
    {
        return $this->dGueltigAb;
    }

    /**
     * @return string|null
     */
    public function getGueltigBis(): ?string
    {
        return $this->dGueltigBis;
    }

    /**
     * @return string|float|null
     */
    public function getMindestbestellwert()
    {
        return $this->fMindestbestellwert;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->cCode;
    }

    /**
     * @return int
     */
    public function getVerwendungen(): int
    {
        return (int)($this->nVerwendungen ?? 0);
    }

    /**
     * @return int
     */
    public function getVerwendungenBisher(): int
    {
        return (int)($this->nVerwendungenBisher ?? 0);
    }

    /**
     * @return int
     */
    public function getVerwendungenProKunde(): int
    {
        return (int)($this->nVerwendungenProKunde ?? 0);
    }

    /**
     * @return string|null
     */
    public function getArtikel(): ?string
    {
        return $this->cArtikel;
    }

    /**
     * @return string|null
     */
    public function getHersteller(): ?string
    {
        return $this->cHersteller;
    }

    /**
     * @return string|null
     */
    public function getKategorien(): ?string
    {
        return $this->cKategorien;
    }

    /**
     * @return string|null
     */
    public function getKunden(): ?string
    {
        return $this->cKunden;
    }

    /**
     * @return string|null
     */
    public function getKuponTyp(): ?string
    {
        return $this->cKuponTyp;
    }

    /**
     * @return string|null
     */
    public function getLieferlaender(): ?string
    {
        return $this->cLieferlaender;
    }

    /**
     * @return string|null
     */
    public function getZusatzgebuehren(): ?string
    {
        return $this->cZusatzgebuehren;
    }

    /**
     * @return string|null
     */
    public function getAktiv(): ?string
    {
        return $this->cAktiv;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @return int
     */
    public function getGanzenWKRabattieren(): int
    {
        return (int)($this->nGanzenWKRabattieren ?? 0);
    }

    /**
     * @param string $code
     * @return bool|Kupon
     */
    public function getByCode(string $code = '')
    {
        return $this->db->getCollection(
            'SELECT kKupon AS id 
                FROM tkupon
                WHERE cCode = :code
                LIMIT 1',
            ['code' => $code]
        )->map(function (stdClass $e): self {
            return new self((int)$e->id, $this->db);
        })->first() ?? false;
    }

    /**
     * @param int $id
     * @return array $translationList
     */
    public function getTranslation(int $id = 0): array
    {
        $translationList = [];
        foreach (Frontend::getLanguages() ?? [] as $language) {
            $localized                             = $this->db->select(
                'tkuponsprache',
                'kKupon',
                $id,
                'cISOSprache',
                $language->getCode(),
                null,
                null,
                false,
                'cName'
            );
            $translationList[$language->getCode()] = $localized->cName ?? '';
        }

        return $translationList;
    }

    public function accept(): void
    {
        $cart                       = Frontend::getCart();
        $logger                     = Shop::Container()->getLogService();
        $this->nGanzenWKRabattieren = (int)$this->nGanzenWKRabattieren;
        if ((!empty($_SESSION['oVersandfreiKupon']) || !empty($_SESSION['VersandKupon']) || !empty($_SESSION['Kupon']))
            && isset($_POST['Kuponcode']) && $_POST['Kuponcode']
        ) {
            $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
        }
        $couponPrice = 0;
        if ($this->cWertTyp === 'festpreis') {
            $couponPrice = $this->fWert;
            if ($this->fWert > $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)) {
                $couponPrice = $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
            }
            if ($this->nGanzenWKRabattieren === 0 && $this->fWert > CartHelper::getCouponProductsTotal(
                $this,
                $cart->PositionenArr
            )) {
                $couponPrice = CartHelper::getCouponProductsTotal($this, $cart->PositionenArr);
            }
        } elseif ($this->cWertTyp === 'prozent') {
            // Alle Positionen prüfen ob der Kupon greift und falls ja, dann Position rabattieren
            if ($this->nGanzenWKRabattieren === 0) {
                $productNames = [];
                if (GeneralObject::hasCount('PositionenArr', $cart)) {
                    $productPrice = 0;
                    foreach ($cart->PositionenArr as $item) {
                        $productPrice += CartHelper::checkSetPercentCouponWKPos($item, $this)->fPreis;
                        if (!empty(CartHelper::checkSetPercentCouponWKPos($item, $this)->cName)) {
                            $productNames[] = CartHelper::checkSetPercentCouponWKPos(
                                $item,
                                $this
                            )->cName;
                        }
                    }
                    $couponPrice = ($productPrice / 100) * (float)$this->fWert;
                }
            } else { //Rabatt ermitteln für den ganzen WK
                $couponPrice = ($cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true) / 100.0)
                    * $this->fWert;
            }
        }
        $special        = new stdClass();
        $special->cName = $this->translationList;
        $languageHelper = LanguageHelper::getInstance();
        $oldLangISO     = $languageHelper->getIso();
        foreach (Frontend::getLanguages() as $language) {
            $code = $language->getCode();
            if ($this->cWertTyp === 'prozent'
                && $this->nGanzenWKRabattieren === 0
                && $this->cKuponTyp !== self::TYPE_NEWCUSTOMER
            ) {
                $languageHelper->setzeSprache($code);
                $special->cName[$code]             .= ' ' . $this->fWert . '% ';
                $special->discountForArticle[$code] = $languageHelper->get('discountForArticle', 'checkout');
            } elseif ($this->cWertTyp === 'prozent') {
                $special->cName[$code] .= ' ' . $this->fWert . '%';
            }
        }
        $languageHelper->setzeSprache($oldLangISO);
        if (isset($productNames)) {
            $special->cArticleNameAffix = $productNames;
        }

        $type = \C_WARENKORBPOS_TYP_KUPON;
        if ($this->cKuponTyp === self::TYPE_STANDARD) {
            $_SESSION['Kupon'] = $this;
            if ($logger->isHandling(\JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Standardkupon' . \print_r($this, true) . ' wurde genutzt.');
            }
        } elseif ($this->cKuponTyp === self::TYPE_NEWCUSTOMER) {
            $type = \C_WARENKORBPOS_TYP_NEUKUNDENKUPON;
            $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON);
            $_SESSION['NeukundenKupon']           = $this;
            $_SESSION['NeukundenKuponAngenommen'] = true;
            //@todo: erst loggen wenn wirklich bestellt wird. hier kann noch abgebrochen werden
            if ($logger->isHandling(\JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Neukundenkupon' . \print_r($this, true) . ' wurde genutzt.');
            }
        } elseif ($this->cKuponTyp === self::TYPE_SHIPPING) {
            // Darf nicht gelöscht werden sondern den Preis nur auf 0 setzen!
            //$cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS);
            $cart->setzeVersandfreiKupon();
            $_SESSION['VersandKupon'] = $this;
            $couponPrice              = 0;
            $special->cName           = $this->translationList;
            unset($_POST['Kuponcode']);
            $cart->erstelleSpezialPos(
                $special->cName,
                1,
                $couponPrice * -1,
                $this->kSteuerklasse,
                $type
            );
            if ($logger->isHandling(\JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Versandkupon ' . \print_r($this, true) . ' wurde genutzt.');
            }
        }
        if ($this->cWertTyp === 'prozent' || $this->cWertTyp === 'festpreis') {
            unset($_POST['Kuponcode']);
            $cart->erstelleSpezialPos($special->cName, 1, $couponPrice * -1, $this->kSteuerklasse, $type);
        }
    }

    /**
     * @return Kupon[]
     */
    public function getNewCustomerCoupon(): array
    {
        $coupons            = [];
        $newCustomerCoupons = $this->db->selectAll(
            'tkupon',
            ['cKuponTyp', 'cAktiv'],
            [self::TYPE_NEWCUSTOMER, 'Y'],
            '*',
            'fWert DESC'
        );

        foreach ($newCustomerCoupons as $newCustomerCoupon) {
            if (isset($newCustomerCoupon->kKupon) && $newCustomerCoupon->kKupon > 0) {
                $coupons[] = new self((int)$newCustomerCoupon->kKupon, $this->db);
            }
        }

        return $coupons;
    }

    /**
     * @param int    $len
     * @param bool   $lower
     * @param bool   $upper
     * @param bool   $numbers
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function generateCode(
        int $len = 7,
        bool $lower = true,
        bool $upper = true,
        bool $numbers = true,
        string $prefix = '',
        string $suffix = ''
    ): string {
        $lowerString   = $lower ? 'abcdefghijklmnopqrstuvwxyz' : null;
        $upperString   = $upper ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : null;
        $numbersString = $numbers ? '0123456789' : null;
        $code          = '';
        $count         = (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt 
                FROM tkupon'
        )->cnt;
        while (empty($code) || ($count === 0
                ? empty($code)
                : $this->db->select('tkupon', 'cCode', $code))) {
            $code = $prefix . \mb_substr(\str_shuffle(\str_repeat(
                $lowerString . $upperString . $numbersString,
                $len
            )), 0, $len) . $suffix;
        }

        return $code;
    }

    /**
     * @former altenKuponNeuBerechnen()
     * @since  5.0.0
     */
    public static function reCheck(): void
    {
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb, dann verwerfen und neu anlegen
        if (isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cWertTyp === 'prozent') {
            $coupon = $_SESSION['Kupon'];
            unset($_SESSION['Kupon']);
            Frontend::getCart()->setzePositionsPreise();
            require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
            self::acceptCoupon($coupon);
        }
    }

    /**
     * @return int
     * @former kuponMoeglich()
     * @since  5.0.0
     */
    public static function couponsAvailable(): int
    {
        $cart        = Frontend::getCart();
        $productQry  = '';
        $manufQry    = '';
        $categories  = [];
        $catQry      = '';
        $customerQry = '';
        if (isset($_SESSION['NeukundenKuponAngenommen']) && $_SESSION['NeukundenKuponAngenommen']) {
            return 0;
        }
        $db   = Shop::Container()->getDB();
        $prep = [
            'tya'  => self::TYPE_SHIPPING,
            'tyb'  => self::TYPE_STANDARD,
            'sum'  => $cart->gibGesamtsummeWaren(true, false),
            'cgid' => Frontend::getCustomerGroup()->getID()
        ];
        foreach ($cart->PositionenArr as $key => $item) {
            if (isset($item->Artikel->cArtNr) && \mb_strlen($item->Artikel->cArtNr) > 0) {
                $itemNmbrKey        = 'cArtNr' . $key;
                $prep[$itemNmbrKey] = \str_replace('%', '\%', $item->Artikel->cArtNr);
                $productQry        .= ' OR FIND_IN_SET(:'  . $itemNmbrKey . ", REPLACE(cArtikel, ';', ',')) > 0";
            }
            if (isset($item->Artikel->cHersteller) && \mb_strlen($item->Artikel->cHersteller) > 0) {
                $mnfKey        = 'mnf' . $key;
                $prep[$mnfKey] = $item->Artikel->kHersteller;
                $manufQry     .= ' OR FIND_IN_SET(:'  . $mnfKey . ", REPLACE(cHersteller, ';', ',')) > 0";
            }
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && isset($item->Artikel->kArtikel)
                && $item->Artikel->kArtikel > 0
            ) {
                $productID = (int)$item->Artikel->kArtikel;
                // Kind?
                if (Product::isVariChild($productID)) {
                    $productID = Product::getParent($productID);
                }
                $categoryIDs = $db->selectAll(
                    'tkategorieartikel',
                    'kArtikel',
                    $productID,
                    'kKategorie'
                );
                foreach ($categoryIDs as $categoryID) {
                    $categoryID->kKategorie = (int)$categoryID->kKategorie;
                    if (!\in_array($categoryID->kKategorie, $categories, true)) {
                        $categories[] = $categoryID->kKategorie;
                    }
                }
            }
        }
        foreach ($categories as $i => $category) {
            $prep['cqid' . $i] = $category;
            $catQry           .= ' OR FIND_IN_SET(:cqid' . $i . ", REPLACE(cKategorien, ';', ',')) > 0";
        }
        if (Frontend::getCustomer()->getID() > 0) {
            $prep['cid'] = Frontend::getCustomer()->getID();
            $customerQry = " OR FIND_IN_SET(:cid, REPLACE(cKunden, ';', ',')) > 0";
        }
        $ok = $db->getAffectedRows(
            "SELECT * FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= NOW()
                    AND (dGueltigBis > NOW()
                        OR dGueltigBis IS NULL)
                    AND fMindestbestellwert <= :sum
                    AND (cKuponTyp = :tya OR cKuponTyp = :tyb)
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = :cgid)
                    AND (nVerwendungen = 0
                        OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' " . $productQry . ")
                    AND (cHersteller = '' OR cHersteller = '-1' " . $manufQry . ")
                    AND (cKategorien = ''
                        OR cKategorien = '-1' " . $catQry . ")
                    AND (cKunden = ''
                        OR cKunden = '-1' " . $customerQry . ')',
            $prep
        );

        return $ok > 0 ? 1 : 0;
    }

    /**
     * @param object|Kupon $coupon
     * @return array
     * @former checkeKupon()
     * @since  5.0.0
     */
    public static function checkCoupon($coupon): array
    {
        if (!\is_object($coupon)) {
            return ['ungueltig' => 1];
        }
        if (\get_class($coupon) !== __CLASS__) {
            $coupon = new self((int)$coupon->kKupon);
        }

        return $coupon->check();
    }

    /**
     * @return array
     * @former checkeKupon()
     * @since  5.2.0
     */
    public function check(): array
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        $ret = [];
        if ($this->cAktiv !== 'Y') {
            //not active
            $ret['ungueltig'] = 1;
        } elseif (!empty($this->dGueltigBis) && \date_create($this->dGueltigBis) < \date_create()) {
            //expired
            $ret['ungueltig'] = 2;
        } elseif (\date_create($this->dGueltigAb) > \date_create()) {
            //invalid at the moment
            $ret['ungueltig'] = 3;
        } elseif ($this->fMindestbestellwert > Frontend::getCart()->gibGesamtsummeWarenExt(
            [\C_WARENKORBPOS_TYP_ARTIKEL],
            true
        )
            || ($this->cWertTyp === 'festpreis'
                && (int)$this->nGanzenWKRabattieren === 0
                && $this->fMindestbestellwert > CartHelper::getCouponProductsTotal(
                    $this,
                    Frontend::getCart()->PositionenArr
                )
            )
        ) {
            //minimum order value not reached for whole cart or the products which are valid for this coupon
            $ret['ungueltig'] = 4;
        } elseif ($this->kKundengruppe > 0
            && (int)$this->kKundengruppe !== Frontend::getCustomerGroup()->getID()
        ) {
            //invalid customer group
            $ret['ungueltig'] = 5;
        } elseif ($this->nVerwendungen > 0 && $this->nVerwendungen <= $this->nVerwendungenBisher) {
            //maximum usage reached
            $ret['ungueltig'] = 6;
        } elseif (!CartHelper::cartHasCouponValidProducts($this, Frontend::getCart()->PositionenArr)) {
            //cart needs at least one product for which this coupon is valid
            $ret['ungueltig'] = 7;
        } elseif (!CartHelper::cartHasCouponValidCategories($this, Frontend::getCart()->PositionenArr)) {
            //cart needs at least one category for which this coupon is valid
            $ret['ungueltig'] = 8;
        } elseif ($this->cKuponTyp !== self::TYPE_NEWCUSTOMER
            && (int)$this->cKunden !== -1
            && (!empty($_SESSION['Kunde']->kKunde
                    && !\str_contains($this->cKunden, $_SESSION['Kunde']->kKunde . ';'))
                || !isset($_SESSION['Kunde']->kKunde)
            )
        ) {
            //invalid for account
            $ret['ungueltig'] = 9;
        } elseif ($this->cKuponTyp === self::TYPE_SHIPPING
            && isset($_SESSION['Lieferadresse'])
            && !\str_contains($this->cLieferlaender, $_SESSION['Lieferadresse']->cLand)
        ) {
            //invalid for shipping country
            $ret['ungueltig'] = 10;
        } elseif (!CartHelper::cartHasCouponValidManufacturers($this, Frontend::getCart()->PositionenArr)) {
            //invalid for manufacturer
            $ret['ungueltig'] = 12;
        } elseif (!empty($_SESSION['Kunde']->cMail)) {
            if ($this->cKuponTyp === self::TYPE_NEWCUSTOMER
                && self::newCustomerCouponUsed($_SESSION['Kunde']->cMail)
            ) {
                //email already used for a new-customer coupon
                $ret['ungueltig'] = 11;
            } elseif (!empty($this->nVerwendungenProKunde) && $this->nVerwendungenProKunde > 0) {
                //check if max usage of coupon is reached for cutomer
                $countCouponUsed = $this->db->getSingleObject(
                    'SELECT nVerwendungen
                         FROM tkuponkunde
                         WHERE kKupon = :coupon
                            AND cMail = :email',
                    [
                        'coupon' => (int)$this->kKupon,
                        'email'  => self::hash($_SESSION['Kunde']->cMail)
                    ]
                );
                if ($countCouponUsed !== null && $countCouponUsed->nVerwendungen >= $this->nVerwendungenProKunde) {
                    $ret['ungueltig'] = 6;
                }
            }
        }

        return $ret;
    }

    /**
     * @return string[]
     */
    public function validate(): array
    {
        $errors = [];
        if ($this->cName === '') {
            $errors[] = \__('errorCouponNameMissing');
        }
        if (($this->cKuponTyp === self::TYPE_STANDARD || $this->cKuponTyp === self::TYPE_NEWCUSTOMER)
            && $this->fWert < 0
        ) {
            $errors[] = \__('errorCouponValueNegative');
        }
        if ($this->fMindestbestellwert < 0) {
            $errors[] = \__('errorCouponMinOrderValueNegative');
        }
        if ($this->cKuponTyp === self::TYPE_SHIPPING && $this->cLieferlaender === '') {
            $errors[] = \__('errorCouponISOMissing');
        }
        if (isset($this->massCreationCoupon)) {
            $codeLen = (int)$this->massCreationCoupon->hashLength
                + (int)\mb_strlen($this->massCreationCoupon->prefixHash)
                + (int)\mb_strlen($this->massCreationCoupon->suffixHash);
            if ($codeLen > 32) {
                $errors[] = \__('errorCouponCodeLong');
            }
            if ($codeLen < 2) {
                $errors[] = \__('errorCouponCodeShort');
            }
            if (!$this->massCreationCoupon->lowerCase
                && !$this->massCreationCoupon->upperCase
                && !$this->massCreationCoupon->numbersHash
            ) {
                $errors[] = \__('errorCouponCodeOptionSelect');
            }
        } elseif (\mb_strlen($this->cCode) > 32) {
            $errors[] = \__('errorCouponCodeLong');
        }
        if ($this->cCode !== ''
            && !isset($this->massCreationCoupon)
            && ($this->cKuponTyp === self::TYPE_STANDARD || $this->cKuponTyp === self::TYPE_SHIPPING)
        ) {
            $queryRes = $this->db->getSingleObject(
                'SELECT kKupon
                    FROM tkupon
                    WHERE cCode = :cCode
                        AND kKupon != :kKupon',
                ['cCode' => $this->cCode, 'kKupon' => $this->kKupon]
            );
            if ($queryRes !== null) {
                $errors[] = \__('errorCouponCodeDuplicate');
            }
        }

        $productNos = [];
        foreach (Text::parseSSK($this->cArtikel) as $productNo) {
            $res = $this->db->select('tartikel', 'cArtNr', $productNo);
            if ($res === null) {
                $errors[] = \sprintf(\__('errorProductNumberNotFound'), $productNo);
            } else {
                $productNos[] = $productNo;
            }
        }

        $this->cArtikel = Text::createSSK($productNos);
        if ($this->cKuponTyp === self::TYPE_SHIPPING) {
            $countryHelper = Shop::Container()->getCountryService();
            foreach (Text::parseSSK($this->cLieferlaender) as $isoCode) {
                if ($countryHelper->getCountry($isoCode) === null) {
                    $errors[] = \sprintf(\__('errorISOInvalid'), $isoCode);
                }
            }
        }

        $validFrom  = \date_create($this->dGueltigAb ?? '');
        $validUntil = \date_create($this->dGueltigBis ?? '');
        if ($validFrom === false) {
            $errors[] = \__('errorPeriodBeginFormat');
        }
        if ($validUntil === false) {
            $errors[] = \__('errorPeriodEndFormat');
        }
        $openEnd = $this->dGueltigBis === null;
        if ($validFrom !== false && $validUntil !== false && $validFrom > $validUntil && $openEnd === false) {
            $errors[] = \__('errorPeriodEndAfterBegin');
        }

        return $errors;
    }

    /**
     * check if a new customer coupon was already used for an email
     *
     * @param string $email
     * @return bool
     */
    public static function newCustomerCouponUsed(string $email): bool
    {
        return Shop::Container()->getDB()->getSingleObject(
            'SELECT kKuponFlag
                FROM tkuponflag
                WHERE cEmailHash = :email
                  AND cKuponTyp = :newCustomer',
            [
                'email'       => self::hash($email),
                'newCustomer' => self::TYPE_NEWCUSTOMER
            ]
        ) !== null;
    }

    /**
     * @param Kupon|object $coupon
     * @former kuponAnnehmen()
     * @since  5.0.0
     */
    public static function acceptCoupon($coupon): void
    {
        if (!\is_object($coupon)) {
            return;
        }
        if (\get_class($coupon) !== __CLASS__) {
            $coupon = new self((int)$coupon->kKupon);
        }
        $coupon->accept();
    }

    /**
     * @former resetNeuKundenKupon()
     * @since  5.0.0
     * @param bool $priceRecalculation
     */
    public static function resetNewCustomerCoupon(bool $priceRecalculation = true): void
    {
        unset($_SESSION['NeukundenKupon'], $_SESSION['NeukundenKuponAngenommen']);
        $cart = Frontend::getCart();
        if ($cart->posTypEnthalten(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON)) {
            $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON);
            if ($priceRecalculation) {
                $cart->setzePositionsPreise();
            }
        }
    }

    /**
     * @param string $strToHash
     * @param bool   $strtolower
     * @return string
     */
    public static function hash(string $strToHash, bool $strtolower = true): string
    {
        return $strToHash === ''
            ? ''
            : \hash(
                'sha256',
                $strtolower ? \mb_convert_case($strToHash, \MB_CASE_LOWER) : $strToHash
            );
    }

    /**
     * @return array
     */
    public static function getCouponTypes(): array
    {
        return [
            'newCustomer' => self::TYPE_NEWCUSTOMER,
            'standard'    => self::TYPE_STANDARD,
            'shipping'    => self::TYPE_SHIPPING
        ];
    }

    /**
     * @param int  $errorCode
     * @param bool $createAlert
     * @return null|string
     */
    public static function mapCouponErrorMessage(int $errorCode, bool $createAlert = true): ?string
    {
        switch ($errorCode) {
            case 0:
                Shop::Container()->getAlertService()->addSuccess(Shop::Lang()->get('couponSuccess'), 'couponSuccess');
                return null;
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 12:
                $errorMessage = Shop::Lang()->get('couponErr' . $errorCode);
                break;
            case 11:
                $errorMessage = Shop::Lang()->get('invalidCouponCode', 'checkout');
                break;
            default:
                $errorMessage = Shop::Lang()->get('couponErr99');
                break;
        }
        if ($createAlert) {
            Shop::Container()->getAlertService()->addDanger(
                $errorMessage,
                'couponError',
                ['saveInSession' => true]
            );
        }

        return $errorMessage;
    }
}
