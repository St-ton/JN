<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Shop;
use stdClass;

/**
 * Class AuswahlAssistentGruppe
 * @package JTL\Extensions
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
            $activeSQL = $bAktiv ? ' AND nAktiv = 1' : '';
            $group     = Shop::Container()->getDB()->queryPrepared(
                'SELECT *
                    FROM tauswahlassistentgruppe
                    WHERE kAuswahlAssistentGruppe = :groupID' .
                $activeSQL,
                ['groupID' => $groupID],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($group->kAuswahlAssistentGruppe) && $group->kAuswahlAssistentGruppe > 0) {
                foreach (\array_keys(\get_object_vars($group)) as $member) {
                    $this->$member = $group->$member;
                }
                $this->kAuswahlAssistentGruppe    = (int)$this->kAuswahlAssistentGruppe;
                $this->kSprache                   = (int)$this->kSprache;
                $this->nAktiv                     = (int)$this->nAktiv;
                $this->oAuswahlAssistentFrage_arr = AuswahlAssistentFrage::getQuestions(
                    $group->kAuswahlAssistentGruppe,
                    $activeOnly
                );
                $oAuswahlAssistentOrt             = new AuswahlAssistentOrt(
                    0,
                    $this->kAuswahlAssistentGruppe,
                    $bBackend
                );
                $this->oAuswahlAssistentOrt_arr   = $oAuswahlAssistentOrt->oOrt_arr;
                foreach ($this->oAuswahlAssistentOrt_arr as $oAuswahlAssistentOrt) {
                    if ($oAuswahlAssistentOrt->cKey === \AUSWAHLASSISTENT_ORT_KATEGORIE) {
                        $this->cKategorie .= $oAuswahlAssistentOrt->kKey . ';';
                    }
                    if ($oAuswahlAssistentOrt->cKey === \AUSWAHLASSISTENT_ORT_STARTSEITE) {
                        $this->nStartseite = 1;
                    }
                }
                $language       = Shop::Container()->getDB()->queryPrepared(
                    'SELECT cNameDeutsch 
                        FROM tsprache 
                        WHERE kSprache = :langID',
                    ['langID' => (int)$this->kSprache],
                    ReturnType::SINGLE_OBJECT
                );
                $this->cSprache = $language->cNameDeutsch;
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
            ReturnType::ARRAY_OF_OBJECTS
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
        $checks = $this->checkGroup($params);
        if (\count($checks) === 0) {
            $oObj = GeneralObject::copyMembers($this);

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

        return $checks;
    }

    /**
     * @param array $cParam_arr
     * @return array|bool
     */
    public function updateGroup(array $cParam_arr)
    {
        $validation = $this->checkGroup($cParam_arr, true);
        if (\count($validation) === 0) {
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
        $validation = \array_merge($location, $validation);

        return $validation;
    }

    /**
     * @param array $params
     * @return bool
     */
    public static function deleteGroup(array $params): bool
    {
        if (!isset($params['kAuswahlAssistentGruppe_arr'])
            || !\is_array($params['kAuswahlAssistentGruppe_arr'])
            || \count($params['kAuswahlAssistentGruppe_arr']) === 0
        ) {
            return false;
        }
        foreach ($params['kAuswahlAssistentGruppe_arr'] as $groupID) {
            Shop::Container()->getDB()->queryPrepared(
                'DELETE tag, taf, tao
                    FROM tauswahlassistentgruppe tag
                    LEFT JOIN tauswahlassistentfrage taf
                        ON taf.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                    LEFT JOIN tauswahlassistentort tao
                        ON tao.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                    WHERE tag.kAuswahlAssistentGruppe = :groupID',
                ['groupID' => (int)$groupID],
                ReturnType::AFFECTED_ROWS
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
            $group = Shop::Container()->getDB()->queryPrepared(
                'SELECT kSprache
                    FROM tauswahlassistentgruppe
                    WHERE kAuswahlAssistentGruppe = :groupID',
                ['groupID' => $groupID],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($group->kSprache) && $group->kSprache > 0) {
                return (int)$group->kSprache;
            }
        }

        return 0;
    }
}
