<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\DB\ReturnType;
use JTL\Catalog\Product\Merkmal;
use JTL\Helpers\GeneralObject;
use JTL\Shop;
use stdClass;

/**
 * Class AuswahlAssistentFrage
 * @package JTL\Extensions
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
     * AuswahlAssistentFrage constructor.
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
        $data = Shop::Container()->getDB()->query(
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
            ReturnType::SINGLE_OBJECT
        );
        if ($data !== null && $data !== false) {
            foreach (\get_object_vars($data) as $name => $value) {
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
            $data = Shop::Container()->getDB()->query(
                'SELECT *
                    FROM tauswahlassistentfrage
                    WHERE kAuswahlAssistentGruppe = ' . $groupID .
                    $cAktivSQL . '
                    ORDER BY nSort',
                ReturnType::ARRAY_OF_OBJECTS
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
            $ins                          = new stdClass();
            $ins->kAuswahlAssistentFrage  = $this->kAuswahlAssistentFrage;
            $ins->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
            $ins->kMerkmal                = $this->kMerkmal;
            $ins->cFrage                  = $this->cFrage;
            $ins->nSort                   = $this->nSort;
            $ins->nAktiv                  = $this->nAktiv;
            $kAuswahlAssistentFrage       = Shop::Container()->getDB()->insert('tauswahlassistentfrage', $ins);

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
            $upd                          = new stdClass();
            $upd->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
            $upd->kMerkmal                = $this->kMerkmal;
            $upd->cFrage                  = $this->cFrage;
            $upd->nSort                   = $this->nSort;
            $upd->nAktiv                  = $this->nAktiv;

            Shop::Container()->getDB()->update(
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
        if (GeneralObject::hasCount('kAuswahlAssistentFrage_arr', $params)) {
            foreach ($params['kAuswahlAssistentFrage_arr'] as $questionID) {
                Shop::Container()->getDB()->delete(
                    'tauswahlassistentfrage',
                    'kAuswahlAssistentFrage',
                    (int)$questionID
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
        if (\mb_strlen($this->cFrage) === 0) {
            $checks['cFrage'] = 1;
        }
        if ($this->kAuswahlAssistentGruppe === null
            || $this->kAuswahlAssistentGruppe === 0
            || $this->kAuswahlAssistentGruppe === -1
        ) {
            $checks['kAuswahlAssistentGruppe'] = 1;
        }
        if ($this->kMerkmal === null || $this->kMerkmal === 0 || $this->kMerkmal === -1) {
            $checks['kMerkmal'] = 1;
        }
        if (!$update && $this->isMerkmalTaken($this->kMerkmal, $this->kAuswahlAssistentGruppe)) {
            $checks['kMerkmal'] = 2;
        }
        if ($this->nSort <= 0) {
            $checks['nSort'] = 1;
        }
        if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
            $checks['nAktiv'] = 1;
        }

        return $checks;
    }

    /**
     * @param int $characteristicID
     * @param int $groupID
     * @return bool
     */
    private function isMerkmalTaken(int $characteristicID, int $groupID): bool
    {
        if ($characteristicID > 0 && $groupID > 0) {
            $question = Shop::Container()->getDB()->select(
                'tauswahlassistentfrage',
                'kMerkmal',
                $characteristicID,
                'kAuswahlAssistentGruppe',
                $groupID
            );

            return isset($question->kAuswahlAssistentFrage) && $question->kAuswahlAssistentFrage > 0;
        }

        return false;
    }

    /**
     * @param int  $characteristicID
     * @param bool $value
     * @return Merkmal|stdClass
     */
    public static function getMerkmal(int $characteristicID, bool $value = false)
    {
        return $characteristicID > 0
            ? new Merkmal($characteristicID, $value)
            : new stdClass();
    }
}
