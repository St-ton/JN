<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

use DB\ReturnType;

/**
 * Class AuswahlAssistentOrt
 *
 * @package Extensions
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
            $locationData   = \Shop::Container()->getDB()->selectAll(
                'tauswahlassistentort',
                'kAuswahlAssistentGruppe',
                $groupID
            );
            foreach ($locationData as $loc) {
                $this->oOrt_arr[] = new self((int)$loc->kAuswahlAssistentOrt, 0, $backend);
            }
        } elseif ($locationID > 0) {
            $oOrt = \Shop::Container()->getDB()->select(
                'tauswahlassistentort',
                'kAuswahlAssistentOrt',
                $locationID
            );
            if (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0) {
                foreach (\array_keys(\get_object_vars($oOrt)) as $member) {
                    $this->$member = $oOrt->$member;
                }
                $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
                $this->kKey                    = (int)$this->kKey;
                switch ($this->cKey) {
                    case \AUSWAHLASSISTENT_ORT_KATEGORIE:
                        if ($backend) {
                            unset($_SESSION['oKategorie_arr'], $_SESSION['oKategorie_arr_new']);
                        }
                        $oKategorie = new \Kategorie(
                            $this->kKey,
                            AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe)
                        );

                        $this->cOrt = $oKategorie->cName . '(Kategorie)';
                        break;

                    case \AUSWAHLASSISTENT_ORT_LINK:
                        $oSprache   = \Shop::Container()->getDB()->select(
                            'tsprache',
                            'kSprache',
                            AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe)
                        );
                        $oLink      = \Shop::Container()->getDB()->select(
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

                    case \AUSWAHLASSISTENT_ORT_STARTSEITE:
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
        if ($groupID > 0 && \is_array($params) && \count($params) > 0) {
            if (isset($params['cKategorie']) && \mb_strlen($params['cKategorie']) > 0) {
                foreach (\explode(';', $params['cKategorie']) as $cKategorie) {
                    if ((int)$cKategorie > 0 && \mb_strlen($cKategorie) > 0) {
                        $ins                          = new \stdClass();
                        $ins->kAuswahlAssistentGruppe = $groupID;
                        $ins->cKey                    = \AUSWAHLASSISTENT_ORT_KATEGORIE;
                        $ins->kKey                    = $cKategorie;

                        \Shop::Container()->getDB()->insert('tauswahlassistentort', $ins);
                    }
                }
            }
            if (isset($params['kLink_arr']) && \is_array($params['kLink_arr']) && \count($params['kLink_arr']) > 0) {
                foreach ($params['kLink_arr'] as $kLink) {
                    if ((int)$kLink > 0) {
                        $ins                          = new \stdClass();
                        $ins->kAuswahlAssistentGruppe = $groupID;
                        $ins->cKey                    = \AUSWAHLASSISTENT_ORT_LINK;
                        $ins->kKey                    = $kLink;

                        \Shop::Container()->getDB()->insert('tauswahlassistentort', $ins);
                    }
                }
            }
            if (isset($params['nStartseite']) && (int)$params['nStartseite'] === 1) {
                $ins                          = new \stdClass();
                $ins->kAuswahlAssistentGruppe = $groupID;
                $ins->cKey                    = \AUSWAHLASSISTENT_ORT_STARTSEITE;
                $ins->kKey                    = 1;

                \Shop::Container()->getDB()->insert('tauswahlassistentort', $ins);
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
        if ($groupID > 0 && \is_array($params) && \count($params) > 0) {
            $rows = \Shop::Container()->getDB()->delete(
                'tauswahlassistentort',
                'kAuswahlAssistentGruppe',
                $groupID
            );
        }

        return $rows > 0 && self::saveLocation($params, $groupID);
    }

    /**
     * @param array $params
     * @param bool  $update
     * @return array
     */
    public static function checkLocation(array $params, bool $update = false): array
    {
        $checks = [];
        // Ort
        if ((!isset($params['cKategorie']) || \mb_strlen($params['cKategorie']) === 0)
            && (!isset($params['kLink_arr'])
                || !\is_array($params['kLink_arr'])
                || \count($params['kLink_arr']) === 0)
            && $params['nStartseite'] == 0
        ) {
            $checks['cOrt'] = 1;
        }
        // Ort Kategorie
        if (isset($params['cKategorie']) && \mb_strlen($params['cKategorie']) > 0) {
            $categories = \explode(';', $params['cKategorie']);
            if (!\is_array($categories) || \count($categories) === 0) {
                $checks['cKategorie'] = 1;
            }
            if (!\is_numeric($categories[0])) {
                $checks['cKategorie'] = 2;
            }
            foreach ($categories as $cKategorie) {
                if ((int)$cKategorie > 0 && \mb_strlen($cKategorie) > 0) {
                    if ($update) {
                        if (self::isCategoryTaken(
                            $cKategorie,
                            $params['kSprache'],
                            $params['kAuswahlAssistentGruppe']
                        )) {
                            $checks['cKategorie'] = 3;
                        }
                    } elseif (self::isCategoryTaken($cKategorie, $params['kSprache'])) {
                        $checks['cKategorie'] = 3;
                    }
                }
            }
        }
        // Ort Spezialseite
        if (isset($params['kLink_arr'])
            && \is_array($params['kLink_arr'])
            && \count($params['kLink_arr']) > 0
        ) {
            foreach ($params['kLink_arr'] as $kLink) {
                if ((int)$kLink > 0) {
                    if ($update) {
                        if (self::isLinkTaken(
                            $kLink,
                            $params['kSprache'],
                            $params['kAuswahlAssistentGruppe']
                        )) {
                            $checks['kLink_arr'] = 1;
                        }
                    } elseif (self::isLinkTaken($kLink, $params['kSprache'])) {
                        $checks['kLink_arr'] = 1;
                    }
                }
            }
        }
        // Ort Startseite
        if (isset($params['nStartseite']) && (int)$params['nStartseite'] === 1) {
            if ($update) {
                if (self::isStartPageTaken(
                    $params['kSprache'],
                    $params['kAuswahlAssistentGruppe']
                )) {
                    $checks['nStartseite'] = 1;
                }
            } elseif (self::isStartPageTaken($params['kSprache'])) {
                $checks['nStartseite'] = 1;
            }
        }

        return $checks;
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
        $locationSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $item        = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $locationSQL . '
                    AND o.kKey = :catID',
            [
                'keyID'  => \AUSWAHLASSISTENT_ORT_KATEGORIE,
                'catID'  => $kKategorie,
                'langID' => $kSprache
            ],
            ReturnType::SINGLE_OBJECT
        );

        return isset($item->kAuswahlAssistentOrt) && $item->kAuswahlAssistentOrt > 0;
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
        $oOrt    = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $cOrtSQL . '
                    AND o.kKey = :linkID',
            [
                'langID' => $kSprache,
                'keyID'  => \AUSWAHLASSISTENT_ORT_LINK,
                'linkID' => $kLink
            ],
            ReturnType::SINGLE_OBJECT
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
        $locationSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $item        = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $locationSQL . '
                    AND o.kKey = 1',
            ['langID' => $kSprache, 'keyID' => \AUSWAHLASSISTENT_ORT_STARTSEITE],
            ReturnType::SINGLE_OBJECT
        );

        return isset($item->kAuswahlAssistentOrt) && $item->kAuswahlAssistentOrt > 0;
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
        if ($kKey > 0 && $kSprache > 0 && \mb_strlen($cKey) > 0) {
            $item = \Shop::Container()->getDB()->executeQueryPrepared(
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
                ReturnType::SINGLE_OBJECT
            );

            if (isset($item->kAuswahlAssistentOrt) && $item->kAuswahlAssistentOrt > 0) {
                return new self((int)$item->kAuswahlAssistentOrt, 0, $bBackend);
            }
        }

        return null;
    }
}
