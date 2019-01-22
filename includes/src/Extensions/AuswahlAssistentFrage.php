<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

/**
 * Class AuswahlAssistentFrage
 *
 * @package Extensions
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
    private function loadFromDB(int $questionID, bool $activeOnly = true): void
    {
        $oDbResult = \Shop::Container()->getDB()->query(
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
            foreach (\get_object_vars($oDbResult) as $name => $value) {
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
        $questions = [];
        if ($groupID > 0) {
            $cAktivSQL = '';
            if ($activeOnly) {
                $cAktivSQL = ' AND nAktiv = 1';
            }
            $data = \Shop::Container()->getDB()->query(
                'SELECT *
                    FROM tauswahlassistentfrage
                    WHERE kAuswahlAssistentGruppe = ' . $groupID .
                    $cAktivSQL . '
                    ORDER BY nSort',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($data as $question) {
                $questions[] = new self((int)$question->kAuswahlAssistentFrage, $activeOnly);
            }
        }

        return $questions;
    }

    /**
     * @param bool $bPrimary
     * @return array|bool
     */
    public function saveQuestion(bool $bPrimary = false)
    {
        $checks = $this->checkQuestion();
        if (\count($checks) === 0) {
            $ins                          = new \stdClass();
            $ins->kAuswahlAssistentFrage  = $this->kAuswahlAssistentFrage;
            $ins->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
            $ins->kMerkmal                = $this->kMerkmal;
            $ins->cFrage                  = $this->cFrage;
            $ins->nSort                   = $this->nSort;
            $ins->nAktiv                  = $this->nAktiv;
            $kAuswahlAssistentFrage       = \Shop::Container()->getDB()->insert('tauswahlassistentfrage', $ins);

            if ($kAuswahlAssistentFrage > 0) {
                return $bPrimary ? $kAuswahlAssistentFrage : true;
            }

            return false;
        }

        return $checks;
    }

    /**
     * @return array|bool
     */
    public function updateQuestion()
    {
        $checks = $this->checkQuestion(true);
        if (\count($checks) === 0) {
            $upd                          = new \stdClass();
            $upd->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
            $upd->kMerkmal                = $this->kMerkmal;
            $upd->cFrage                  = $this->cFrage;
            $upd->nSort                   = $this->nSort;
            $upd->nAktiv                  = $this->nAktiv;

            \Shop::Container()->getDB()->update(
                'tauswahlassistentfrage',
                'kAuswahlAssistentFrage',
                (int)$this->kAuswahlAssistentFrage,
                $upd
            );

            return true;
        }

        return $checks;
    }

    /**
     * @param array $params
     * @return bool
     */
    public static function deleteQuestion(array $params): bool
    {
        if (isset($params['kAuswahlAssistentFrage_arr'])
            && \is_array($params['kAuswahlAssistentFrage_arr'])
            && \count($params['kAuswahlAssistentFrage_arr']) > 0
        ) {
            foreach ($params['kAuswahlAssistentFrage_arr'] as $kAuswahlAssistentFrage) {
                \Shop::Container()->getDB()->delete(
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
     * @param bool $update
     * @return array
     */
    public function checkQuestion(bool $update = false): array
    {
        $checks = [];
        // Frage
        if (\strlen($this->cFrage) === 0) {
            $checks['cFrage'] = 1;
        }
        // Gruppe
        if ($this->kAuswahlAssistentGruppe === null
            || $this->kAuswahlAssistentGruppe === 0
            || $this->kAuswahlAssistentGruppe === -1
        ) {
            $checks['kAuswahlAssistentGruppe'] = 1;
        }
        // Merkmal
        if ($this->kMerkmal === null || $this->kMerkmal === 0 || $this->kMerkmal === -1) {
            $checks['kMerkmal'] = 1;
        }
        if (!$update && $this->isMerkmalTaken($this->kMerkmal, $this->kAuswahlAssistentGruppe)) {
            $checks['kMerkmal'] = 2;
        }
        // Sortierung
        if ($this->nSort <= 0) {
            $checks['nSort'] = 1;
        }
        // Aktiv
        if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
            $checks['nAktiv'] = 1;
        }

        return $checks;
    }

    /**
     * @param int $kMerkmal
     * @param int $kAuswahlAssistentGruppe
     * @return bool
     */
    private function isMerkmalTaken(int $kMerkmal, int $kAuswahlAssistentGruppe): bool
    {
        if ($kMerkmal > 0 && $kAuswahlAssistentGruppe > 0) {
            $oFrage = \Shop::Container()->getDB()->select(
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
     * @return \Merkmal|\stdClass
     */
    public static function getMerkmal(int $kMerkmal, bool $bMMW = false)
    {
        return $kMerkmal > 0
            ? new \Merkmal($kMerkmal, $bMMW)
            : new \stdClass();
    }
}
