<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Kupon
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
     * Kupon constructor.
     * @param int $kKupon
     */
    public function __construct(int $kKupon = 0)
    {
        if ($kKupon > 0) {
            $this->loadFromDB($kKupon);
        }
    }

    /**
     * @param int $kKupon
     * @return bool|Kupon
     */
    private function loadFromDB(int $kKupon = 0)
    {
        $couponResult = Shop::Container()->getDB()->select('tkupon', 'kKupon', $kKupon);

        if ($couponResult !== null && $couponResult->kKupon > 0) {
            $couponResult->translationList = $this->getTranslation($couponResult->kKupon);
            $cMember_arr                   = array_keys(get_object_vars($couponResult));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $couponResult->$cMember;
            }

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
        $ins = new stdClass();
        foreach (array_keys(get_object_vars($this)) as $cMember) {
            $ins->$cMember = $this->$cMember;
        }

        unset($ins->kKupon);
        if (empty($ins->dGueltigBis)) {
            $ins->dGueltigBis = '_DBNULL_';
        }

        $kPrim = Shop::Container()->getDB()->insert('tkupon', $ins);

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

        return Shop::Container()->getDB()->update('tkupon', 'kKupon', (int)$this->kKupon, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkupon', 'kKupon', (int)$this->kKupon);
    }

    /**
     * @param int $kKupon
     * @return $this
     */
    public function setKupon(int $kKupon): self
    {
        $this->kKupon = $kKupon;

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return $this
     */
    public function setKundengruppe(int $kKundengruppe): self
    {
        $this->kKundengruppe = $kKundengruppe;

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
     * @param string $cName
     * @return $this
     */
    public function setName($cName): self
    {
        $this->cName = Shop::Container()->getDB()->escape($cName);

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
        $this->cWertTyp = Shop::Container()->getDB()->escape($cWertTyp);

        return $this;
    }

    /**
     * @param string $dGueltigAb
     * @return $this
     */
    public function setGueltigAb($dGueltigAb): self
    {
        $this->dGueltigAb = Shop::Container()->getDB()->escape($dGueltigAb);

        return $this;
    }

    /**
     * @param string $dGueltigBis
     * @return $this
     */
    public function setGueltigBis($dGueltigBis): self
    {
        $this->dGueltigBis = Shop::Container()->getDB()->escape($dGueltigBis);

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
     * @param string $cCode
     * @return $this
     */
    public function setCode($cCode): self
    {
        $this->cCode = Shop::Container()->getDB()->escape($cCode);

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
        $this->cArtikel = Shop::Container()->getDB()->escape($cArtikel);

        return $this;
    }

    /**
     * @param string $cHersteller
     * @return $this
     */
    public function setHersteller($cHersteller): self
    {
        $this->cHersteller = Shop::Container()->getDB()->escape($cHersteller);

        return $this;
    }

    /**
     * @param string $cKategorien
     * @return $this
     */
    public function setKategorien($cKategorien): self
    {
        $this->cKategorien = Shop::Container()->getDB()->escape($cKategorien);

        return $this;
    }

    /**
     * @param string $cKunden
     * @return $this
     */
    public function setKunden($cKunden): self
    {
        $this->cKunden = Shop::Container()->getDB()->escape($cKunden);

        return $this;
    }

    /**
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp): self
    {
        $this->cKuponTyp = Shop::Container()->getDB()->escape($cKuponTyp);

        return $this;
    }

    /**
     * @param string $cLieferlaender
     * @return $this
     */
    public function setLieferlaender($cLieferlaender): self
    {
        $this->cLieferlaender = Shop::Container()->getDB()->escape($cLieferlaender);

        return $this;
    }

    /**
     * @param string $cZusatzgebuehren
     * @return $this
     */
    public function setZusatzgebuehren($cZusatzgebuehren): self
    {
        $this->cZusatzgebuehren = Shop::Container()->getDB()->escape($cZusatzgebuehren);

        return $this;
    }

    /**
     * @param string $cAktiv
     * @return $this
     */
    public function setAktiv($cAktiv): self
    {
        $this->cAktiv = Shop::Container()->getDB()->escape($cAktiv);

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = Shop::Container()->getDB()->escape($dErstellt);

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
     * @param string $cCode
     * @return bool|Kupon
     */
    public function getByCode($cCode = '')
    {
        $couponResult = Shop::Container()->getDB()->select('tkupon', 'cCode', $cCode);

        if (isset($couponResult->kKupon) && $couponResult->kKupon > 0) {
            $couponResult->translationList = $this->getTranslation($couponResult->kKupon);
            $cMember_arr                   = array_keys(get_object_vars($couponResult));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $couponResult->$cMember;
            }

            return $this;
        }

        return false;
    }

    /**
     * @param int $kKupon
     * @return array $translationList
     */
    public function getTranslation(int $kKupon = 0): array
    {
        $translationList = [];
        if (isset($_SESSION['Sprachen'])) {
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                $name_spr                        = Shop::Container()->getDB()->select(
                    'tkuponsprache',
                    'kKupon',
                    $kKupon,
                    'cISOSprache',
                    $Sprache->cISO,
                    null,
                    null,
                    false,
                    'cName'
                );
                $translationList[$Sprache->cISO] = $name_spr->cName ?? '';
            }
        }

        return $translationList;
    }

    /**
     * @return array|bool
     */
    public function getNewCustomerCoupon()
    {
        $newCustomerCoupons_arr = [];
        $newCustomerCoupons     = Shop::Container()->getDB()->selectAll(
            'tkupon',
            ['cKuponTyp', 'cAktiv'],
            ['neukundenkupon', 'Y'],
            '*',
            'fWert DESC'
        );

        foreach ($newCustomerCoupons as $newCustomerCoupon) {
            if (isset($newCustomerCoupon->kKupon) && $newCustomerCoupon->kKupon > 0) {
                $newCustomerCoupon->translationList = $this->getTranslation($newCustomerCoupon->kKupon);

                $newCustomerCoupons_arr[] = $newCustomerCoupon;
            }
        }

        return $newCustomerCoupons_arr;
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
        $prefix = '',
        $suffix = ''
    ): string {
        $lowerString   = $lower ? 'abcdefghijklmnopqrstuvwxyz' : null;
        $upperString   = $upper ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : null;
        $numbersString = $numbers ? '0123456789' : null;
        $cCode         = '';
        $count         = (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS cnt 
                FROM tkupon',
            \DB\ReturnType::SINGLE_OBJECT
        )->cnt;
        while (empty($cCode) || ($count === 0
                ? empty($cCode)
                : Shop::Container()->getDB()->select('tkupon', 'cCode', $cCode))) {
            $cCode = $prefix . substr(str_shuffle(str_repeat(
                $lowerString . $upperString . $numbersString,
                $len
            )), 0, $len) . $suffix;
        }

        return $cCode;
    }

    /**
     * @former altenKuponNeuBerechnen()
     * @since 5.0.0
     */
    public static function reCheck(): void
    {
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb, dann verwerfen und neu anlegen
        if (isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cWertTyp === 'prozent') {
            $oKupon = $_SESSION['Kupon'];
            unset($_SESSION['Kupon']);
            \Session\Session::getCart()->setzePositionsPreise();
            require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
            self::acceptCoupon($oKupon);
        }
    }

    /**
     * @return int
     * @former kuponMoeglich()
     * @since 5.0.0
     */
    public static function couponsAvailable(): int
    {
        $cart        = \Session\Session::getCart();
        $productQry  = '';
        $manufQry    = '';
        $categories  = [];
        $catQry      = '';
        $customerQry = '';
        if ((isset($_SESSION['Zahlungsart']->cModulId)
                && strpos($_SESSION['Zahlungsart']->cModulId, 'za_billpay') === 0)
            || (isset($_SESSION['NeukundenKuponAngenommen']) && $_SESSION['NeukundenKuponAngenommen'])
        ) {
            return 0;
        }
        foreach ($cart->PositionenArr as $Pos) {
            if (isset($Pos->Artikel->cArtNr) && strlen($Pos->Artikel->cArtNr) > 0) {
                $productQry .= " OR FIND_IN_SET('" .
                    str_replace('%', '\%', Shop::Container()->getDB()->escape($Pos->Artikel->cArtNr))
                    . "', REPLACE(cArtikel, ';', ',')) > 0";
            }
            if (isset($Pos->Artikel->cHersteller) && strlen($Pos->Artikel->cHersteller) > 0) {
                $manufQry .= " OR FIND_IN_SET('" .
                    str_replace('%', '\%', Shop::Container()->getDB()->escape($Pos->Artikel->kHersteller))
                    . "', REPLACE(cHersteller, ';', ',')) > 0";
            }
            if ($Pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                && isset($Pos->Artikel->kArtikel)
                && $Pos->Artikel->kArtikel > 0
            ) {
                $kArtikel = (int)$Pos->Artikel->kArtikel;
                // Kind?
                if (ArtikelHelper::isVariChild($kArtikel)) {
                    $kArtikel = ArtikelHelper::getParent($kArtikel);
                }
                $categoryIDs = Shop::Container()->getDB()->selectAll(
                    'tkategorieartikel',
                    'kArtikel',
                    $kArtikel,
                    'kKategorie'
                );
                foreach ($categoryIDs as $categoryID) {
                    $categoryID->kKategorie = (int)$categoryID->kKategorie;
                    if (!in_array($categoryID->kKategorie, $categories, true)) {
                        $categories[] = $categoryID->kKategorie;
                    }
                }
            }
        }
        foreach ($categories as $category) {
            $catQry .= " OR FIND_IN_SET('{$category}', REPLACE(cKategorien, ';', ',')) > 0";
        }

        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $customerQry = " OR FIND_IN_SET('{$_SESSION['Kunde']->kKunde}', REPLACE(cKunden, ';', ',')) > 0";
        }
        $kupons_mgl = Shop::Container()->getDB()->query(
            "SELECT * FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= NOW()
                    AND (dGueltigBis > NOW()
                        OR dGueltigBis IS NULL)
                    AND fMindestbestellwert <= " . $cart->gibGesamtsummeWaren(true, false) . "
                    AND (cKuponTyp = 'versandkupon'
                        OR cKuponTyp = 'standard')
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = " . \Session\Session::getCustomerGroup()->getID() . ")
                    AND (nVerwendungen = 0
                        OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' $productQry)
                    AND (cHersteller IS NULL OR cHersteller = '' OR cHersteller = '-1' $manufQry)
                    AND (cKategorien = ''
                        OR cKategorien = '-1' $catQry)
                    AND (cKunden = ''
                        OR cKunden = '-1' $customerQry)",
            \DB\ReturnType::SINGLE_OBJECT
        );

        return empty($kupons_mgl) ? 0 : 1;
    }

    /**
     * @param object|Kupon $Kupon
     * @return array
     * @former checkeKupon()
     * @since 5.0.0
     */
    public static function checkCoupon($Kupon): array
    {
        $ret = [];
        if ($Kupon->cAktiv !== 'Y') {
            $ret['ungueltig'] = 1;
        } elseif (!empty($Kupon->dGueltigBis) && date_create($Kupon->dGueltigBis) < date_create()) {
            $ret['ungueltig'] = 2;
        } elseif (date_create($Kupon->dGueltigAb) > date_create()) {
            $ret['ungueltig'] = 3;
        } elseif ($Kupon->fMindestbestellwert > \Session\Session::getCart()->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
                || ($Kupon->cWertTyp === 'festpreis'
                    && $Kupon->nGanzenWKRabattieren === '0'
                    && $Kupon->fMindestbestellwert > gibGesamtsummeKuponartikelImWarenkorb(
                        $Kupon,
                        \Session\Session::getCart()->PositionenArr
                    )
                )
        ) {
            $ret['ungueltig'] = 4;
        } elseif ($Kupon->kKundengruppe > 0 && $Kupon->kKundengruppe != \Session\Session::getCustomerGroup()->getID()) {
            $ret['ungueltig'] = 5;
        } elseif ($Kupon->nVerwendungen > 0 && $Kupon->nVerwendungen <= $Kupon->nVerwendungenBisher) {
            $ret['ungueltig'] = 6;
        } elseif ($Kupon->cArtikel && !warenkorbKuponFaehigArtikel($Kupon, \Session\Session::getCart()->PositionenArr)) {
            $ret['ungueltig'] = 7;
        } elseif ($Kupon->cKategorien
            && $Kupon->cKategorien != -1
            && !warenkorbKuponFaehigKategorien($Kupon, \Session\Session::getCart()->PositionenArr)
        ) {
            $ret['ungueltig'] = 8;
        } elseif (($Kupon->cKunden != -1 && !empty($_SESSION['Kunde']->kKunde)
                && strpos($Kupon->cKunden, $_SESSION['Kunde']->kKunde . ';') === false
                && $Kupon->cKuponTyp !== 'neukundenkupon')
            || ($Kupon->cKunden != -1 && $Kupon->cKuponTyp !== 'neukundenkupon' && !isset($_SESSION['Kunde']->kKunde))
        ) {
            $ret['ungueltig'] = 9;
        } elseif ($Kupon->cKuponTyp === 'versandkupon'
            && isset($_SESSION['Lieferadresse'])
            && strpos($Kupon->cLieferlaender, $_SESSION['Lieferadresse']->cLand) === false
        ) {
            $ret['ungueltig'] = 10;
        } elseif ($Kupon->cKuponTyp === 'neukundenkupon'
            && self::newCustomerCouponUsed($_SESSION['Kunde']->cMail)
        ) {
            $ret['ungueltig'] = 11;
        } elseif ((int)$Kupon->cHersteller !== -1
            && !empty($Kupon->cHersteller)
            && !warenkorbKuponFaehigHersteller($Kupon, \Session\Session::getCart()->PositionenArr)
        ) {
            $ret['ungueltig'] = 12;
        } else {
            $alreadyUsedSQL = '';
            $bindings = [];
            $email = $_SESSION['Kunde']->cMail ?? '';
            if (!empty($_SESSION['Kunde']->kKunde) && !empty($email)) {
                $alreadyUsedSQL = 'SELECT SUM(nVerwendungen) AS nVerwendungen
                                      FROM tkuponkunde
                                      WHERE (kKunde = :customer OR cMail = :mail)
                                          AND kKupon = :coupon';
                $bindings = [
                    'customer' => (int)$_SESSION['Kunde']->kKunde,
                    'mail' => $email,
                    'coupon' => (int)$Kupon->kKupon
                ];
            } elseif (!empty($email)) {
                $alreadyUsedSQL = 'SELECT SUM(nVerwendungen) AS nVerwendungen
                                      FROM tkuponkunde
                                      WHERE cMail = :mail
                                          AND kKupon = :coupon';
                $bindings = [
                    'mail' => $email,
                    'coupon' => (int)$Kupon->kKupon
                ];
            } elseif (!empty($_SESSION['Kunde']->kKunde)) {
                $alreadyUsedSQL = 'SELECT SUM(nVerwendungen) AS nVerwendungen
                                      FROM tkuponkunde
                                      WHERE kKunde = :customer
                                          AND kKupon = :coupon';
                $bindings = [
                    'customer' => (int)$_SESSION['Kunde']->kKunde,
                    'coupon' => (int)$Kupon->kKupon
                ];
            }
            if ($alreadyUsedSQL !== '') {
                //hat der kunde schon die max. Verwendungsanzahl erreicht?
                $anz = Shop::Container()->getDB()->executeQueryPrepared(
                    $alreadyUsedSQL,
                    $bindings,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($Kupon->nVerwendungenProKunde, $anz->nVerwendungen)
                    && $anz->nVerwendungen >= $Kupon->nVerwendungenProKunde
                    && $Kupon->nVerwendungenProKunde > 0
                ) {
                    $ret['ungueltig'] = 6;
                }
            }
        }

        return $ret;
    }

    /**
     * check if a new customer coupon was already used for an email
     * @param string $email
     * @return bool
     */
    public static function newCustomerCouponUsed(string $email): bool
    {
        $newCustomerCouponUsed = Shop::Container()->getDB()->queryPrepared(
            "SELECT kKuponFlag
                FROM tkuponflag
                WHERE cEmailHash = :email
                  AND cKuponTyp = 'neukunden'",
            ['email' => self::hash($email)],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return !empty($newCustomerCouponUsed);
    }

    /**
     * @param Kupon|object $Kupon
     * @former kuponAnnehmen()
     * @since 5.0.0
     */
    public static function acceptCoupon($Kupon): void
    {
        $cart                        = \Session\Session::getCart();
        $logger                      = Shop::Container()->getLogService();
        $Kupon->nGanzenWKRabattieren = (int)$Kupon->nGanzenWKRabattieren;
        if ((!empty($_SESSION['oVersandfreiKupon']) || !empty($_SESSION['VersandKupon']) || !empty($_SESSION['Kupon']))
            && isset($_POST['Kuponcode']) && $_POST['Kuponcode']
        ) {
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
        }
        $couponPrice = 0;
        if ($Kupon->cWertTyp === 'festpreis') {
            $couponPrice = $Kupon->fWert;
            if ($Kupon->fWert > $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)) {
                $couponPrice = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
            }
            if ($Kupon->nGanzenWKRabattieren === 0 && $Kupon->fWert > gibGesamtsummeKuponartikelImWarenkorb(
                $Kupon,
                $cart->PositionenArr
            )) {
                $couponPrice = gibGesamtsummeKuponartikelImWarenkorb($Kupon, $cart->PositionenArr);
            }
        } elseif ($Kupon->cWertTyp === 'prozent') {
            // Alle Positionen prüfen ob der Kupon greift und falls ja, dann Position rabattieren
            if ($Kupon->nGanzenWKRabattieren === 0) {
                $articleName_arr = [];
                if (is_array($cart->PositionenArr) && count($cart->PositionenArr) > 0) {
                    $articlePrice = 0;
                    foreach ($cart->PositionenArr as $oWKPosition) {
                        $articlePrice += WarenkorbHelper::checkSetPercentCouponWKPos($oWKPosition, $Kupon)->fPreis;
                        if (!empty(WarenkorbHelper::checkSetPercentCouponWKPos($oWKPosition, $Kupon)->cName)) {
                            $articleName_arr[] = WarenkorbHelper::checkSetPercentCouponWKPos(
                                $oWKPosition,
                                $Kupon
                            )->cName;
                        }
                    }
                    $couponPrice = ($articlePrice / 100) * (float)$Kupon->fWert;
                }
            } else { //Rabatt ermitteln für den ganzen WK
                $couponPrice = ($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true) / 100.0)
                    * $Kupon->fWert;
            }
        }

        //posname lokalisiert ablegen
        $Spezialpos        = new stdClass();
        $Spezialpos->cName = $Kupon->translationList;
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            if ($Kupon->cWertTyp === 'prozent'
                && $Kupon->nGanzenWKRabattieren === 0
                && $Kupon->cKuponTyp !== 'neukundenkupon'
            ) {
                $Spezialpos->cName[$Sprache->cISO] .= ' ' . $Kupon->fWert . '% ';
                $discountForArticle                = Shop::Container()->getDB()->select(
                    'tsprachwerte',
                    'cName',
                    'discountForArticle',
                    'kSprachISO',
                    $Sprache->kSprache,
                    null,
                    null,
                    false,
                    'cWert'
                );

                $Spezialpos->discountForArticle[$Sprache->cISO] = $discountForArticle->cWert;
            } elseif ($Kupon->cWertTyp === 'prozent') {
                $Spezialpos->cName[$Sprache->cISO] .= ' ' . $Kupon->fWert . '%';
            }
        }
        if (isset($articleName_arr)) {
            $Spezialpos->cArticleNameAffix = $articleName_arr;
        }

        $postyp = C_WARENKORBPOS_TYP_KUPON;
        if ($Kupon->cKuponTyp === 'standard') {
            $_SESSION['Kupon'] = $Kupon;
            if ($logger->isHandling(JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Standardkupon' . print_r($Kupon, true) . ' wurde genutzt.');
            }
        } elseif ($Kupon->cKuponTyp === 'neukundenkupon') {
            $postyp = C_WARENKORBPOS_TYP_NEUKUNDENKUPON;
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON);
            $_SESSION['NeukundenKupon']           = $Kupon;
            $_SESSION['NeukundenKuponAngenommen'] = true;
            //@todo: erst loggen wenn wirklich bestellt wird. hier kann noch abgebrochen werden
            if ($logger->isHandling(JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Neukundenkupon' . print_r($Kupon, true) . ' wurde genutzt.');
            }
        } elseif ($Kupon->cKuponTyp === 'versandkupon') {
            // Darf nicht gelöscht werden sondern den Preis nur auf 0 setzen!
            //$cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
            $cart->setzeVersandfreiKupon();
            $_SESSION['VersandKupon'] = $Kupon;
            $couponPrice              = 0;
            $Spezialpos->cName        = $Kupon->translationList;
            unset($_POST['Kuponcode']);
            $cart->erstelleSpezialPos(
                $Spezialpos->cName,
                1,
                $couponPrice * -1,
                $Kupon->kSteuerklasse,
                $postyp
            );
            if ($logger->isHandling(JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Versandkupon ' . print_r($Kupon, true) . ' wurde genutzt.');
            }
        }
        if ($Kupon->cWertTyp === 'prozent' || $Kupon->cWertTyp === 'festpreis') {
            unset($_POST['Kuponcode']);
            $cart->erstelleSpezialPos($Spezialpos->cName, 1, $couponPrice * -1, $Kupon->kSteuerklasse, $postyp);
        }
    }

    /**
     * @former resetNeuKundenKupon()
     * @since 5.0.0
     */
    public static function resetNewCustomerCoupon(): void
    {
        unset($_SESSION['NeukundenKupon'], $_SESSION['NeukundenKuponAngenommen']);
        \Session\Session::getCart()
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
               ->setzePositionsPreise();
    }

    /**
     * @param string $strToHash
     * @return string
     */
    public static function hash(string $strToHash): string
    {
        return $strToHash === '' ? '' : md5($strToHash);
    }
}
