<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Link
 */
class Link extends MainModel
{
    /**
     * @var int
     */
    public $kLink;

    /**
     * @var int
     */
    public $kVaterLink;

    /**
     * @var int
     */
    public $kLinkgruppe;

    /**
     * @var int
     */
    public $kPlugin;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var int
     */
    public $nLinkart;

    /**
     * @var string
     */
    public $cNoFollow;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cKundengruppen;

    /**
     * @var string
     */
    public $cSichtbarNachLogin;

    /**
     * deprecated
     *
     * @var string
     */
    public $cDruckButton;

    /**
     * @var string
     */
    public $nSort;

    /**
     * @var string
     */
    public $bSSL = '0';

    /**
     * @var string
     */
    public $bIsFluid = '0';

    /**
     * @var string
     */
    public $cIdentifier = '';

    /**
     * @var array
     */
    public $oSub_arr = array();

    /**
     * @return int
     */
    public function getLink()
    {
        return (int)$this->kLink;
    }

    /**
     * @param int $kLink
     * @return $this
     */
    public function setLink($kLink)
    {
        $this->kLink = (int)$kLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getVaterLink()
    {
        return (int)$this->kVaterLink;
    }

    /**
     * @param int $kVaterLink
     * @return $this
     */
    public function setVaterLink($kVaterLink)
    {
        $this->kVaterLink = (int)$kVaterLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkgruppe()
    {
        return (int)$this->kLinkgruppe;
    }

    /**
     * @param int $kLinkgruppe
     * @return $this
     */
    public function setLinkgruppe($kLinkgruppe)
    {
        $this->kLinkgruppe = (int)$kLinkgruppe;

        return $this;
    }

    /**
     * @return int
     */
    public function getPlugin()
    {
        return (int)$this->kPlugin;
    }

    /**
     * @param int $kPlugin
     * @return $this
     */
    public function setPlugin($kPlugin)
    {
        $this->kPlugin = (int)$kPlugin;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkart()
    {
        return (int)$this->nLinkart;
    }

    /**
     * @param int $nLinkart
     * @return $this
     */
    public function setLinkart($nLinkart)
    {
        $this->nLinkart = (int)$nLinkart;

        return $this;
    }

    /**
     * @return string
     */
    public function getNoFollow()
    {
        return $this->cNoFollow;
    }

    /**
     * @param string $cNoFollow
     * @return $this
     */
    public function setNoFollow($cNoFollow)
    {
        $this->cNoFollow = $cNoFollow;

        return $this;
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return $this->cURL;
    }

    /**
     * @param string $cURL
     * @return $this
     */
    public function setURL($cURL)
    {
        $this->cURL = $cURL;

        return $this;
    }

    /**
     * @return string
     */
    public function getKundengruppen()
    {
        return $this->cKundengruppen;
    }

    /**
     * @param string $cKundengruppen
     * @return $this
     */
    public function setKundengruppen($cKundengruppen)
    {
        $this->cKundengruppen = $cKundengruppen;

        return $this;
    }

    /**
     * @return string
     */
    public function getSichtbarNachLogin()
    {
        return $this->cSichtbarNachLogin;
    }

    /**
     * @param string $cSichtbarNachLogin
     * @return $this
     */
    public function setSichtbarNachLogin($cSichtbarNachLogin)
    {
        $this->cSichtbarNachLogin = $cSichtbarNachLogin;

        return $this;
    }

    /**
     * deprecated
     *
     * @return string
     */
    public function getDruckButton()
    {
        return $this->cDruckButton;
    }

    /**
     * deprecated
     *
     * @param string $cDruckButton
     * @return $this
     */
    public function setDruckButton($cDruckButton)
    {
        $this->cDruckButton = $cDruckButton;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->nSort;
    }

    /**
     * @param int $nSort
     * @return $this
     */
    public function setSort($nSort)
    {
        $this->nSort = (int)$nSort;

        return $this;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setSSL($mode)
    {
        $this->bSSL = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSSL()
    {
        return $this->bSSL;
    }

    /**
     * @param $mode
     * @return $this
     */
    public function setIsFluid($mode)
    {
        $this->bIsFluid = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsFluid()
    {
        return $this->bIsFluid;
    }

    /**
     * @param string $ident
     * @return $this
     */
    public function setIdentifier($ident)
    {
        $this->cIdentifier = $ident;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->cIdentifier;
    }

    /**
     * @param int         $kKey
     * @param object|null $oObj
     * @param mixed|null  $xOption
     * @return $this
     */
    public function load($kKey, $oObj = null, $xOption = null)
    {
        $kKey = (int)$kKey;
        $oObj = Shop::DB()->query(
            "SELECT *
              FROM tlink
              WHERE kLink = " . $kKey, 1
        );

        if (!empty($oObj->kLink)) {
            $this->loadObject($oObj);

            if ($xOption) {
                $this->oSub_arr = self::getSub($this->getLink());
            }
        }

        return $this;
    }

    /**
     * @param int $kVaterLink
     * @return null|array
     */
    public static function getSub($kVaterLink)
    {
        $kVaterLink = (int)$kVaterLink;
        if ($kVaterLink > 0) {
            $oLink_arr = Shop::DB()->query(
                "SELECT *
                  FROM tlink
                  WHERE kVaterLink = " . $kVaterLink, 2
            );

            if (is_array($oLink_arr) && count($oLink_arr) > 0) {
                foreach ($oLink_arr as &$oLink) {
                    $oLink = new self($oLink->kLink, null, true);
                }
            }

            return $oLink_arr;
        }

        return;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save($bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            $oObj->kLink              = $this->kLink;
            $oObj->kVaterLink         = $this->kVaterLink;
            $oObj->kLinkgruppe        = $this->kLinkgruppe;
            $oObj->kPlugin            = $this->kPlugin;
            $oObj->cName              = $this->cName;
            $oObj->nLinkart           = $this->nLinkart;
            $oObj->cNoFollow          = $this->cNoFollow;
            $oObj->cURL               = $this->cURL;
            $oObj->cKundengruppen     = $this->cKundengruppen;
            $oObj->cSichtbarNachLogin = $this->cSichtbarNachLogin;
            $oObj->cDruckButton       = $this->cDruckButton;
            $oObj->nSort              = $this->nSort;
            $oObj->bSSL               = $this->bSSL;
            $oObj->bIsFluid           = $this->bIsFluid;
            $oObj->cIdentifier        = $this->cIdentifier;
        }

        $kPrim = Shop::DB()->insert('tlink', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @throws Exception
     * @return int
     */
    public function update()
    {
        $cQuery   = "UPDATE tlink SET ";
        $cSet_arr = array();

        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = 'get' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $mValue = "'" . Shop::DB()->realEscape(
                            call_user_func(
                                array(
                                    &$this,
                                    $cMethod
                                )
                            )
                        ) . "'";
                    if (call_user_func(
                            array(
                                &$this,
                                $cMethod
                            )
                        ) === null
                    ) {
                        $mValue = 'NULL';
                    }
                    $cSet_arr[] = "{$cMember} = {$mValue}";
                }
            }

            $cQuery .= implode(', ', $cSet_arr);
            $cQuery .= " WHERE kLink = {$this->getLink()}";

            return Shop::DB()->query($cQuery, 3);
        } else {
            throw new Exception("ERROR: Object has no members!");
        }
    }

    /**
     * @param bool $bSub
     * @return int
     */
    public function delete($bSub = true)
    {
        $nRows = 0;
        if ($this->kLink > 0) {
            $nRows = Shop::DB()->query("DELETE FROM tlink WHERE kLink = " . $this->getLink(), 3);
            Shop::DB()->delete('tlinksprache', 'kLink', $this->getLink());
            Shop::DB()->query("DELETE FROM tseo WHERE kKey = " . $this->getLink() . " AND cKey = 'kLink'", 4);

            $cDir = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER . $this->getLink();
            if (is_dir($cDir) && $this->getLink() > 0) {
                if (delDirRecursively($cDir)) {
                    rmdir($cDir);
                }
            }

            if ($bSub) {
                if (isset($this->oSub_arr) && count($this->oSub_arr) > 0) {
                    foreach ($this->oSub_arr as $oSub) {
                        $oSub->delete();
                    }
                }
            }
        }

        return $nRows;
    }
}
