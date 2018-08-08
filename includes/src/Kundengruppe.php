<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Kundengruppe
 */
class Kundengruppe
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $discount = 0.0;

    /**
     * @var string
     */
    protected $default;

    /**
     * @var string
     */
    protected $cShopLogin;

    /**
     * @var int
     */
    protected $isMerchant = 0;

    /**
     * @var int
     */
    protected $mayViewPrices = 1;

    /**
     * @var int
     */
    protected $mayViewCategories = 1;

    /**
     * @var int
     */
    protected $languageID = 0;

    /**
     * @var array
     */
    protected $Attribute;

    /**
     * @var string
     */
    private $nameLocalized;

    /**
     * @var array
     */
    private static $mapping = [
        'kKundengruppe'              => 'ID',
        'kSprache'                   => 'LanguageID',
        'nNettoPreise'               => 'IsMerchant',
        'darfPreiseSehen'            => 'MayViewPrices',
        'darfArtikelKategorienSehen' => 'MayViewCategories',
        'cName'                      => 'Name',
        'cStandard'                  => 'Default',
        'fRabatt'                    => 'Discount',
        'cNameLocalized'             => 'nameLocalized'
    ];

    /**
     * Kundengruppe constructor.
     * @param int $kKundengruppe
     */
    public function __construct(int $kKundengruppe = 0)
    {
        if ($kKundengruppe > 0) {
            $this->loadFromDB($kKundengruppe);
        }
    }

    /**
     * @return $this
     */
    public function loadDefaultGroup(): self
    {
        $oObj = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
        if ($oObj !== null) {
            $conf = Shop::getSettings([CONF_GLOBAL]);
            $this->setID((int)$oObj->kKundengruppe)
                 ->setName($oObj->cName)
                 ->setDiscount($oObj->fRabatt)
                 ->setDefault($oObj->cStandard)
                 ->setShopLogin($oObj->cShopLogin)
                 ->setIsMerchant((int)$oObj->nNettoPreise);
            if ($this->isDefault()) {
                if ((int)$conf['global']['global_sichtbarkeit'] === 2) {
                    $this->mayViewPrices = 0;
                } elseif ((int)$conf['global']['global_sichtbarkeit'] === 3) {
                    $this->mayViewPrices     = 0;
                    $this->mayViewCategories = 0;
                }
            }
            $this->localize()->initAttributes();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function localize(): self
    {
        if ($this->id > 0 && $this->languageID > 0) {
            $oKundengruppeSprache = Shop::Container()->getDB()->select(
                'tkundengruppensprache',
                'kKundengruppe',
                (int)$this->id,
                'kSprache',
                (int)$this->languageID
            );
            if (isset($oKundengruppeSprache->cName)) {
                $this->nameLocalized = $oKundengruppeSprache->cName;
            }
        }

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return $this
     */
    private function loadFromDB(int $kKundengruppe = 0): self
    {
        $oObj = Shop::Container()->getDB()->select('tkundengruppe', 'kKundengruppe', $kKundengruppe);
        if (isset($oObj->kKundengruppe) && $oObj->kKundengruppe > 0) {
            $this->setID((int)$oObj->kKundengruppe)
                 ->setName($oObj->cName)
                 ->setDiscount($oObj->fRabatt)
                 ->setDefault($oObj->cStandard)
                 ->setShopLogin($oObj->cShopLogin)
                 ->setIsMerchant($oObj->nNettoPreise);
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $obj               = new stdClass();
        $obj->cName        = $this->name;
        $obj->fRabatt      = $this->discount;
        $obj->cStandard    = strtoupper($this->default);
        $obj->cShopLogin   = $this->cShopLogin;
        $obj->nNettoPreise = (int)$this->isMerchant;
        $kPrim             = Shop::Container()->getDB()->insert('tkundengruppe', $obj);
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
        $_upd               = new stdClass();
        $_upd->cName        = $this->name;
        $_upd->fRabatt      = $this->discount;
        $_upd->cStandard    = $this->default;
        $_upd->cShopLogin   = $this->cShopLogin;
        $_upd->nNettoPreise = $this->isMerchant;

        return Shop::Container()->getDB()->update('tkundengruppe', 'kKundengruppe', (int)$this->id, $_upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkundengruppe', 'kKundengruppe', (int)$this->id);
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     *
     * @param int $kKundengruppe
     * @return $this
     * @deprecated since 4.06
     */
    public function setKundengruppe(int $kKundengruppe): self
    {
        trigger_error('Kundengruppe::setKundengruppe() is deprecated - use setID() instead', E_USER_DEPRECATED);
        $this->id = $kKundengruppe;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = Shop::Container()->getDB()->escape($name);

        return $this;
    }

    /**
     * @param float $fRabatt
     * @return $this
     * @deprecated since 4.06
     */
    public function setRabatt($fRabatt): self
    {
        trigger_error('Kundengruppe::setRabatt() is deprecated - use setDiscount() instead', E_USER_DEPRECATED);

        return $this->setDiscount($fRabatt);
    }

    /**
     * @param float $discount
     * @return $this
     */
    public function setDiscount($discount): self
    {
        $this->discount = (float)$discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param string $cStandard
     * @return $this
     * @deprecated since 4.06
     */
    public function setStandard($cStandard): self
    {
        trigger_error(__METHOD__ . ' is deprecated - use setDefault() instead', E_USER_DEPRECATED);

        return $this->setDefault($cStandard);
    }

    /**
     * @param string $default
     * @return $this
     */
    public function setDefault($default): self
    {
        $this->default = Shop::Container()->getDB()->escape($default);

        return $this;
    }

    /**
     * @param string $cShopLogin
     * @return $this
     */
    public function setShopLogin($cShopLogin): self
    {
        $this->cShopLogin = Shop::Container()->getDB()->escape($cShopLogin);

        return $this;
    }

    /**
     * @param int $nNettoPreise
     * @return $this
     */
    public function setNettoPreise($nNettoPreise): self
    {
        trigger_error('Kundengruppe::setNettoPreise() is deprecated - use setIsMerchant() instead', E_USER_DEPRECATED);

        return $this->setIsMerchant($nNettoPreise);
    }

    /**
     * @param int $is
     * @return $this
     */
    public function setIsMerchant(int $is): self
    {
        $this->isMerchant = $is;

        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setMayViewPrices(int $n): self
    {
        $this->mayViewPrices = $n;

        return $this;
    }

    /**
     * @return bool
     */
    public function mayViewPrices(): bool
    {
        return (int)$this->mayViewPrices === 1;
    }

    /**
     * @return int
     */
    public function getMayViewPrices(): int
    {
        return $this->mayViewPrices;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setMayViewCategories(int $n): self
    {
        $this->mayViewCategories = $n;

        return $this;
    }

    /**
     * @return int
     */
    public function getMayViewCategories(): int
    {
        return $this->mayViewCategories;
    }

    /**
     * @return bool
     */
    public function mayViewCategories(): bool
    {
        return (int)$this->mayViewCategories === 1;
    }

    /**
     * @return int
     * @deprecated since 4.06
     */
    public function getKundengruppe(): int
    {
        trigger_error('Kundengruppe::getKundengruppe() is deprecated - use getID() instead', E_USER_DEPRECATED);

        return $this->getID();
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     * @deprecated since 4.06
     */
    public function getRabatt(): float
    {
        trigger_error('Kundengruppe::getRabatt() is deprecated - use getDiscount() instead', E_USER_DEPRECATED);

        return $this->getDiscount();
    }

    /**
     * @return string|null
     */
    public function getStandard()
    {
        trigger_error('Kundengruppe::getStandard() is deprecated - use getDefault() instead', E_USER_DEPRECATED);

        return $this->getIsDefault();
    }

    /**
     * @return string|null
     */
    public function getIsDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default === 'Y';
    }

    /**
     * @return string|null
     */
    public function getShopLogin()
    {
        return $this->cShopLogin;
    }

    /**
     * @return int
     */
    public function getIsMerchant(): int
    {
        return $this->isMerchant;
    }

    /**
     * @return bool
     */
    public function isMerchant(): bool
    {
        return $this->isMerchant > 0;
    }

    /**
     * @return int
     */
    public function getNettoPreise(): int
    {
        trigger_error('Kundengruppe::getNettoPreise() is deprecated - use getIsMerchant() instead', E_USER_DEPRECATED);

        return $this->getIsMerchant();
    }

    /**
     * Static helper
     *
     * @return array
     */
    public static function getGroups(): array
    {
        $oKdngrp_arr = [];
        $oObj_arr    = Shop::Container()->getDB()->query(
            'SELECT kKundengruppe 
                FROM tkundengruppe',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oObj_arr as $oObj) {
            if (isset($oObj->kKundengruppe) && $oObj->kKundengruppe > 0) {
                $oKdngrp_arr[] = new self($oObj->kKundengruppe);
            }
        }

        return $oKdngrp_arr;
    }

    /**
     * @return stdClass|false
     */
    public static function getDefault()
    {
        return Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setLanguageID($languageID): self
    {
        $this->languageID = (int)$languageID;

        return $this;
    }

    /**
     * @return int
     */
    public static function getCurrent(): int
    {
        $kKundengruppe = 0;
        if (isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $kKundengruppe = $_SESSION['Kundengruppe']->getID();
        } elseif (isset($_SESSION['Kunde']->kKundengruppe)) {
            $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        }

        return (int)$kKundengruppe;
    }

    /**
     * @return int
     */
    public static function getDefaultGroupID(): int
    {
        if (isset($_SESSION['Kundengruppe'])
            && $_SESSION['Kundengruppe'] instanceof self
            && $_SESSION['Kundengruppe']->getID() > 0
        ) {
            return $_SESSION['Kundengruppe']->getID();
        }
        $oKundengruppe = self::getDefault();
        if (isset($oKundengruppe->kKundengruppe) && $oKundengruppe->kKundengruppe > 0) {
            return (int)$oKundengruppe->kKundengruppe;
        }

        return 0;
    }

    /**
     * @param int $kKundengruppe
     * @return Kundengruppe|stdClass
     */
    public static function reset(int $kKundengruppe)
    {
        if (isset($_SESSION['Kundengruppe'])
            && $_SESSION['Kundengruppe'] instanceof self
            && $_SESSION['Kundengruppe']->getID() === $kKundengruppe
        ) {
            return $_SESSION['Kundengruppe'];
        }
        $oKundengruppe = new stdClass();
        if (!$kKundengruppe) {
            $kKundengruppe = self::getDefaultGroupID();
        }
        if ($kKundengruppe > 0) {
            $oKundengruppe = new self($kKundengruppe);
            if ($oKundengruppe->getID() > 0 && !isset($_SESSION['Kundengruppe'])) {
                $oKundengruppe->setMayViewPrices(1)->setMayViewCategories(1);
                $conf = Shop::getSettings([CONF_GLOBAL]);
                if ((int)$conf['global']['global_sichtbarkeit'] === 2) {
                    $oKundengruppe->setMayViewPrices(0);
                }
                if ((int)$conf['global']['global_sichtbarkeit'] === 3) {
                    $oKundengruppe->setMayViewPrices(0)->setMayViewCategories(0);
                }
                $_SESSION['Kundengruppe'] = $oKundengruppe->initAttributes();
            }
        }

        return $oKundengruppe;
    }

    /**
     * @return $this
     */
    public function initAttributes(): self
    {
        if ($this->id > 0) {
            $this->Attribute = [];
            $attributes      = Shop::Container()->getDB()->selectAll('tkundengruppenattribut', 'kKundengruppe', (int)$this->id);
            foreach ($attributes as $attribute) {
                $this->Attribute[strtolower($attribute->cName)] = $attribute->cWert;
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributes(): bool
    {
        return $this->Attribute !== null;
    }

    /**
     * @param string $attributeName
     * @return mixed|null
     */
    public function getAttribute($attributeName)
    {
        return $this->Attribute[$attributeName] ?? null;
    }

    /**
     * @param int $kKundengruppe
     * @return array
     * @deprecated since 4.06
     */
    public static function getAttributes(int $kKundengruppe): array
    {
        $attributes = [];
        if ($kKundengruppe > 0) {
            $attr_arr = Shop::Container()->getDB()->selectAll('tkundengruppenattribut', 'kKundengruppe', $kKundengruppe);
            foreach ($attr_arr as $Att) {
                $attributes[strtolower($Att->cName)] = $Att->cWert;
            }
        }

        return $attributes;
    }
}
