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
     * Constructor
     *
     * @param int $kKupon - primarykey
     */
    public function __construct($kKupon = 0)
    {
        if ((int)$kKupon > 0) {
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
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }

        unset($oObj->kKupon);

        $kPrim = Shop::Container()->getDB()->insert('tkupon', $oObj);

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
        $_upd                        = new stdClass();
        $_upd->kKundengruppe         = $this->kKundengruppe;
        $_upd->kSteuerklasse         = $this->kSteuerklasse;
        $_upd->cName                 = $this->cName;
        $_upd->fWert                 = $this->fWert;
        $_upd->cWertTyp              = $this->cWertTyp;
        $_upd->dGueltigAb            = $this->dGueltigAb;
        $_upd->dGueltigBis           = $this->dGueltigBis;
        $_upd->fMindestbestellwert   = $this->fMindestbestellwert;
        $_upd->cCode                 = $this->cCode;
        $_upd->nVerwendungen         = $this->nVerwendungen;
        $_upd->nVerwendungenBisher   = $this->nVerwendungenBisher;
        $_upd->nVerwendungenProKunde = $this->nVerwendungenProKunde;
        $_upd->cArtikel              = $this->cArtikel;
        $_upd->cHersteller           = $this->cHersteller;
        $_upd->cKategorien           = $this->cKategorien;
        $_upd->cKunden               = $this->cKunden;
        $_upd->cKuponTyp             = $this->cKuponTyp;
        $_upd->cLieferlaender        = $this->cLieferlaender;
        $_upd->cZusatzgebuehren      = $this->cZusatzgebuehren;
        $_upd->cAktiv                = $this->cAktiv;
        $_upd->dErstellt             = $this->dErstellt;
        $_upd->nGanzenWKRabattieren  = $this->nGanzenWKRabattieren;

        return Shop::Container()->getDB()->update('tkupon', 'kKupon', (int)$this->kKupon, $_upd);
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
    public function setKupon(int $kKupon)
    {
        $this->kKupon = $kKupon;

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return $this
     */
    public function setKundengruppe(int $kKundengruppe)
    {
        $this->kKundengruppe = $kKundengruppe;

        return $this;
    }

    /**
     * @param int $kSteuerklasse
     * @return $this
     */
    public function setSteuerklasse(int $kSteuerklasse)
    {
        $this->kSteuerklasse = $kSteuerklasse;

        return $this;
    }

    /**
     * @param string $cName
     */
    public function setName($cName)
    {
        $this->cName = Shop::Container()->getDB()->escape($cName);
    }

    /**
     * @param float $fWert
     */
    public function setWert($fWert)
    {
        $this->fWert = (float)$fWert;
    }

    /**
     * @param string $cWertTyp
     * @return $this
     */
    public function setWertTyp($cWertTyp)
    {
        $this->cWertTyp = Shop::Container()->getDB()->escape($cWertTyp);

        return $this;
    }

    /**
     * @param string $dGueltigAb
     * @return $this
     */
    public function setGueltigAb($dGueltigAb)
    {
        $this->dGueltigAb = Shop::Container()->getDB()->escape($dGueltigAb);

        return $this;
    }

    /**
     * @param string $dGueltigBis
     * @return $this
     */
    public function setGueltigBis($dGueltigBis)
    {
        $this->dGueltigBis = Shop::Container()->getDB()->escape($dGueltigBis);

        return $this;
    }

    /**
     * @param float $fMindestbestellwert
     * @return $this
     */
    public function setMindestbestellwert($fMindestbestellwert)
    {
        $this->fMindestbestellwert = (float)$fMindestbestellwert;

        return $this;
    }

    /**
     * @param string $cCode
     * @return $this
     */
    public function setCode($cCode)
    {
        $this->cCode = Shop::Container()->getDB()->escape($cCode);

        return $this;
    }

    /**
     * @param int $nVerwendungen
     * @return $this
     */
    public function setVerwendungen(int $nVerwendungen)
    {
        $this->nVerwendungen = $nVerwendungen;

        return $this;
    }

    /**
     * @param int $nVerwendungenBisher
     * @return $this
     */
    public function setVerwendungenBisher(int $nVerwendungenBisher)
    {
        $this->nVerwendungenBisher = $nVerwendungenBisher;

        return $this;
    }

    /**
     * @param int $nVerwendungenProKunde
     * @return $this
     */
    public function setVerwendungenProKunde(int $nVerwendungenProKunde)
    {
        $this->nVerwendungenProKunde = $nVerwendungenProKunde;

        return $this;
    }

    /**
     * @param string $cArtikel
     * @return $this
     */
    public function setArtikel($cArtikel)
    {
        $this->cArtikel = Shop::Container()->getDB()->escape($cArtikel);

        return $this;
    }

    /**
     * @param string $cHersteller
     * @return $this
     */
    public function setHersteller($cHersteller)
    {
        $this->cHersteller = Shop::Container()->getDB()->escape($cHersteller);

        return $this;
    }

    /**
     * @param string $cKategorien
     * @return $this
     */
    public function setKategorien($cKategorien)
    {
        $this->cKategorien = Shop::Container()->getDB()->escape($cKategorien);

        return $this;
    }

    /**
     * @param string $cKunden
     * @return $this
     */
    public function setKunden($cKunden)
    {
        $this->cKunden = Shop::Container()->getDB()->escape($cKunden);

        return $this;
    }

    /**
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp)
    {
        $this->cKuponTyp = Shop::Container()->getDB()->escape($cKuponTyp);

        return $this;
    }

    /**
     * @param string $cLieferlaender
     * @return $this
     */
    public function setLieferlaender($cLieferlaender)
    {
        $this->cLieferlaender = Shop::Container()->getDB()->escape($cLieferlaender);

        return $this;
    }

    /**
     * @param string $cZusatzgebuehren
     * @return $this
     */
    public function setZusatzgebuehren($cZusatzgebuehren)
    {
        $this->cZusatzgebuehren = Shop::Container()->getDB()->escape($cZusatzgebuehren);

        return $this;
    }

    /**
     * @param string $cAktiv
     * @return $this
     */
    public function setAktiv($cAktiv)
    {
        $this->cAktiv = Shop::Container()->getDB()->escape($cAktiv);

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = Shop::Container()->getDB()->escape($dErstellt);

        return $this;
    }

    /**
     * @param int $nGanzenWKRabattieren
     * @return $this
     */
    public function setGanzenWKRabattieren($nGanzenWKRabattieren)
    {
        $this->nGanzenWKRabattieren = (int)$nGanzenWKRabattieren;

        return $this;
    }

    /**
     * @return int
     */
    public function getKupon()
    {
        return $this->kKupon;
    }

    /**
     * @return int
     */
    public function getKundengruppe()
    {
        return $this->kKundengruppe;
    }

    /**
     * @return int
     */
    public function getSteuerklasse()
    {
        return $this->kSteuerklasse;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @return float
     */
    public function getWert()
    {
        return $this->fWert;
    }

    /**
     * @return string
     */
    public function getWertTyp()
    {
        return $this->cWertTyp;
    }

    /**
     * @return string
     */
    public function getGueltigAb()
    {
        return $this->dGueltigAb;
    }

    /**
     * @return string
     */
    public function getGueltigBis()
    {
        return $this->dGueltigBis;
    }

    /**
     * @return float
     */
    public function getMindestbestellwert()
    {
        return $this->fMindestbestellwert;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->cCode;
    }

    /**
     * @return int
     */
    public function getVerwendungen()
    {
        return $this->nVerwendungen;
    }

    /**
     * @return int
     */
    public function getVerwendungenBisher()
    {
        return $this->nVerwendungenBisher;
    }

    /**
     * @return int
     */
    public function getVerwendungenProKunde()
    {
        return $this->nVerwendungenProKunde;
    }

    /**
     * @return string
     */
    public function getArtikel()
    {
        return $this->cArtikel;
    }

    /**
     * @return string
     */
    public function getHersteller()
    {
        return $this->cHersteller;
    }

    /**
     * @return string
     */
    public function getKategorien()
    {
        return $this->cKategorien;
    }

    /**
     * @return string
     */
    public function getKunden()
    {
        return $this->cKunden;
    }

    /**
     * @return string
     */
    public function getKuponTyp()
    {
        return $this->cKuponTyp;
    }

    /**
     * @return string
     */
    public function getLieferlaender()
    {
        return $this->cLieferlaender;
    }

    /**
     * @return string
     */
    public function getZusatzgebuehren()
    {
        return $this->cZusatzgebuehren;
    }

    /**
     * @return string
     */
    public function getAktiv()
    {
        return $this->cAktiv;
    }

    /**
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @return int
     */
    public function getGanzenWKRabattieren()
    {
        return $this->nGanzenWKRabattieren;
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
                $translationList[$Sprache->cISO] = $name_spr->cName;
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
     * @param int $len
     * @param bool $lower
     * @param bool $upper
     * @param bool $numbers
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function generateCode(int $len = 7, bool $lower = true, bool $upper = true, bool $numbers = true, $prefix = '', $suffix = '')
    {
        $lowerString   = $lower ? 'abcdefghijklmnopqrstuvwxyz' : null;
        $upperString   = $upper ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : null;
        $numbersString = $numbers ? '0123456789' : null;
        $cCode         = '';
        $count         = (int)Shop::Container()->getDB()->query(
            "COUNT(*) AS cnt 
                FROM tkupon",
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
     */
    public static function reCheck()
    {
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb, dann verwerfen und neu anlegen
        if (isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cWertTyp === 'prozent') {
            $oKupon = $_SESSION['Kupon'];
            unset($_SESSION['Kupon']);
            Session::Cart()->setzePositionsPreise();
            require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
            kuponAnnehmen($oKupon);
        }
    }

    /**
     * @former resetNeuKundenKupon()
     */
    public static function resetNewCustomerCoupon()
    {
        if (Session::Customer()->isLoggedIn()) {
            $hash = Kuponneukunde::Hash(
                null,
                trim($_SESSION['Kunde']->cNachname),
                trim($_SESSION['Kunde']->cStrasse),
                null,
                trim($_SESSION['Kunde']->cPLZ),
                trim($_SESSION['Kunde']->cOrt),
                trim($_SESSION['Kunde']->cLand)
            );
            Shop::Container()->getDB()->delete('tkuponneukunde', ['cDatenHash','cVerwendet'], [$hash,'N']);
        }

        unset($_SESSION['NeukundenKupon'], $_SESSION['NeukundenKuponAngenommen']);
        Session::Cart()
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
               ->setzePositionsPreise();
    }
}
