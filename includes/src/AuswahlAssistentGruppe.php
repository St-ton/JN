<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AuswahlAssistentGruppe
 */
class AuswahlAssistentGruppe
{
    /**
     * @var int
     */
    public $kAuswahlAssistentGruppe;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var array
     */
    public $oAuswahlAssistentFrage_arr;

    /**
     * @var array
     */
    public $oAuswahlAssistentOrt_arr;

    /**
     * @var string
     */
    public $cSprache;

    /**
     * @var int
     */
    public $nStartseite;

    /**
     * @var string
     */
    public $cKategorie;

    /**
     * @param int  $groupID
     * @param bool $bAktiv
     * @param bool $bAktivFrage
     * @param bool $bBackend
     */
    public function __construct(int $groupID = 0, bool $bAktiv = true, bool $bAktivFrage = true, bool $bBackend = false)
    {
        if ($groupID > 0) {
            $this->loadFromDB($groupID, $bAktiv, $bAktivFrage, $bBackend);
        }
    }

    /**
     * @param int  $kAuswahlAssistentGruppe
     * @param bool $bAktiv
     * @param bool $bAktivFrage
     * @param bool $bBackend
     */
    private function loadFromDB(int $kAuswahlAssistentGruppe, bool $bAktiv, bool $bAktivFrage, bool $bBackend)
    {
        if ($kAuswahlAssistentGruppe > 0) {
            $cAktivSQL = $bAktiv ? ' AND nAktiv = 1' : '';
            $oGruppe   = Shop::Container()->getDB()->queryPrepared(
                "SELECT *
                    FROM tauswahlassistentgruppe
                    WHERE kAuswahlAssistentGruppe = :groupID" .
                    $cAktivSQL,
                ['groupID' => $kAuswahlAssistentGruppe],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) {
                $cMember_arr = array_keys(get_object_vars($oGruppe));
                if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                    foreach ($cMember_arr as $cMember) {
                        $this->$cMember = $oGruppe->$cMember;
                    }
                }
                $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                $this->kSprache                = (int)$this->kSprache;
                $this->nAktiv                  = (int)$this->nAktiv;
                // Fragen
                $this->oAuswahlAssistentFrage_arr = AuswahlAssistentFrage::getQuestions(
                    $oGruppe->kAuswahlAssistentGruppe,
                    $bAktivFrage
                );
                $oAuswahlAssistentOrt             = new AuswahlAssistentOrt(
                    0,
                    $this->kAuswahlAssistentGruppe,
                    $bBackend
                );
                $this->oAuswahlAssistentOrt_arr   = $oAuswahlAssistentOrt->oOrt_arr;
                foreach ($this->oAuswahlAssistentOrt_arr as $oAuswahlAssistentOrt) {
                    // Kategorien
                    if ($oAuswahlAssistentOrt->cKey === AUSWAHLASSISTENT_ORT_KATEGORIE) {
                        $this->cKategorie .= $oAuswahlAssistentOrt->kKey . ';';
                    }
                    // Startseite
                    if ($oAuswahlAssistentOrt->cKey === AUSWAHLASSISTENT_ORT_STARTSEITE) {
                        $this->nStartseite = 1;
                    }
                }
                $oSprache       = Shop::Container()->getDB()->queryPrepared(
                    'SELECT cNameDeutsch 
                        FROM tsprache 
                        WHERE kSprache = :langID',
                    ['langID' => (int)$this->kSprache],
                    \DB\ReturnType::SINGLE_OBJECT
                );
                $this->cSprache = $oSprache->cNameDeutsch;
            }
        }
    }

    /**
     * @param int  $kSprache
     * @param bool $bAktiv
     * @param bool $bAktivFrage
     * @param bool $bBackend
     * @return array
     */
    public static function getGroups(int $kSprache, bool $bAktiv = true, bool $bAktivFrage = true, bool $bBackend = false): array
    {
        $oGruppe_arr    = [];
        $cAktivSQL      = $bAktiv ? ' AND nAktiv = 1' : '';
        $oGruppeTMP_arr = Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentGruppe
                FROM tauswahlassistentgruppe
                WHERE kSprache = :langID' . $cAktivSQL,
            ['langID' => $kSprache],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oGruppeTMP_arr as $oGruppeTMP) {
            $oGruppe_arr[] = new self($oGruppeTMP->kAuswahlAssistentGruppe, $bAktiv, $bAktivFrage, $bBackend);
        }

        return $oGruppe_arr;
    }

    /**
     * @param array $cParam_arr
     * @param bool  $bPrimary
     * @return array|bool
     */
    public function saveGroup(array $cParam_arr, bool $bPrimary = false)
    {
        $cPlausi_arr = $this->checkGroup($cParam_arr);
        if (count($cPlausi_arr) === 0) {
            $oObj = kopiereMembers($this);

            $this->nAktiv                  = (int)$this->nAktiv;
            $this->kSprache                = (int)$this->kSprache;
            $this->nStartseite             = (int)$this->nStartseite;
            $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
            unset(
                $oObj->cSprache,
                $oObj->nStartseite,
                $oObj->cKategorie,
                $oObj->oAuswahlAssistentOrt_arr,
                $oObj->oAuswahlAssistentFrage_arr
            );
            $kAuswahlAssistentGruppe = Shop::Container()->getDB()->insert('tauswahlassistentgruppe', $oObj);
            if ($kAuswahlAssistentGruppe > 0) {
                AuswahlAssistentOrt::saveLocation($cParam_arr, $kAuswahlAssistentGruppe);

                return $bPrimary ? $kAuswahlAssistentGruppe : true;
            }

            return false;
        }

        return $cPlausi_arr;
    }

    /**
     * @param array $cParam_arr
     * @return array|bool
     */
    public function updateGroup(array $cParam_arr)
    {
        $cPlausi_arr = $this->checkGroup($cParam_arr, true);
        if (count($cPlausi_arr) === 0) {
            $_upd                = new stdClass();
            $_upd->kSprache      = $this->kSprache;
            $_upd->cName         = $this->cName;
            $_upd->cBeschreibung = $this->cBeschreibung;
            $_upd->nAktiv        = $this->nAktiv;

            Shop::Container()->getDB()->update(
                'tauswahlassistentgruppe',
                'kAuswahlAssistentGruppe',
                (int)$this->kAuswahlAssistentGruppe,
                $_upd
            );
            AuswahlAssistentOrt::updateLocation($cParam_arr, $this->kAuswahlAssistentGruppe);

            return true;
        }

        return $cPlausi_arr;
    }

    /**
     * @param array $cParam_arr
     * @param bool  $bUpdate
     * @return array
     */
    public function checkGroup(array $cParam_arr, bool $bUpdate = false): array
    {
        $cPlausi_arr = [];
        // Name
        if (empty($this->cName)) {
            $cPlausi_arr['cName'] = 1;
        }
        // Sprache
        if ($this->kSprache === 0) {
            $cPlausi_arr['kSprache'] = 1;
        }
        // Aktiv
        if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
            $cPlausi_arr['nAktiv'] = 1;
        }
        $cPlausiOrt_arr = AuswahlAssistentOrt::checkLocation($cParam_arr, $bUpdate);
        $cPlausi_arr    = array_merge($cPlausiOrt_arr, $cPlausi_arr);

        return $cPlausi_arr;
    }

    /**
     * @param array $cParam_arr
     * @return bool
     */
    public static function deleteGroup(array $cParam_arr): bool
    {
        if (isset($cParam_arr['kAuswahlAssistentGruppe_arr'])
            && is_array($cParam_arr['kAuswahlAssistentGruppe_arr'])
            && count($cParam_arr['kAuswahlAssistentGruppe_arr']) > 0
        ) {
            foreach ($cParam_arr['kAuswahlAssistentGruppe_arr'] as $kAuswahlAssistentGruppe) {
                Shop::Container()->getDB()->queryPrepared(
                    'DELETE tag, taf, tao
                        FROM tauswahlassistentgruppe tag
                        LEFT JOIN tauswahlassistentfrage taf
                            ON taf.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                        LEFT JOIN tauswahlassistentort tao
                            ON tao.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                        WHERE tag.kAuswahlAssistentGruppe = :groupID', 
                    ['groupID' => (int)$kAuswahlAssistentGruppe],
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $kAuswahlAssistentGruppe
     * @return int
     */
    public static function getLanguage(int $kAuswahlAssistentGruppe): int
    {
        if ($kAuswahlAssistentGruppe > 0) {
            $oGruppe = Shop::Container()->getDB()->queryPrepared(
                'SELECT kSprache
                    FROM tauswahlassistentgruppe
                    WHERE kAuswahlAssistentGruppe = :groupID',
                ['groupID' => $kAuswahlAssistentGruppe],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oGruppe->kSprache) && $oGruppe->kSprache > 0) {
                return (int)$oGruppe->kSprache;
            }
        }

        return 0;
    }
}
