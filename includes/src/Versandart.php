<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Versandart
 */
class Versandart
{
    /**
     * @var int
     */
    public $kVersandart;

    /**
     * @var int
     */
    public $kVersandberechnung;

    /**
     * @var string
     */
    public $cVersandklassen;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cLaender;

    /**
     * @var string
     */
    public $cAnzeigen;

    /**
     * @var string
     */
    public $cKundengruppen;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var string
     */
    public $cNurAbhaengigeVersandart;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var float
     */
    public $fPreis;

    /**
     * @var float
     */
    public $fVersandkostenfreiAbX;

    /**
     * @var float
     */
    public $fDeckelung;

    /**
     * @var array
     */
    public $oVersandartSprache_arr;

    /**
     * @var array
     */
    public $oVersandartStaffel_arr;

    /**
     * @var int
     */
    public $kRechnungsadresse;

    /**
     * @var string
     */
    public $cSendConfirmationMail;

    /**
     * @var string
     */
    public $cIgnoreShippingProposal;

    /**
     * @var int
     */
    public $nMinLiefertage;

    /**
     * @var int
     */
    public $nMaxLiefertage;

    /**
     * Konstruktor
     *
     * @param int $kVersandart
     */
    public function __construct(int $kVersandart = 0)
    {
        if ($kVersandart > 0) {
            $this->loadFromDB($kVersandart);
        }
    }

    /**
     * @param int $kVersandart
     * @return int
     */
    public function loadFromDB(int $kVersandart): int
    {
        $obj = Shop::Container()->getDB()->select('tversandart', 'kVersandart', $kVersandart);
        if ($obj === null || !$obj->kVersandart) {
            return 0;
        }
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        $this->kVersandart = (int)$this->kVersandart;
        // VersandartSprache
        $oVersandartSprache_arr = Shop::Container()->getDB()->selectAll('tversandartsprache', 'kVersandart', $this->kVersandart);
        foreach ($oVersandartSprache_arr as $oVersandartSprache) {
            $this->oVersandartSprache_arr[$oVersandartSprache->cISOSprache] = $oVersandartSprache;
        }
        // Versandstaffel
        $this->oVersandartStaffel_arr = Shop::Container()->getDB()->selectAll(
            'tversandartstaffel',
            'kVersandart',
            (int)$this->kVersandart
        );

        return 1;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);
        unset(
            $obj->oVersandartSprache_arr,
            $obj->oVersandartStaffel_arr,
            $obj->kRechnungsadresse,
            $obj->nMinLiefertage,
            $obj->nMaxLiefertage
        );
        $this->kRechnungsadresse = Shop::Container()->getDB()->insert('tversandart', $obj);

        return $this->kVersandart;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);
        unset(
            $obj->oVersandartSprache_arr,
            $obj->oVersandartStaffel_arr,
            $obj->kRechnungsadresse,
            $obj->nMinLiefertage,
            $obj->nMaxLiefertage
        );

        return Shop::Container()->getDB()->update('tversandart', 'kVersandart', $obj->kVersandart, $obj);
    }

    /**
     * @param int $kVersandart
     * @return bool
     */
    public static function deleteInDB(int $kVersandart): bool
    {
        if ($kVersandart <= 0) {
            return false;
        }
        Shop::Container()->getDB()->delete('tversandart', 'kVersandart', $kVersandart);
        Shop::Container()->getDB()->delete('tversandartsprache', 'kVersandart', $kVersandart);
        Shop::Container()->getDB()->delete('tversandartzahlungsart', 'kVersandart', $kVersandart);
        Shop::Container()->getDB()->delete('tversandartstaffel', 'kVersandart', $kVersandart);
        Shop::Container()->getDB()->query(
            "DELETE tversandzuschlag, tversandzuschlagplz, tversandzuschlagsprache
                FROM tversandzuschlag
                LEFT JOIN tversandzuschlagplz 
                    ON tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                LEFT JOIN tversandzuschlagsprache 
                    ON tversandzuschlagsprache.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                WHERE tversandzuschlag.kVersandart = {$kVersandart}",
            \DB\ReturnType::DEFAULT
        );

        return true;
    }

    /**
     * @param int $kVersandart
     * @return bool
     */
    public static function cloneShipping(int $kVersandart): bool
    {
        $cSection_arr = [
            'tversandartsprache'     => 'kVersandart',
            'tversandartstaffel'     => 'kVersandartStaffel',
            'tversandartzahlungsart' => 'kVersandartZahlungsart',
            'tversandzuschlag'       => 'kVersandzuschlag'
        ];

        $oVersandart = Shop::Container()->getDB()->select('tversandart', 'kVersandart', $kVersandart);

        if (isset($oVersandart->kVersandart) && $oVersandart->kVersandart > 0) {
            unset($oVersandart->kVersandart);
            $kVersandartNew = Shop::Container()->getDB()->insert('tversandart', $oVersandart);

            if ($kVersandartNew > 0) {
                foreach ($cSection_arr as $cSection => $cKey) {
                    $oSection_arr = self::getShippingSection($cSection, 'kVersandart', $kVersandart);
                    self::cloneShippingSection($oSection_arr, $cSection, 'kVersandart', $kVersandartNew, $cKey);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $table
     * @param string $key
     * @param int    $value
     * @return array
     */
    private static function getShippingSection($table, $key, int $value): array
    {
        if ($value > 0 && strlen($table) > 0 && strlen($key) > 0) {
            $Objs = Shop::Container()->getDB()->selectAll($table, $key, $value);

            if (is_array($Objs)) {
                return $Objs;
            }
        }

        return [];
    }

    /**
     * @param array       $objectArr
     * @param string      $table
     * @param string      $key
     * @param mixed       $value
     * @param null|string $unsetKey
     */
    private static function cloneShippingSection(array $objectArr, $table, $key, int $value, $unsetKey = null)
    {
        if ($value > 0 && is_array($objectArr) && count($objectArr) > 0 && strlen($key) > 0) {
            foreach ($objectArr as $Obj) {
                $kKeyPrim = $Obj->$unsetKey;
                if ($unsetKey !== null) {
                    unset($Obj->$unsetKey);
                }
                $Obj->$key = $value;
                if ($table === 'tversandartzahlungsart' && empty($Obj->fAufpreis)) {
                    $Obj->fAufpreis = 0;
                }
                $kKey = Shop::Container()->getDB()->insert($table, $Obj);

                if ($kKey > 0 && $table === 'tversandzuschlag') {
                    self::cloneShippingSectionSpecial($kKeyPrim, $kKey);
                }
            }
        }
    }

    /**
     * @param int $oldKey
     * @param int $newKey
     */
    private static function cloneShippingSectionSpecial(int $oldKey, int $newKey)
    {
        if ($oldKey > 0 && $newKey > 0) {
            $cSectionSub_arr = [
                'tversandzuschlagplz'     => 'kVersandzuschlagPlz',
                'tversandzuschlagsprache' => 'kVersandzuschlag'
            ];

            foreach ($cSectionSub_arr as $cSectionSub => $cSubKey) {
                $oSubSection_arr = self::getShippingSection($cSectionSub, 'kVersandzuschlag', $oldKey);

                self::cloneShippingSection($oSubSection_arr, $cSectionSub, 'kVersandzuschlag', $newKey, $cSubKey);
            }
        }
    }
}
