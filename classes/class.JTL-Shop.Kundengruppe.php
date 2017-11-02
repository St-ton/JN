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
     * Constructor
     *
     * @param int $kKundengruppe
     */
    public function __construct($kKundengruppe = 0)
    {
        if ((int)$kKundengruppe > 0) {
            $this->loadFromDB($kKundengruppe);
        }
    }

    /**
     * @return $this
     */
    public function loadDefaultGroup()
    {
        $oObj = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
        if ($oObj !== null) {
            $conf = Shop::getSettings([CONF_GLOBAL]);
            $this->setID($oObj->kKundengruppe)
                 ->setName($oObj->cName)
                 ->setDiscount($oObj->fRabatt)
                 ->setDefault($oObj->cStandard)
                 ->setShopLogin($oObj->cShopLogin)
                 ->setIsMerchant($oObj->nNettoPreise);
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
    private function localize()
    {
        if ($this->id > 0 && $this->languageID > 0) {
            $oKundengruppeSprache = Shop::DB()->select(
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
     * Loads database member into class member
     *
     * @param int $kKundengruppe primarykey
     * @return $this
     */
    private function loadFromDB($kKundengruppe = 0)
    {
        $oObj = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
        if (isset($oObj->kKundengruppe) && $oObj->kKundengruppe > 0) {
            $this->setID($oObj->kKundengruppe)
                 ->setName($oObj->cName)
                 ->setDiscount($oObj->fRabatt)
                 ->setDefault($oObj->cStandard)
                 ->setShopLogin($oObj->cShopLogin)
                 ->setIsMerchant($oObj->nNettoPreise);
        }

        return $this;
    }

    /**
     * Store the class in the database
     *
     * @param bool $bPrim - Controls the return of the method
     * @return bool|int
     */
    public function save($bPrim = true)
    {
        $obj               = new stdClass();
        $obj->cName        = $this->name;
        $obj->fRabatt      = $this->discount;
        $obj->cStandard    = strtoupper($this->default);
        $obj->cShopLogin   = $this->cShopLogin;
        $obj->nNettoPreise = (int)$this->isMerchant;
        $kPrim             = Shop::DB()->insert('tkundengruppe', $obj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * Update the class in the database
     *
     * @return int
     */
    public function update()
    {
        $_upd               = new stdClass();
        $_upd->cName        = $this->name;
        $_upd->fRabatt      = $this->fRabatt;
        $_upd->cStandard    = $this->cStandard;
        $_upd->cShopLogin   = $this->cShopLogin;
        $_upd->nNettoPreise = $this->isMerchant;

        return Shop::DB()->update('tkundengruppe', 'kKundengruppe', (int)$this->id, $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     */
    public function delete()
    {
        return Shop::DB()->delete('tkundengruppe', 'kKundengruppe', (int)$this->id);
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     *
     * @param int $kKundengruppe
     * @return $this
     * @deprecated since 4.06
     */
    public function setKundengruppe($kKundengruppe)
    {
        trigger_error('Kundengruppe::setKundengruppe() is deprecated - use setID() instead', E_USER_DEPRECATED);
        $this->id = (int)$kKundengruppe;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = Shop::DB()->escape($name);

        return $this;
    }

    /**
     * @param float $fRabatt
     * @return $this
     * @deprecated since 4.06
     */
    public function setRabatt($fRabatt)
    {
        trigger_error('Kundengruppe::setRabatt() is deprecated - use setDiscount() instead', E_USER_DEPRECATED);

        return $this->setDiscount($fRabatt);
    }

    /**
     * @param float $discount
     * @return $this
     */
    public function setDiscount($discount)
    {
        $this->discount = (float)$discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param string $cStandard
     * @return $this
     * @deprecated since 4.06
     */
    public function setStandard($cStandard)
    {
        trigger_error('Kundengruppe::setStandard() is deprecated - use setDefault() instead', E_USER_DEPRECATED);

        return $this->setDefault($cStandard);
    }

    /**
     * @param string $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = Shop::DB()->escape($default);

        return $this;
    }

    /**
     * @param string $cShopLogin
     * @return $this
     */
    public function setShopLogin($cShopLogin)
    {
        $this->cShopLogin = Shop::DB()->escape($cShopLogin);

        return $this;
    }

    /**
     * @param int $nNettoPreise
     * @return $this
     */
    public function setNettoPreise($nNettoPreise)
    {
        trigger_error('Kundengruppe::setNettoPreise() is deprecated - use setIsMerchant() instead', E_USER_DEPRECATED);

        return $this->setIsMerchant($nNettoPreise);
    }

    /**
     * @param int $is
     * @return $this
     */
    public function setIsMerchant($is)
    {
        $this->isMerchant = (int)$is;

        return $this;
    }

    /**
     * @param $n
     * @return $this
     */
    public function setMayViewPrices($n)
    {
        $this->mayViewPrices = (int)$n;

        return $this;
    }

    /**
     * @return bool
     */
    public function mayViewPrices()
    {
        return (int)$this->mayViewPrices === 1;
    }

    /**
     * @return bool
     */
    public function getMayViewPrices()
    {
        return $this->mayViewPrices;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setMayViewCategories($n)
    {
        $this->mayViewCategories = (int)$n;

        return $this;
    }

    /**
     * @return int
     */
    public function getMayViewCategories()
    {
        return $this->mayViewCategories;
    }

    /**
     * @return bool
     */
    public function mayViewCategories()
    {
        return (int)$this->mayViewCategories === 1;
    }

    /**
     * @return int
     * @deprecated since 4.06
     */
    public function getKundengruppe()
    {
        trigger_error('Kundengruppe::getKundengruppe() is deprecated - use getID() instead', E_USER_DEPRECATED);

        return $this->getID();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     * @deprecated since 4.06
     */
    public function getRabatt()
    {
        trigger_error('Kundengruppe::getRabatt() is deprecated - use getDiscount() instead', E_USER_DEPRECATED);

        return $this->getDiscount();
    }

    /**
     * @return string
     */
    public function getStandard()
    {
        trigger_error('Kundengruppe::getStandard() is deprecated - use getDefault() instead', E_USER_DEPRECATED);

        return $this->getIsDefault();
    }

    /**
     * @return string
     */
    public function getIsDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default === 'Y';
    }

    /**
     * @return string
     */
    public function getShopLogin()
    {
        return $this->cShopLogin;
    }

    /**
     * @return int
     */
    public function getIsMerchant()
    {
        return $this->isMerchant;
    }

    /**
     * @return bool
     */
    public function isMerchant()
    {
        return $this->isMerchant > 0;
    }

    /**
     * @return int
     */
    public function getNettoPreise()
    {
        trigger_error('Kundengruppe::getNettoPreise() is deprecated - use getIsMerchant() instead', E_USER_DEPRECATED);

        return $this->getIsMerchant();
    }

    /**
     * Static helper
     *
     * @return array
     */
    public static function getGroups()
    {
        $oKdngrp_arr = [];
        $oObj_arr    = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe", 2);

        if (is_array($oObj_arr) && count($oObj_arr) > 0) {
            foreach ($oObj_arr as $oObj) {
                if (isset($oObj->kKundengruppe) && $oObj->kKundengruppe > 0) {
                    $oKdngrp_arr[] = new self($oObj->kKundengruppe);
                }
            }
        }

        return $oKdngrp_arr;
    }

    /**
     * @return stdClass
     */
    public static function getDefault()
    {
        return Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
    }

    /**
     * @return int
     */
    public function getLanguageID()
    {
        return $this->languageID;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setLanguageID($languageID)
    {
        $this->languageID = (int)$languageID;

        return $this;
    }

    /**
     * @return int
     */
    public static function getCurrent()
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
    public static function getDefaultGroupID()
    {
        if (isset($_SESSION['Kundengruppe'])
            && get_class($_SESSION['Kundengruppe']) === 'Kundengruppe'
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
     * @return stdClass
     */
    public static function reset($kKundengruppe)
    {
        $kKundengruppe = (int)$kKundengruppe;
        if (isset($_SESSION['Kundengruppe'])
            && get_class($_SESSION['Kundengruppe']) === 'Kundengruppe'
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
    public function initAttributes()
    {
        if ($this->id > 0) {
            $attributes = Shop::DB()->selectAll('tkundengruppenattribut', 'kKundengruppe', (int)$this->id);
            foreach ($attributes as $attribute) {
                $this->Attribute[strtolower($attribute->cName)] = $attribute->cWert;
            }
        }

        return $this;
    }

    /**
     * @param string $attributeName
     * @return mixed|null
     */
    public function getAttribute($attributeName)
    {
        return isset($this->Attribute[$attributeName])
            ? $this->Attribute[$attributeName]
            : null;
    }

    /**
     * @param int $kKundengruppe
     * @return array
     * @deprecated since 4.06
     */
    public static function getAttributes($kKundengruppe)
    {
        $attributes = [];
        if ($kKundengruppe > 0) {
            $attr_arr = Shop::DB()->selectAll('tkundengruppenattribut', 'kKundengruppe', (int)$kKundengruppe);
            foreach ($attr_arr as $Att) {
                $attributes[strtolower($Att->cName)] = $Att->cWert;
            }
        }

        return $attributes;
    }
}
