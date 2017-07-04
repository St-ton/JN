<?php

/**
 * Class Kundengruppe
 */
class Kundengruppe
{
    /**
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var float
     */
    protected $fRabatt;

    /**
     * @var string
     */
    protected $cStandard;

    /**
     * @var string
     */
    protected $cShopLogin;

    /**
     * @var int
     */
    protected $nNettoPreise;

    /**
     * @var int
     */
    protected $darfPreiseSehen = 1;

    /**
     * @var int
     */
    protected $darfArtikelKategorienSehen = 1;

    /**
     * @var int
     */
    protected $kSprache = 0;

    /**
     * @var array
     */
    protected $Attribute = [];

    /**
     * @var string
     */
    private $cNameLocalized;

    /**
     * Constructor
     *
     * @param int $kKundengruppe
     * @access public
     */
    public function __construct($kKundengruppe = 0)
    {
        if ((int)$kKundengruppe > 0) {
            $this->loadFromDB($kKundengruppe);
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws OutOfBoundsException
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            trigger_error('Kundengruppe: setter should be use to set ' . $name, E_USER_DEPRECATED);
            $this->$name = $value;

            return $this;
        }
        throw new OutOfBoundsException('Unable to get ' . $name);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws OutOfBoundsException
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            trigger_error('Kundengruppe: getter should be use to get ' . $name, E_USER_DEPRECATED);
            Shop::dbg($name, false, 'bt: ', 3);
            return $this->$name;
        }

        throw new OutOfBoundsException('Unable to get ' . $name);
    }

    /**
     * @return $this
     */
    public function loadDefaultGroup()
    {
        $oObj = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
        if ($oObj !== null) {
            $conf = Shop::getSettings([CONF_GLOBAL]);
            foreach (get_object_vars($oObj) as $k => $v) {
                $this->$k = $v;
            }
            $this->kKundengruppe = (int)$this->kKundengruppe;
            $this->nNettoPreise  = (int)$this->nNettoPreise;
            if ($this->cStandard === 'Y') {
                if ((int)$conf['global']['global_sichtbarkeit'] === 2) {
                    $this->darfPreiseSehen = 0;
                } elseif ((int)$conf['global']['global_sichtbarkeit'] === 3) {
                    $this->darfPreiseSehen            = 0;
                    $this->darfArtikelKategorienSehen = 0;
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
        if ($this->kKundengruppe > 0 && $this->kSprache > 0) {
            $oKundengruppeSprache = Shop::DB()->select(
                'tkundengruppensprache',
                'kKundengruppe',
                (int)$this->kKundengruppe,
                'kSprache',
                (int)$this->kSprache
            );
            if (isset($oKundengruppeSprache->cName)) {
                $this->cNameLocalized = $oKundengruppeSprache->cName;
            }
        }

        return $this;
    }

    /**
     * Loads database member into class member
     *
     * @param int $kKundengruppe primarykey
     * @return $this
     * @access private
     */
    private function loadFromDB($kKundengruppe = 0)
    {
        $oObj = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
        if (isset($oObj->kKundengruppe) && $oObj->kKundengruppe > 0) {
            foreach (get_object_vars($oObj) as $k => $v) {
                $this->$k = $v;
            }
        }

        return $this;
    }

    /**
     * Store the class in the database
     *
     * @param bool $bPrim - Controls the return of the method
     * @return bool|int
     * @access public
     */
    public function save($bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }

        unset($oObj->kKundengruppe);
        $kPrim = Shop::DB()->insert('tkundengruppe', $oObj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * Update the class in the database
     *
     * @return int
     * @access public
     */
    public function update()
    {
        $_upd               = new stdClass();
        $_upd->cName        = $this->cName;
        $_upd->fRabatt      = $this->fRabatt;
        $_upd->cStandard    = $this->cStandard;
        $_upd->cShopLogin   = $this->cShopLogin;
        $_upd->nNettoPreise = $this->nNettoPreise;

        return Shop::DB()->update('tkundengruppe', 'kKundengruppe', (int)$this->kKundengruppe, $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @access public
     * @return int
     */
    public function delete()
    {
        return Shop::DB()->delete('tkundengruppe', 'kKundengruppe', (int)$this->kKundengruppe);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setLanguageID($id)
    {
        $this->kSprache = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->kKundengruppe;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->kKundengruppe = (int)$id;

        return $this;
    }

    /**
     * Sets the kKundengruppe
     *
     * @access public
     * @param int $kKundengruppe
     * @return $this
     */
    public function setKundengruppe($kKundengruppe)
    {
        $this->kKundengruppe = (int)$kKundengruppe;

        return $this;
    }

    /**
     * Sets the cName
     *
     * @access public
     * @param string $cName
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = Shop::DB()->escape($cName);

        return $this;
    }

    /**
     * Sets the fRabatt
     *
     * @access public
     * @param float $fRabatt
     * @return $this
     */
    public function setRabatt($fRabatt)
    {
        $this->fRabatt = (float)$fRabatt;

        return $this;
    }

    /**
     * Sets the cStandard
     *
     * @access public
     * @param string $cStandard
     * @return $this
     */
    public function setStandard($cStandard)
    {
        $this->cStandard = Shop::DB()->escape($cStandard);

        return $this;
    }

    /**
     * Sets the cShopLogin
     *
     * @access public
     * @param string $cShopLogin
     * @return $this
     */
    public function setShopLogin($cShopLogin)
    {
        $this->cShopLogin = Shop::DB()->escape($cShopLogin);

        return $this;
    }

    /**
     * Sets the nNettoPreise
     *
     * @access public
     * @param int $nNettoPreise
     * @return $this
     */
    public function setNettoPreise($nNettoPreise)
    {
        $this->nNettoPreise = (int)$nNettoPreise;

        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setDarfPreiseSehen($n)
    {
        return $this->setMayViewPrices($n);
    }

    /**
     * @return bool
     */
    public function getDarfPreiseSehen()
    {
        return $this->mayViewPrices();
    }

    /**
     * @param $n
     * @return $this
     */
    public function setMayViewPrices($n)
    {
        $this->darfPreiseSehen = (int)$n;

        return $this;
    }

    /**
     * @return bool
     */
    public function mayViewPrices()
    {
        return (int)$this->darfPreiseSehen === 1;
    }

    /**
     * @param $n
     * @return $this
     */
    public function setDarfKategorienSehen($n)
    {
        $this->darfArtikelKategorienSehen = (int)$n;

        return $this;
    }

    /**
     * @param $n
     * @return $this
     */
    public function setMayViewCategories($n)
    {
        $this->darfArtikelKategorienSehen = (int)$n;

        return $this;
    }

    /**
     * @return int
     */
    public function getDarfArtikelKategorienSehen()
    {
        return $this->mayViewCategories();
    }

    /**
     * @return bool
     */
    public function mayViewCategories()
    {
        return (int)$this->darfArtikelKategorienSehen === 1;
    }

    /**
     * Gets the kKundengruppe
     *
     * @access public
     * @return int
     */
    public function getKundengruppe()
    {
        return (int)$this->kKundengruppe;
    }

    /**
     * Gets the cName
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * Gets the fRabatt
     *
     * @access public
     * @return float
     */
    public function getRabatt()
    {
        return $this->fRabatt;
    }

    /**
     * Gets the cStandard
     *
     * @access public
     * @return string
     */
    public function getStandard()
    {
        return $this->cStandard;
    }

    /**
     * Gets the cShopLogin
     *
     * @access public
     * @return string
     */
    public function getShopLogin()
    {
        return $this->cShopLogin;
    }

    /**
     * Gets the nNettoPreise
     *
     * @access public
     * @return int
     */
    public function getNettoPreise()
    {
        return $this->nNettoPreise;
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
    public static function getCurrent()
    {
        $kKundengruppe = 0;
        if (isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        } elseif (isset($_SESSION['Kunde']->kKundengruppe)) {
            $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        }

        return $kKundengruppe;
    }

    /**
     * @return int
     */
    public static function getDefaultGroupID()
    {
        if (isset($_SESSION['Kundengruppe']->kKundengruppe) && $_SESSION['Kundengruppe']->kKundengruppe > 0) {
            return (int)$_SESSION['Kundengruppe']->kKundengruppe;
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
        if (isset($_SESSION['Kundengruppe']->kKundengruppe) && $_SESSION['Kundengruppe']->kKundengruppe == $kKundengruppe) {
            return $_SESSION['Kundengruppe'];
        }
        $oKundengruppe = new stdClass();
        if (!$kKundengruppe) {
            $kKundengruppe = self::getDefaultGroupID();
        }
        if ($kKundengruppe > 0) {
            $oKundengruppe = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
            if (isset($oKundengruppe->kKundengruppe) && $oKundengruppe->kKundengruppe > 0 && !isset($_SESSION['Kundengruppe'])) {
                $_SESSION['Kundengruppe']                             = $oKundengruppe;
                $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
                $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
                $conf                                                 = Shop::getSettings([CONF_GLOBAL]);
                if ($conf['global']['global_sichtbarkeit'] == 2) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen = 0;
                }
                if ($conf['global']['global_sichtbarkeit'] == 3) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen            = 0;
                    $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 0;
                }
                $_SESSION['Kundengruppe']->Attribute = self::getAttributes($_SESSION['Kundengruppe']->kKundengruppe);
            }
        }

        return $oKundengruppe;
    }

    /**
     * @return $this
     */
    public function initAttributes()
    {
        if ($this->kKundengruppe > 0) {
            $attributes = Shop::DB()->selectAll('tkundengruppenattribut', 'kKundengruppe', (int)$this->kKundengruppe);
            foreach ($attributes as $attribute) {
                $this->Attribute[strtolower($attribute->cName)] = $attribute->cWert;
            }
        }

        return $this;
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
