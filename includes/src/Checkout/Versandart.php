<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Checkout;

use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Shop;
use Illuminate\Support\Collection;

/**
 * Class Versandart
 * @package JTL\Checkout
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
     * @var ?string
     */
    public $eSteuer;

    /**
     * @var ?string
     */
    public $cCountryCode;

    /**
     * @var ?array
     */
    public $cPriceLocalized;

    /**
     * @var Collection
     */
    public $surcharges;

    /**
     * Versandart constructor.
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
        $members = \array_keys(\get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        $this->kVersandart = (int)$this->kVersandart;
        $localized         = Shop::Container()->getDB()->selectAll(
            'tversandartsprache',
            'kVersandart',
            $this->kVersandart
        );
        foreach ($localized as $translation) {
            $this->oVersandartSprache_arr[$translation->cISOSprache] = $translation;
        }
        // Versandstaffel
        $this->oVersandartStaffel_arr = Shop::Container()->getDB()->selectAll(
            'tversandartstaffel',
            'kVersandart',
            (int)$this->kVersandart
        );

        $this->loadSurcharges();

        return 1;
    }

    public function loadSurcharges(): void
    {
        $this->surcharges = new Collection();
        $surcharges       = Shop::Container()->getDB()->queryPrepared(
            'SELECT kVersandzuschlag
                FROM tversandzuschlag
                WHERE kVersandart = :kVersandart
                ORDER BY kVersandzuschlag DESC',
            ['kVersandart' => $this->kVersandart],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($surcharges as $surcharge) {
            $this->surcharges->push(new Versandzuschlag($surcharge->kVersandzuschlag));
        }
    }

    /**
     * @param string $ISO
     * @return Collection
     */
    public function getSurchargesForCountry(string $ISO): Collection
    {
        return $this->surcharges->filter(function (Versandzuschlag $surcharge) use ($ISO) {
            return $surcharge->getISO() === $ISO;
        });
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset(
            $obj->oVersandartSprache_arr,
            $obj->oVersandartStaffel_arr,
            $obj->nMinLiefertage,
            $obj->nMaxLiefertage
        );
        $this->kVersandart = Shop::Container()->getDB()->insert('tversandart', $obj);

        return $this->kVersandart;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset(
            $obj->oVersandartSprache_arr,
            $obj->oVersandartStaffel_arr,
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
            'DELETE tversandzuschlag, tversandzuschlagplz, tversandzuschlagsprache
                FROM tversandzuschlag
                LEFT JOIN tversandzuschlagplz 
                    ON tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                LEFT JOIN tversandzuschlagsprache 
                    ON tversandzuschlagsprache.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                WHERE tversandzuschlag.kVersandart = ' . $kVersandart,
            ReturnType::DEFAULT
        );

        return true;
    }

    /**
     * @param int $kVersandart
     * @return bool
     */
    public static function cloneShipping(int $kVersandart): bool
    {
        $sections = [
            'tversandartsprache'     => 'kVersandart',
            'tversandartstaffel'     => 'kVersandartStaffel',
            'tversandartzahlungsart' => 'kVersandartZahlungsart',
            'tversandzuschlag'       => 'kVersandzuschlag'
        ];

        $method = Shop::Container()->getDB()->select('tversandart', 'kVersandart', $kVersandart);

        if (isset($method->kVersandart) && $method->kVersandart > 0) {
            unset($method->kVersandart);
            $kVersandartNew = Shop::Container()->getDB()->insert('tversandart', $method);

            if ($kVersandartNew > 0) {
                foreach ($sections as $name => $key) {
                    $items = self::getShippingSection($name, 'kVersandart', $kVersandart);
                    self::cloneShippingSection($items, $name, 'kVersandart', $kVersandartNew, $key);
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
        if ($value > 0 && \mb_strlen($table) > 0 && \mb_strlen($key) > 0) {
            $Objs = Shop::Container()->getDB()->selectAll($table, $key, $value);

            if (\is_array($Objs)) {
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
    private static function cloneShippingSection(array $objectArr, $table, $key, int $value, $unsetKey = null): void
    {
        if ($value > 0 && \is_array($objectArr) && \count($objectArr) > 0 && \mb_strlen($key) > 0) {
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
    private static function cloneShippingSectionSpecial(int $oldKey, int $newKey): void
    {
        if ($oldKey > 0 && $newKey > 0) {
            $sections = [
                'tversandzuschlagplz'     => 'kVersandzuschlagPlz',
                'tversandzuschlagsprache' => 'kVersandzuschlag'
            ];

            foreach ($sections as $section => $subKey) {
                $subSections = self::getShippingSection($section, 'kVersandzuschlag', $oldKey);

                self::cloneShippingSection($subSections, $section, 'kVersandzuschlag', $newKey, $subKey);
            }
        }
    }
}
