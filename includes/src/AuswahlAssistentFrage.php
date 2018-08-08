<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AuswahlAssistentFrage
 */
class AuswahlAssistentFrage
{
    /**
     * @var int
     */
    public $kAuswahlAssistentFrage = 0;

    /**
     * @var int
     */
    public $kAuswahlAssistentGruppe = 0;

    /**
     * @var int
     */
    public $kMerkmal = 0;

    /**
     * @var string
     */
    public $cFrage = '';

    /**
     * @var int
     */
    public $nSort = 0;

    /**
     * @var int
     */
    public $nAktiv = 0;

    /**
     * @var array
     */
    public $oWert_arr = [];

    /**
     * @var array - mapping from kMerkmalWert to tmerkmalwert object
     */
    public $oWert_assoc = [];

    /**
     * @var int - how many products found that have a value of this attribute
     */
    public $nTotalResultCount = 0;

    /**
     * @var object - used by old AWA
     */
    public $oMerkmal;

    /**
     * @param int  $kAuswahlAssistentFrage
     * @param bool $activeOnly
     */
    public function __construct(int $kAuswahlAssistentFrage = 0, bool $activeOnly = true)
    {
        if ($kAuswahlAssistentFrage > 0) {
            $this->loadFromDB($kAuswahlAssistentFrage, $activeOnly);
        }
    }

    /**
     * @param int  $questionID
     * @param bool $activeOnly
     */
    private function loadFromDB(int $questionID, bool $activeOnly = true)
    {
        $oDbResult = Shop::Container()->getDB()->query(
            'SELECT af.*, m.cBildpfad, COALESCE(ms.cName, m.cName) AS cName, m.cBildpfad
                FROM tauswahlassistentfrage AS af
                    JOIN tauswahlassistentgruppe as ag
                        ON ag.kAuswahlAssistentGruppe = af.kAuswahlAssistentGruppe 
                    JOIN tmerkmal AS m
                        ON m.kMerkmal = af.kMerkmal 
                    LEFT JOIN tmerkmalsprache AS ms
                        ON ms.kMerkmal = m.kMerkmal 
                            AND ms.kSprache = ag.kSprache
                WHERE af.kAuswahlAssistentFrage = ' . $questionID .
                    ($activeOnly ? ' AND af.nAktiv = 1' : ''),
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($oDbResult !== null && $oDbResult !== false) {
            foreach (get_object_vars($oDbResult) as $name => $value) {
                $this->$name = $value;
            }
            $this->kAuswahlAssistentFrage  = (int)$this->kAuswahlAssistentFrage;
            $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
            $this->kMerkmal                = (int)$this->kMerkmal;
            $this->nSort                   = (int)$this->nSort;
            $this->nAktiv                  = (int)$this->nAktiv;
        }
    }

    /**
     * @param int  $groupID
     * @param bool $activeOnly
     * @return array
     */
    public static function getQuestions(int $groupID, bool $activeOnly = true): array
    {
        $oAuswahlAssistentFrage_arr = [];
        if ($groupID > 0) {
            $cAktivSQL = '';
            if ($activeOnly) {
                $cAktivSQL = ' AND nAktiv = 1';
            }
            $oFrage_arr = Shop::Container()->getDB()->query(
                'SELECT *
                    FROM tauswahlassistentfrage
                    WHERE kAuswahlAssistentGruppe = ' . $groupID .
                    $cAktivSQL . '
                    ORDER BY nSort',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oFrage_arr as $oFrage) {
                $oAuswahlAssistentFrage_arr[] = new self($oFrage->kAuswahlAssistentFrage, $activeOnly);
            }
        }

        return $oAuswahlAssistentFrage_arr;
    }

    /**
     * @param bool $bPrimary
     * @return array|bool
     */
    public function saveQuestion(bool $bPrimary = false)
    {
        $cPlausi_arr = $this->checkQuestion();
        if (count($cPlausi_arr) === 0) {
            $obj                          = new stdClass();
            $obj->kAuswahlAssistentFrage  = $this->kAuswahlAssistentFrage;
            $obj->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
            $obj->kMerkmal                = $this->kMerkmal;
            $obj->cFrage                  = $this->cFrage;
            $obj->nSort                   = $this->nSort;
            $obj->nAktiv                  = $this->nAktiv;
            $kAuswahlAssistentFrage       = Shop::Container()->getDB()->insert('tauswahlassistentfrage', $obj);

            if ($kAuswahlAssistentFrage > 0) {
                return $bPrimary ? $kAuswahlAssistentFrage : true;
            }

            return false;
        }

        return $cPlausi_arr;
    }

    /**
     * @return array|bool
     */
    public function updateQuestion()
    {
        $cPlausi_arr = $this->checkQuestion(true);
        if (count($cPlausi_arr) === 0) {
            $_upd                          = new stdClass();
            $_upd->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
            $_upd->kMerkmal                = $this->kMerkmal;
            $_upd->cFrage                  = $this->cFrage;
            $_upd->nSort                   = $this->nSort;
            $_upd->nAktiv                  = $this->nAktiv;

            Shop::Container()->getDB()->update(
                'tauswahlassistentfrage',
                'kAuswahlAssistentFrage',
                (int)$this->kAuswahlAssistentFrage,
                $_upd
            );

            return true;
        }

        return $cPlausi_arr;
    }

    /**
     * @param array $cParam_arr
     * @return bool
     */
    public static function deleteQuestion(array $cParam_arr): bool
    {
        if (isset($cParam_arr['kAuswahlAssistentFrage_arr'])
            && is_array($cParam_arr['kAuswahlAssistentFrage_arr'])
            && count($cParam_arr['kAuswahlAssistentFrage_arr']) > 0
        ) {
            foreach ($cParam_arr['kAuswahlAssistentFrage_arr'] as $kAuswahlAssistentFrage) {
                Shop::Container()->getDB()->delete(
                    'tauswahlassistentfrage',
                    'kAuswahlAssistentFrage',
                    (int)$kAuswahlAssistentFrage
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @param bool $bUpdate
     * @return array
     */
    public function checkQuestion(bool $bUpdate = false): array
    {
        $cPlausi_arr = [];
        // Frage
        if (strlen($this->cFrage) === 0) {
            $cPlausi_arr['cFrage'] = 1;
        }
        // Gruppe
        if ($this->kAuswahlAssistentGruppe === null ||
            $this->kAuswahlAssistentGruppe === 0 ||
            $this->kAuswahlAssistentGruppe === -1
        ) {
            $cPlausi_arr['kAuswahlAssistentGruppe'] = 1;
        }
        // Merkmal
        if ($this->kMerkmal === null || $this->kMerkmal === 0 || $this->kMerkmal === -1) {
            $cPlausi_arr['kMerkmal'] = 1;
        }
        if (!$bUpdate && $this->isMerkmalTaken($this->kMerkmal, $this->kAuswahlAssistentGruppe)) {
            $cPlausi_arr['kMerkmal'] = 2;
        }
        // Sortierung
        if ($this->nSort <= 0) {
            $cPlausi_arr['nSort'] = 1;
        }
        // Aktiv
        if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
            $cPlausi_arr['nAktiv'] = 1;
        }

        return $cPlausi_arr;
    }

    /**
     * @param int $kMerkmal
     * @param int $kAuswahlAssistentGruppe
     * @return bool
     */
    private function isMerkmalTaken(int $kMerkmal, int $kAuswahlAssistentGruppe): bool
    {
        if ($kMerkmal > 0 && $kAuswahlAssistentGruppe > 0) {
            $oFrage = Shop::Container()->getDB()->select(
                'tauswahlassistentfrage',
                'kMerkmal',
                $kMerkmal,
                'kAuswahlAssistentGruppe',
                $kAuswahlAssistentGruppe
            );

            return isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0;
        }

        return false;
    }

    /**
     * @param int  $kMerkmal
     * @param bool $bMMW
     * @return Merkmal|stdClass
     */
    public static function getMerkmal(int $kMerkmal, bool $bMMW = false)
    {
        return $kMerkmal > 0
            ? new Merkmal($kMerkmal, $bMMW)
            : new stdClass();
    }
}
