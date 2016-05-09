<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (class_exists('AuswahlAssistent')) {
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
         * @param int  $kAuswahlAssistentOrt
         * @param int  $kAuswahlAssistentGruppe
         * @param bool $bBackend
         */
        public function __construct($kAuswahlAssistentOrt = 0, $kAuswahlAssistentGruppe = 0, $bBackend = false)
        {
            if ($kAuswahlAssistentOrt > 0 || $kAuswahlAssistentGruppe > 0) {
                $this->loadFromDB($kAuswahlAssistentOrt, $kAuswahlAssistentGruppe, $bBackend);
            }
        }

        /**
         * @param int  $kAuswahlAssistentOrt
         * @param int  $kAuswahlAssistentGruppe
         * @param bool $bBackend
         */
        private function loadFromDB($kAuswahlAssistentOrt, $kAuswahlAssistentGruppe, $bBackend)
        {
            if ($kAuswahlAssistentGruppe > 0) {
                $this->oOrt_arr = array();
                $oOrtTMP_arr    = Shop::DB()->query(
                    "SELECT *
                        FROM tauswahlassistentort
                        WHERE kAuswahlAssistentGruppe = " . (int)$kAuswahlAssistentGruppe, 2
                );
                if (is_array($oOrtTMP_arr) && count($oOrtTMP_arr) > 0) {
                    foreach ($oOrtTMP_arr as $oOrtTMP) {
                        $this->oOrt_arr[] = new self($oOrtTMP->kAuswahlAssistentOrt, 0, $bBackend);
                    }
                }
            } elseif ($kAuswahlAssistentOrt > 0) {
                $oOrt = Shop::DB()->query(
                    "SELECT *
                        FROM tauswahlassistentort
                        WHERE kAuswahlAssistentOrt = " . (int)$kAuswahlAssistentOrt, 1
                );
                if (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0) {
                    $cMember_arr = array_keys(get_object_vars($oOrt));
                    if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                        foreach ($cMember_arr as $cMember) {
                            $this->$cMember = $oOrt->$cMember;
                        }
                    }
                    // cKey Mapping
                    switch ($this->cKey) {
                        case AUSWAHLASSISTENT_ORT_KATEGORIE:
                            if ($bBackend) {
                                unset($_SESSION['oKategorie_arr']);
                                unset($_SESSION['oKategorie_arr_new']);
                            }
                            $oKategorie = new Kategorie($this->kKey, AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe));

                            $this->cOrt = $oKategorie->cName . "(Kategorie)";
                            break;

                        case AUSWAHLASSISTENT_ORT_LINK:
                            $oSprache = Shop::DB()->query(
                                "SELECT cISO
                                    FROM tsprache
                                    WHERE kSprache = " . AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe), 1
                            );

                            $oLink = Shop::DB()->query(
                                "SELECT cName
                                    FROM tlinksprache
                                    WHERE kLink = " . $this->kKey . "
                                        AND cISOSprache = '" . $oSprache->cISO . "'", 1
                            );
                            $this->cOrt = (isset($oLink->cName)) ? ($oLink->cName . '(CMS)') : null;
                            break;

                        case AUSWAHLASSISTENT_ORT_STARTSEITE:
                            $this->cOrt = 'Startseite';
                            break;
                    }
                }
            }
        }

        /**
         * @param array $cParam_arr
         * @param int   $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function saveLocation($cParam_arr, $kAuswahlAssistentGruppe)
        {
            $kAuswahlAssistentGruppe = (int)$kAuswahlAssistentGruppe;
            if (is_array($cParam_arr) && count($cParam_arr) > 0 && $kAuswahlAssistentGruppe > 0) {
                // Kategorie
                if (isset($cParam_arr['cKategorie']) && strlen($cParam_arr['cKategorie']) > 0) {
                    $cKategorie_arr = explode(';', $cParam_arr['cKategorie']);
                    if (is_array($cKategorie_arr) && count($cKategorie_arr) > 0) {
                        foreach ($cKategorie_arr as $cKategorie) {
                            if (strlen($cKategorie) > 0 && intval($cKategorie) > 0) {
                                $oOrt                          = new stdClass();
                                $oOrt->kAuswahlAssistentGruppe = $kAuswahlAssistentGruppe;
                                $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_KATEGORIE;
                                $oOrt->kKey                    = $cKategorie;

                                Shop::DB()->insert('tauswahlassistentort', $oOrt);
                            }
                        }
                    }
                }
                // Spezialseite
                if (isset($cParam_arr['kLink_arr']) && is_array($cParam_arr['kLink_arr']) && count($cParam_arr['kLink_arr']) > 0) {
                    foreach ($cParam_arr['kLink_arr'] as $kLink) {
                        if (intval($kLink) > 0) {
                            $oOrt                          = new stdClass();
                            $oOrt->kAuswahlAssistentGruppe = $kAuswahlAssistentGruppe;
                            $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_LINK;
                            $oOrt->kKey                    = $kLink;

                            Shop::DB()->insert('tauswahlassistentort', $oOrt);
                        }
                    }
                }
                // Startseite
                if (isset($cParam_arr['nStartseite']) && intval($cParam_arr['nStartseite']) === 1) {
                    $oOrt                          = new stdClass();
                    $oOrt->kAuswahlAssistentGruppe = $kAuswahlAssistentGruppe;
                    $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_STARTSEITE;
                    $oOrt->kKey                    = 1;

                    Shop::DB()->insert('tauswahlassistentort', $oOrt);
                }
            }

            return false;
        }

        /**
         * @param array $cParam_arr
         * @param int   $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function updateLocation($cParam_arr, $kAuswahlAssistentGruppe)
        {
            $kAuswahlAssistentGruppe = (int)$kAuswahlAssistentGruppe;
            if (is_array($cParam_arr) && count($cParam_arr) > 0 && $kAuswahlAssistentGruppe > 0) {
                $nRow = Shop::DB()->query(
                    "DELETE FROM tauswahlassistentort
                        WHERE kAuswahlAssistentGruppe = " . $kAuswahlAssistentGruppe, 3
                );

                if ($nRow > 0) {
                    if (self::saveLocation($cParam_arr, $kAuswahlAssistentGruppe)) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * @param array $cParam_arr
         * @param bool  $bUpdate
         * @return array
         */
        public static function checkLocation($cParam_arr, $bUpdate = false)
        {
            $cPlausi_arr = array();
            // Ort
            if ((!isset($cParam_arr['cKategorie']) || strlen($cParam_arr['cKategorie']) === 0)
                && (!isset($cParam_arr['kLink_arr']) || !is_array($cParam_arr['kLink_arr']) || count($cParam_arr['kLink_arr']) === 0)
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
                    if (strlen($cKategorie) > 0 && intval($cKategorie) > 0) {
                        if ($bUpdate) {
                            if (self::isCategoryTaken($cKategorie, $cParam_arr['kSprache'], $cParam_arr['kAuswahlAssistentGruppe'])) {
                                $cPlausi_arr['cKategorie'] = 3;
                            }
                        } else {
                            if (self::isCategoryTaken($cKategorie, $cParam_arr['kSprache'])) {
                                $cPlausi_arr['cKategorie'] = 3;
                            }
                        }
                    }
                }
            }
            // Ort Spezialseite
            if (isset($cParam_arr['kLink_arr']) && is_array($cParam_arr['kLink_arr']) && count($cParam_arr['kLink_arr']) > 0) {
                foreach ($cParam_arr['kLink_arr'] as $kLink) {
                    if (intval($kLink) > 0) {
                        if ($bUpdate) {
                            if (self::isLinkTaken($kLink, $cParam_arr['kSprache'], $cParam_arr['kAuswahlAssistentGruppe'])) {
                                $cPlausi_arr['kLink_arr'] = 1;
                            }
                        } else {
                            if (self::isLinkTaken($kLink, $cParam_arr['kSprache'])) {
                                $cPlausi_arr['kLink_arr'] = 1;
                            }
                        }
                    }
                }
            }
            // Ort Startseite
            if (isset($cParam_arr['nStartseite']) && intval($cParam_arr['nStartseite']) === 1) {
                if ($bUpdate) {
                    if (self::isStartPageTaken($cParam_arr['kSprache'], $cParam_arr['kAuswahlAssistentGruppe'])) {
                        $cPlausi_arr['nStartseite'] = 1;
                    }
                } else {
                    if (self::isStartPageTaken($cParam_arr['kSprache'])) {
                        $cPlausi_arr['nStartseite'] = 1;
                    }
                }
            }

            return $cPlausi_arr;
        }

        /**
         * @param int $kKategorie
         * @param int $kSprache
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function isCategoryTaken($kKategorie, $kSprache, $kAuswahlAssistentGruppe = 0)
        {
            if (intval($kKategorie) === 0 || intval($kSprache) === 0) {
                return false;
            }
            $cOrtSQL = '';
            if ($kAuswahlAssistentGruppe > 0) {
                $cOrtSQL = " AND tauswahlassistentort.kAuswahlAssistentGruppe != " . (int)$kAuswahlAssistentGruppe;
            }
            $oOrt = Shop::DB()->query(
                "SELECT kAuswahlAssistentOrt
                    FROM tauswahlassistentort
                    JOIN tauswahlassistentgruppe ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                        AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                    WHERE tauswahlassistentort.cKey = '" . AUSWAHLASSISTENT_ORT_KATEGORIE . "'
                        " . $cOrtSQL . "
                        AND tauswahlassistentort.kKey = " . (int)$kKategorie, 1
            );

            return (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0);
        }

        /**
         * @param int $kLink
         * @param int $kSprache
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function isLinkTaken($kLink, $kSprache, $kAuswahlAssistentGruppe = 0)
        {
            if (intval($kLink) === 0 || intval($kSprache) === 0) {
                return false;
            }
            $cOrtSQL = '';
            if ($kAuswahlAssistentGruppe > 0) {
                $cOrtSQL = " AND tauswahlassistentort.kAuswahlAssistentGruppe != " . (int)$kAuswahlAssistentGruppe;
            }
            $oOrt = Shop::DB()->query(
                "SELECT kAuswahlAssistentOrt
                    FROM tauswahlassistentort
                    JOIN tauswahlassistentgruppe ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                        AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                    WHERE tauswahlassistentort.cKey = '" . AUSWAHLASSISTENT_ORT_LINK . "'
                        " . $cOrtSQL . "
                        AND tauswahlassistentort.kKey = " . (int)$kLink, 1
            );

            return (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0);
        }

        /**
         * @param int $kSprache
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function isStartPageTaken($kSprache, $kAuswahlAssistentGruppe = 0)
        {
            if (intval($kSprache) === 0) {
                return false;
            }
            $cOrtSQL = '';
            if ($kAuswahlAssistentGruppe > 0) {
                $cOrtSQL = " AND tauswahlassistentort.kAuswahlAssistentGruppe != " . (int)$kAuswahlAssistentGruppe;
            }

            $oOrt = Shop::DB()->query(
                "SELECT kAuswahlAssistentOrt
                    FROM tauswahlassistentort
                    JOIN tauswahlassistentgruppe ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                        AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                    WHERE tauswahlassistentort.cKey = '" . AUSWAHLASSISTENT_ORT_STARTSEITE . "'
                        " . $cOrtSQL . "
                        AND tauswahlassistentort.kKey = 1", 1
            );

            return (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0);
        }

        /**
         * @param string $cKey
         * @param int    $kKey
         * @param int    $kSprache
         * @param bool   $bBackend
         * @return AuswahlAssistentOrt|null
         */
        public static function getLocation($cKey, $kKey, $kSprache, $bBackend = false)
        {
            if (strlen($cKey) > 0 && intval($kKey) > 0 && intval($kSprache) > 0) {
                $oOrt = Shop::DB()->query(
                    "SELECT kAuswahlAssistentOrt
                        FROM tauswahlassistentort
                        JOIN tauswahlassistentgruppe ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                            AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                        WHERE tauswahlassistentort.cKey = '" . Shop::DB()->escape($cKey) . "'
                            AND tauswahlassistentort.kKey = " . (int)$kKey, 1
                );

                if (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0) {
                    return new self($oOrt->kAuswahlAssistentOrt, 0, $bBackend);
                }
            }

            return;
        }
    }
}
