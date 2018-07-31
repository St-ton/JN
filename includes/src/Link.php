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
     * @var int
     */
    public $nSort;

    /**
     * @var int
     */
    public $bSSL = 0;

    /**
     * @var int
     */
    public $bIsFluid = 0;

    /**
     * @var string
     */
    public $cIdentifier = '';

    /**
     * @var int
     */
    public $bIsActive = 1;

    /**
     * @var array
     */
    public $oSub_arr = [];

    /**
     * @var string
     */
    public $cISO;

    /**
     * @var int
     */
    public $kSprache = 0;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var int
     */
    public $nHTTPRedirectCode = 0;

    /**
     * @var bool
     */
    public $bHideContent = false;

    /**
     * @var int
     */
    public $nPluginStatus = 0;

    /**
     * @var string
     */
    public $cURLFull;

    /**
     * @var string
     */
    public $cURLFullSSL;

    /**
     * @var int
     */
    public $kSpezialSeite = 0;

    /**
     * @param int $kSpezialSeite
     * @return $this
     */
    public function setSpezialSeite(int $kSpezialSeite): self
    {
        $this->kSpezialSeite = $kSpezialSeite;

        return $this;
    }

    /**
     * @return int
     */
    public function getSpezialSeite(): int
    {
        return (int)$this->kSpezialSeite;
    }

    /**
     * @return string|null
     */
    public function getURLFullSSL()
    {
        return $this->cURLFullSSL;
    }

    /**
     * @param string $cURLFullSSL
     * @return $this
     */
    public function setURLFullSSL($cURLFullSSL): self
    {
        $this->cURLFullSSL = $cURLFullSSL;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURLFull()
    {
        return $this->cURLFull;
    }

    /**
     * @param string $cURLFull
     * @return $this
     */
    public function setURLFull($cURLFull): self
    {
        $this->cURLFull = $cURLFull;

        return $this;
    }

    /**
     * @param int $nPluginStatus
     * @return $this
     */
    public function setPluginStatus(int $nPluginStatus): self
    {
        $this->nPluginStatus = $nPluginStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getPluginStatus(): int
    {
        return (int)$this->nPluginStatus;
    }

    /**
     * @param string $cISO
     * @return $this
     */
    public function setISO($cISO): self
    {
        $this->cISO = $cISO;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getISO()
    {
        return $this->cISO;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache(int $kSprache): self
    {
        $this->kSprache = $kSprache;

        return $this;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @param string $cSeo
     * @return $this
     */
    public function setSeo($cSeo): self
    {
        $this->cSeo = $cSeo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeo()
    {
        return $this->cSeo;
    }

    /**
     * @return int
     */
    public function getLink(): int
    {
        return (int)$this->kLink;
    }

    /**
     * @param int $kLink
     * @return $this
     */
    public function setLink(int $kLink): self
    {
        $this->kLink = $kLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getVaterLink(): int
    {
        return (int)$this->kVaterLink;
    }

    /**
     * @param int $kVaterLink
     * @return $this
     */
    public function setVaterLink(int $kVaterLink): self
    {
        $this->kVaterLink = $kVaterLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkgruppe(): int
    {
        return (int)$this->kLinkgruppe;
    }

    /**
     * @param int $kLinkgruppe
     * @return $this
     */
    public function setLinkgruppe(int $kLinkgruppe): self
    {
        $this->kLinkgruppe = $kLinkgruppe;

        return $this;
    }

    /**
     * @return int
     */
    public function getPlugin(): int
    {
        return (int)$this->kPlugin;
    }

    /**
     * @param int $kPlugin
     * @return $this
     */
    public function setPlugin(int $kPlugin): self
    {
        $this->kPlugin = $kPlugin;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName($cName): self
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkart(): int
    {
        return (int)$this->nLinkart;
    }

    /**
     * @param int $nLinkart
     * @return $this
     */
    public function setLinkart(int $nLinkart): self
    {
        $this->nLinkart = $nLinkart;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNoFollow()
    {
        return $this->cNoFollow;
    }

    /**
     * @param string $cNoFollow
     * @return $this
     */
    public function setNoFollow($cNoFollow): self
    {
        $this->cNoFollow = $cNoFollow;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURL()
    {
        return $this->cURL;
    }

    /**
     * @param string $cURL
     * @return $this
     */
    public function setURL($cURL): self
    {
        $this->cURL = $cURL;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getKundengruppen()
    {
        return $this->cKundengruppen;
    }

    /**
     * @param string $cKundengruppen
     * @return $this
     */
    public function setKundengruppen($cKundengruppen): self
    {
        $this->cKundengruppen = $cKundengruppen;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSichtbarNachLogin()
    {
        return $this->cSichtbarNachLogin;
    }

    /**
     * @param string $cSichtbarNachLogin
     * @return $this
     */
    public function setSichtbarNachLogin($cSichtbarNachLogin): self
    {
        $this->cSichtbarNachLogin = $cSichtbarNachLogin;

        return $this;
    }

    /**
     * @deprecated since 4.0
     * @return string|null
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
    public function setDruckButton($cDruckButton): self
    {
        $this->cDruckButton = $cDruckButton;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort(): int
    {
        return (int)$this->nSort;
    }

    /**
     * @param int $nSort
     * @return $this
     */
    public function setSort(int $nSort): self
    {
        $this->nSort = $nSort;

        return $this;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setSSL(int $mode): self
    {
        $this->bSSL = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSSL(): int
    {
        return (int)$this->bSSL;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setIsFluid(int $mode): self
    {
        $this->bIsFluid = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsFluid(): int
    {
        return (int)$this->bIsFluid;
    }

    /**
     * @param string $ident
     * @return $this
     */
    public function setIdentifier($ident): self
    {
        $this->cIdentifier = $ident;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->cIdentifier;
    }

    /**
     * @param null|int    $kKey
     * @param null|object $oObj
     * @param null|mixed  $xOption
     * @param null|int   $kLinkgruppe
     */
    public function __construct($kKey = null, $oObj = null, $xOption = null, int $kLinkgruppe = null)
    {
        if (is_object($oObj)) {
            $this->loadObject($oObj);
        } elseif ($kKey !== null) {
            $this->load((int)$kKey, $oObj, $xOption, $kLinkgruppe);
        }
    }

    /**
     * @param int         $kKey
     * @param object|null $oObj
     * @param mixed|null  $xOption
     * @param int         $kLinkgruppe
     * @return $this
     */
    public function load($kKey, $oObj = null, $xOption = null, int $kLinkgruppe = null): self
    {
        if ($kLinkgruppe > 0) {
            $oObj = Shop::Container()->getDB()->queryPrepared(
                'SELECT tlink.* 
                    FROM tlink 
                    JOIN tlinkgroupassociations t 
                        ON tlink.kLink = t.linkID
                    WHERE tlink.kLink = :lid
                    AND t.linkGroupID = :lgid',
                ['lid' => (int)$kKey, 'lgid' => $kLinkgruppe],
                \DB\ReturnType::SINGLE_OBJECT
            );
        } else {
            $oObj = Shop::Container()->getDB()->select('tlink', 'kLink', (int)$kKey);
        }
        if (!empty($oObj->kLink)) {
            $this->loadObject($oObj);

            if ($xOption) {
                $this->oSub_arr = self::getSub($this->getLink(), $this->getLinkgruppe());
            }
        }

        return $this;
    }

    /**
     * @param int $kVaterLink
     * @param int $kVaterLinkgruppe
     * @return null|array
     */
    public static function getSub(int $kVaterLink, int $kVaterLinkgruppe = null)
    {
        if ($kVaterLink > 0) {
            if (!empty($kVaterLinkgruppe)) {
                $oLink_arr = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tlink.* 
                        FROM tlink 
                        JOIN tlinkgroupassociations t 
                            ON tlink.kLink = t.linkID
                        WHERE tlink.kVaterLink = :parentID
                            AND t.linkGroupID = :lgid',
                    [
                        'parentID' => $kVaterLink,
                        'lgid'     => $kVaterLinkgruppe
                    ],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            } else {
                $oLink_arr = Shop::Container()->getDB()->selectAll('tlink', 'kVaterLink', $kVaterLink);
            }
            foreach ($oLink_arr as &$oLink) {
                $oLink = new self($oLink->kLink, null, true, $kVaterLinkgruppe);
            }

            return $oLink_arr;
        }

        return null;
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
            $oObj->kLink              = $this->kLink;
            $oObj->kVaterLink         = $this->kVaterLink;
            $oObj->kLinkgruppe        = $this->kLinkgruppe;
            $oObj->kPlugin            = $this->kPlugin;
            $oObj->cName              = $this->cName;
            $oObj->nLinkart           = $this->nLinkart;
            $oObj->cNoFollow          = $this->cNoFollow;
            $oObj->cURL               = $this->cURL;
            $oObj->cKundengruppen     = $this->cKundengruppen;
            $oObj->bIsActive          = $this->bIsActive;
            $oObj->cSichtbarNachLogin = $this->cSichtbarNachLogin;
            $oObj->cDruckButton       = $this->cDruckButton;
            $oObj->nSort              = $this->nSort;
            $oObj->bSSL               = $this->bSSL;
            $oObj->bIsFluid           = $this->bIsFluid;
            $oObj->cIdentifier        = $this->cIdentifier;
        }

        $kPrim = Shop::Container()->getDB()->insert('tlink', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @throws Exception
     * @return int
     */
    public function update(): int
    {
        $cQuery   = 'UPDATE tlink SET ';
        $cSet_arr = [];

        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = 'get' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $val        = $this->$cMethod();
                    $mValue     = $val === null
                        ? 'NULL'
                        : ("'" . Shop::Container()->getDB()->realEscape($val) . "'");
                    $cSet_arr[] = "{$cMember} = {$mValue}";
                }
            }

            $cQuery .= implode(', ', $cSet_arr);
            $cQuery .= " WHERE kLink = {$this->getLink()} AND klinkgruppe = {$this->getLinkgruppe()}";

            return Shop::Container()->getDB()->query($cQuery, \DB\ReturnType::AFFECTED_ROWS);
        }
        throw new Exception('ERROR: Object has no members!');
    }

    /**
     * @param bool $bSub
     * @param int  $kLinkgruppe
     * @return int
     */
    public function delete(bool $bSub = true, int $kLinkgruppe = null): int
    {
        $nRows = 0;
        if ($this->kLink > 0) {
            if (!empty($kLinkgruppe)) {
                $nRows = Shop::Container()->getDB()->delete('tlink', ['kLink', 'kLinkgruppe'], [$this->getLink(), $kLinkgruppe]);
            } else {
                $nRows = Shop::Container()->getDB()->delete('tlink', 'kLink', $this->getLink());
            }
            $nLinkAnz = Shop::Container()->getDB()->selectAll('tlink', 'kLink', $this->getLink());
            if (count($nLinkAnz) === 0) {
                Shop::Container()->getDB()->delete('tlinksprache', 'kLink', $this->getLink());
                Shop::Container()->getDB()->delete('tseo', ['kKey', 'cKey'], [$this->getLink(), 'kLink']);

                $cDir = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER . $this->getLink();
                if (is_dir($cDir) && $this->getLink() > 0 && FileSystemHelper::delDirRecursively($cDir)) {
                    rmdir($cDir);
                }
            }

            if ($bSub && count($this->oSub_arr) > 0) {
                foreach ($this->oSub_arr as $oSub) {
                    $oSub->delete(true, $kLinkgruppe);
                }
            }
        }

        return $nRows;
    }
}
