<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\ObjectHelper;

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
     * @param bool $activeOnly
     * @param bool $bBackend
     */
    public function __construct(int $groupID = 0, bool $bAktiv = true, bool $activeOnly = true, bool $bBackend = false)
    {
        if ($groupID > 0) {
            $this->loadFromDB($groupID, $bAktiv, $activeOnly, $bBackend);
        }
    }

    /**
     * @param int  $groupID
     * @param bool $bAktiv
     * @param bool $activeOnly
     * @param bool $bBackend
     */
    private function loadFromDB(int $groupID, bool $bAktiv, bool $activeOnly, bool $bBackend): void
    {
        if ($groupID > 0) {
            $cAktivSQL = $bAktiv ? ' AND nAktiv = 1' : '';
            $oGruppe   = Shop::Container()->getDB()->queryPrepared(
                'SELECT *
                    FROM tauswahlassistentgruppe
                    WHERE kAuswahlAssistentGruppe = :groupID' .
                    $cAktivSQL,
                ['groupID' => $groupID],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) {
                $cMember_arr = array_keys(get_object_vars($oGruppe));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oGruppe->$cMember;
                }
                $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                $this->kSprache                = (int)$this->kSprache;
                $this->nAktiv                  = (int)$this->nAktiv;
                // Fragen
                $this->oAuswahlAssistentFrage_arr = AuswahlAssistentFrage::getQuestions(
                    $oGruppe->kAuswahlAssistentGruppe,
                    $activeOnly
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
     * @param int  $langID
     * @param bool $active
     * @param bool $activeOnly
     * @param bool $backend
     * @return array
     */
    public static function getGroups(
        int $langID,
        bool $active = true,
        bool $activeOnly = true,
        bool $backend = false
    ): array {
        $groups    = [];
        $activeSQL = $active ? ' AND nAktiv = 1' : '';
        $groupData = Shop::Container()->getDB()->queryPrepared(
            'SELECT kAuswahlAssistentGruppe
                FROM tauswahlassistentgruppe
                WHERE kSprache = :langID' . $activeSQL,
            ['langID' => $langID],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($groupData as $oGruppeTMP) {
            $groups[] = new self($oGruppeTMP->kAuswahlAssistentGruppe, $active, $activeOnly, $backend);
        }

        return $groups;
    }

    /**
     * @param array $params
     * @param bool  $primary
     * @return array|bool
     */
    public function saveGroup(array $params, bool $primary = false)
    {
        $cPlausi_arr = $this->checkGroup($params);
        if (count($cPlausi_arr) === 0) {
            $oObj = ObjectHelper::copyMembers($this);

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
            $groupID = Shop::Container()->getDB()->insert('tauswahlassistentgruppe', $oObj);
            if ($groupID > 0) {
                AuswahlAssistentOrt::saveLocation($params, $groupID);

                return $primary ? $groupID : true;
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
        $validation = $this->checkGroup($cParam_arr, true);
        if (count($validation) === 0) {
            $upd                = new stdClass();
            $upd->kSprache      = $this->kSprache;
            $upd->cName         = $this->cName;
            $upd->cBeschreibung = $this->cBeschreibung;
            $upd->nAktiv        = $this->nAktiv;

            Shop::Container()->getDB()->update(
                'tauswahlassistentgruppe',
                'kAuswahlAssistentGruppe',
                (int)$this->kAuswahlAssistentGruppe,
                $upd
            );
            AuswahlAssistentOrt::updateLocation($cParam_arr, $this->kAuswahlAssistentGruppe);

            return true;
        }

        return $validation;
    }

    /**
     * @param array $params
     * @param bool  $update
     * @return array
     */
    public function checkGroup(array $params, bool $update = false): array
    {
        $validation = [];
        if (empty($this->cName)) {
            $validation['cName'] = 1;
        }
        if ($this->kSprache === 0) {
            $validation['kSprache'] = 1;
        }
        if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
            $validation['nAktiv'] = 1;
        }
        $location   = AuswahlAssistentOrt::checkLocation($params, $update);
        $validation = array_merge($location, $validation);

        return $validation;
    }

    /**
     * @param array $cParam_arr
     * @return bool
     */
    public static function deleteGroup(array $cParam_arr): bool
    {
        if (!isset($cParam_arr['kAuswahlAssistentGruppe_arr'])
            || !is_array($cParam_arr['kAuswahlAssistentGruppe_arr'])
            || count($cParam_arr['kAuswahlAssistentGruppe_arr']) === 0
        ) {
            return false;
        }
        foreach ($cParam_arr['kAuswahlAssistentGruppe_arr'] as $groupID) {
            Shop::Container()->getDB()->queryPrepared(
                'DELETE tag, taf, tao
                    FROM tauswahlassistentgruppe tag
                    LEFT JOIN tauswahlassistentfrage taf
                        ON taf.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                    LEFT JOIN tauswahlassistentort tao
                        ON tao.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                    WHERE tag.kAuswahlAssistentGruppe = :groupID',
                ['groupID' => (int)$groupID],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        return true;
    }

    /**
     * @param int $groupID
     * @return int
     */
    public static function getLanguage(int $groupID): int
    {
        if ($groupID > 0) {
            $oGruppe = Shop::Container()->getDB()->queryPrepared(
                'SELECT kSprache
                    FROM tauswahlassistentgruppe
                    WHERE kAuswahlAssistentGruppe = :groupID',
                ['groupID' => $groupID],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oGruppe->kSprache) && $oGruppe->kSprache > 0) {
                return (int)$oGruppe->kSprache;
            }
        }

        return 0;
    }
}
