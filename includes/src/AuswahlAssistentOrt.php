<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AuswahlAssistentOrt
 */
class AuswahlAssistentOrt
{
    /**
     * @var int
     */
    public $kAuswahlAssistentOrt;

    /**
     * @var int
     */
    public $kAuswahlAssistentGruppe;

    /**
     * @var string
     */
    public $cKey;

    /**
     * @var int
     */
    public $kKey;

    /**
     * @var array
     */
    public $oOrt_arr;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @param int  $locationID
     * @param int  $groupID
     * @param bool $backend
     */
    public function __construct(int $locationID = 0, int $groupID = 0, bool $backend = false)
    {
        if ($locationID > 0 || $groupID > 0) {
            $this->loadFromDB($locationID, $groupID, $backend);
        }
    }

    /**
     * @param int  $locationID
     * @param int  $groupID
     * @param bool $backend
     */
    private function loadFromDB(int $locationID, int $groupID, bool $backend): void
    {
        if ($groupID > 0) {
            $this->oOrt_arr = [];
            $locationData   = Shop::Container()->getDB()->selectAll(
                'tauswahlassistentort',
                'kAuswahlAssistentGruppe',
                $groupID
            );
            foreach ($locationData as $loc) {
                $this->oOrt_arr[] = new self((int)$loc->kAuswahlAssistentOrt, 0, $backend);
            }
        } elseif ($locationID > 0) {
            $oOrt = Shop::Container()->getDB()->select(
                'tauswahlassistentort',
                'kAuswahlAssistentOrt',
                $locationID
            );
            if (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0) {
                $cMember_arr = array_keys(get_object_vars($oOrt));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oOrt->$cMember;
                }
                $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
                $this->kKey                    = (int)$this->kKey;
                switch ($this->cKey) {
                    case AUSWAHLASSISTENT_ORT_KATEGORIE:
                        if ($backend) {
                            unset($_SESSION['oKategorie_arr'], $_SESSION['oKategorie_arr_new']);
                        }
                        $oKategorie = new Kategorie(
                            $this->kKey,
                            AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe)
                        );

                        $this->cOrt = $oKategorie->cName . '(Kategorie)';
                        break;

                    case AUSWAHLASSISTENT_ORT_LINK:
                        $oSprache   = Shop::Container()->getDB()->select(
                            'tsprache',
                            'kSprache',
                            AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe)
                        );
                        $oLink      = Shop::Container()->getDB()->select(
                            'tlinksprache',
                            'kLink',
                            $this->kKey,
                            'cISOSprache',
                            $oSprache->cISO,
                            null,
                            null,
                            false,
                            'cName'
                        );
                        $this->cOrt = isset($oLink->cName) ? ($oLink->cName . '(CMS)') : null;
                        break;

                    case AUSWAHLASSISTENT_ORT_STARTSEITE:
                        $this->cOrt = 'Startseite';
                        break;
                }
            }
        }
    }

    /**
     * @param array $params
     * @param int   $groupID
     * @return bool
     */
    public static function saveLocation(array $params, int $groupID): bool
    {
        if ($groupID > 0 && is_array($params) && count($params) > 0) {
            // Kategorie
            if (isset($params['cKategorie']) && strlen($params['cKategorie']) > 0) {
                $cKategorie_arr = explode(';', $params['cKategorie']);
                foreach ($cKategorie_arr as $cKategorie) {
                    if ((int)$cKategorie > 0 && strlen($cKategorie) > 0) {
                        $oOrt                          = new stdClass();
                        $oOrt->kAuswahlAssistentGruppe = $groupID;
                        $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_KATEGORIE;
                        $oOrt->kKey                    = $cKategorie;

                        Shop::Container()->getDB()->insert('tauswahlassistentort', $oOrt);
                    }
                }
            }
            // Spezialseite
            if (isset($params['kLink_arr']) && is_array($params['kLink_arr']) && count($params['kLink_arr']) > 0) {
                foreach ($params['kLink_arr'] as $kLink) {
                    if ((int)$kLink > 0) {
                        $oOrt                          = new stdClass();
                        $oOrt->kAuswahlAssistentGruppe = $groupID;
                        $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_LINK;
                        $oOrt->kKey                    = $kLink;

                        Shop::Container()->getDB()->insert('tauswahlassistentort', $oOrt);
                    }
                }
            }
            // Startseite
            if (isset($params['nStartseite']) && (int)$params['nStartseite'] === 1) {
                $oOrt                          = new stdClass();
                $oOrt->kAuswahlAssistentGruppe = $groupID;
                $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_STARTSEITE;
                $oOrt->kKey                    = 1;

                Shop::Container()->getDB()->insert('tauswahlassistentort', $oOrt);
            }
        }

        return false;
    }

    /**
     * @param array $params
     * @param int   $groupID
     * @return bool
     */
    public static function updateLocation(array $params, int $groupID): bool
    {
        $rows = 0;
        if ($groupID > 0 && is_array($params) && count($params) > 0) {
            $rows = Shop::Container()->getDB()->delete(
                'tauswahlassistentort',
                'kAuswahlAssistentGruppe',
                $groupID
            );
        }

        return $rows > 0 && self::saveLocation($params, $groupID);
    }

    /**
     * @param array $cParam_arr
     * @param bool  $bUpdate
     * @return array
     */
    public static function checkLocation(array $cParam_arr, bool $bUpdate = false): array
    {
        $cPlausi_arr = [];
        // Ort
        if ((!isset($cParam_arr['cKategorie']) || strlen($cParam_arr['cKategorie']) === 0)
            && (!isset($cParam_arr['kLink_arr'])
                || !is_array($cParam_arr['kLink_arr'])
                || count($cParam_arr['kLink_arr']) === 0)
            && $cParam_arr['nStartseite'] == 0
        ) {
            $cPlausi_arr['cOrt'] = 1;
        }
        // Ort Kategorie
        if (isset($cParam_arr['cKategorie']) && strlen($cParam_arr['cKategorie']) > 0) {
            $cKategorie_arr = explode(';', $cParam_arr['cKategorie']);

            if (!is_array($cKategorie_arr) || count($cKategorie_arr) === 0) {
                $cPlausi_arr['cKategorie'] = 1;
            }
            if (!is_numeric($cKategorie_arr[0])) {
                $cPlausi_arr['cKategorie'] = 2;
            }

            foreach ($cKategorie_arr as $cKategorie) {
                if ((int)$cKategorie > 0 && strlen($cKategorie) > 0) {
                    if ($bUpdate) {
                        if (self::isCategoryTaken(
                            $cKategorie,
                            $cParam_arr['kSprache'],
                            $cParam_arr['kAuswahlAssistentGruppe']
                        )) {
                            $cPlausi_arr['cKategorie'] = 3;
                        }
                    } elseif (self::isCategoryTaken($cKategorie, $cParam_arr['kSprache'])) {
                        $cPlausi_arr['cKategorie'] = 3;
                    }
                }
            }
        }
        // Ort Spezialseite
        if (isset($cParam_arr['kLink_arr'])
            && is_array($cParam_arr['kLink_arr'])
            && count($cParam_arr['kLink_arr']) > 0
        ) {
            foreach ($cParam_arr['kLink_arr'] as $kLink) {
                if ((int)$kLink > 0) {
                    if ($bUpdate) {
                        if (self::isLinkTaken(
                            $kLink,
                            $cParam_arr['kSprache'],
                            $cParam_arr['kAuswahlAssistentGruppe']
                        )) {
                            $cPlausi_arr['kLink_arr'] = 1;
                        }
                    } elseif (self::isLinkTaken($kLink, $cParam_arr['kSprache'])) {
                        $cPlausi_arr['kLink_arr'] = 1;
                    }
                }
            }
        }
        // Ort Startseite
        if (isset($cParam_arr['nStartseite']) && (int)$cParam_arr['nStartseite'] === 1) {
            if ($bUpdate) {
                if (self::isStartPageTaken(
                    $cParam_arr['kSprache'],
                    $cParam_arr['kAuswahlAssistentGruppe']
                )) {
                    $cPlausi_arr['nStartseite'] = 1;
                }
            } elseif (self::isStartPageTaken($cParam_arr['kSprache'])) {
                $cPlausi_arr['nStartseite'] = 1;
            }
        }

        return $cPlausi_arr;
    }

    /**
     * @param int $kKategorie
     * @param int $kSprache
     * @param int $groupID
     * @return bool
     */
    public static function isCategoryTaken(int $kKategorie, int $kSprache, int $groupID = 0): bool
    {
        if ($kKategorie === 0 || $kSprache === 0) {
            return false;
        }
        $cOrtSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $oOrt    = Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $cOrtSQL . '
                    AND o.kKey = :catID',
            [
                'keyID'  => AUSWAHLASSISTENT_ORT_KATEGORIE,
                'catID'  => $kKategorie,
                'langID' => $kSprache,
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0;
    }

    /**
     * @param int $kLink
     * @param int $kSprache
     * @param int $groupID
     * @return bool
     */
    public static function isLinkTaken(int $kLink, int $kSprache, int $groupID = 0): bool
    {
        if ($kLink === 0 || $kSprache === 0) {
            return false;
        }
        $cOrtSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $oOrt    = Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $cOrtSQL . '
                    AND o.kKey = :linkID',
            [
                'langID' => $kSprache,
                'keyID'  => AUSWAHLASSISTENT_ORT_LINK,
                'linkID' => $kLink
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0;
    }

    /**
     * @param int $kSprache
     * @param int $groupID
     * @return bool
     */
    public static function isStartPageTaken(int $kSprache, int $groupID = 0): bool
    {
        if ($kSprache === 0) {
            return false;
        }
        $cOrtSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $oOrt    = Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $cOrtSQL . '
                    AND o.kKey = 1',
            ['langID' => $kSprache, 'keyID'  => AUSWAHLASSISTENT_ORT_STARTSEITE],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0;
    }

    /**
     * @param string $cKey
     * @param int    $kKey
     * @param int    $kSprache
     * @param bool   $bBackend
     * @return AuswahlAssistentOrt|null
     */
    public static function getLocation($cKey, int $kKey, int $kSprache, bool $bBackend = false): ?self
    {
        if ($kKey > 0 && $kSprache > 0 && strlen($cKey) > 0) {
            $oOrt = Shop::Container()->getDB()->executeQueryPrepared(
                'SELECT kAuswahlAssistentOrt
                    FROM tauswahlassistentort AS o
                    JOIN tauswahlassistentgruppe AS g
                        ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                        AND g.kSprache = :langID
                    WHERE o.cKey = :keyID
                        AND o.kKey = :kkey',
                [
                    'langID' => $kSprache,
                    'keyID'  => $cKey,
                    'kkey'   => $kKey
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0) {
                return new self((int)$oOrt->kAuswahlAssistentOrt, 0, $bBackend);
            }
        }

        return null;
    }
}
