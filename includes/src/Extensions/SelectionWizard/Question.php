<?php declare(strict_types=1);

namespace JTL\Extensions\SelectionWizard;

use JTL\Catalog\Product\Merkmal;
use JTL\DB\DbInterface;
use JTL\Shop;
use stdClass;

/**
 * Class Question
 * @package JTL\Extensions\SelectionWizard
 */
class Question
{
    /**
     * @var int
     */
    public int $kAuswahlAssistentFrage = 0;

    /**
     * @var int
     */
    public int $kAuswahlAssistentGruppe = 0;

    /**
     * @var int
     */
    public int $kMerkmal = 0;

    /**
     * @var string
     */
    public string $cFrage = '';

    /**
     * @var string
     */
    public string $cName = '';

    /**
     * @var string
     */
    public string $cBildpfad = '';

    /**
     * @var int
     */
    public int $nSort = 0;

    /**
     * @var int
     */
    public int $nAktiv = 0;

    /**
     * @var array
     */
    public array $oWert_arr = [];

    /**
     * @var array - mapping from kMerkmalWert to tmerkmalwert object
     */
    public array $oWert_assoc = [];

    /**
     * @var int - how many products found that have a value of this attribute
     */
    public int $nTotalResultCount = 0;

    /**
     * @var object|null - used by old AWA
     * @deprecated since 5.0.0
     */
    public ?object $oMerkmal = null;

    /**
     * @var DbInterface|null
     */
    private ?DbInterface $db;

    /**
     * Location constructor.
     * @param int  $id
     * @param bool $activeOnly
     */
    public function __construct(int $id = 0, bool $activeOnly = true)
    {
        $this->db = Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id, $activeOnly);
        }
    }

    /**
     * @param int  $id
     * @param bool $activeOnly
     */
    private function loadFromDB(int $id, bool $activeOnly = true): void
    {
        $data = $this->db->getSingleObject(
            'SELECT af.*, m.cBildpfad, COALESCE(ms.cName, m.cName) AS cName, m.cBildpfad
                FROM tauswahlassistentfrage AS af
                    JOIN tauswahlassistentgruppe as ag
                        ON ag.kAuswahlAssistentGruppe = af.kAuswahlAssistentGruppe 
                    JOIN tmerkmal AS m
                        ON m.kMerkmal = af.kMerkmal 
                    LEFT JOIN tmerkmalsprache AS ms
                        ON ms.kMerkmal = m.kMerkmal 
                            AND ms.kSprache = ag.kSprache
                WHERE af.kAuswahlAssistentFrage = :qid' . ($activeOnly ? ' AND af.nAktiv = 1' : ''),
            ['qid' => $id]
        );
        if ($data !== null) {
            $this->kAuswahlAssistentFrage  = (int)$data->kAuswahlAssistentFrage;
            $this->kAuswahlAssistentGruppe = (int)$data->kAuswahlAssistentGruppe;
            $this->kMerkmal                = (int)$data->kMerkmal;
            $this->nSort                   = (int)$data->nSort;
            $this->nAktiv                  = (int)$data->nAktiv;
            $this->cFrage                  = $data->cFrage;
            $this->cName                   = $data->cName;
            $this->cBildpfad               = $data->cBildpfad;
        }
    }

    /**
     * @param int  $groupID
     * @param bool $activeOnly
     * @return array
     */
    public function getQuestions(int $groupID, bool $activeOnly = true): array
    {
        $activeSQL = $activeOnly ? ' AND nAktiv = 1' : '';

        return $this->db->getCollection(
            'SELECT kAuswahlAssistentFrage AS id
                FROM tauswahlassistentfrage
                WHERE kAuswahlAssistentGruppe = :gid' . $activeSQL . '
                ORDER BY nSort',
            ['gid' => $groupID]
        )->map(static function (stdClass $e) use ($activeOnly): self {
            return new self((int)$e->id, $activeOnly);
        })->all();
    }

    /**
     * @param bool $primary
     * @return array|bool
     */
    public function saveQuestion(bool $primary = false)
    {
        $checks = $this->checkQuestion();
        if (\count($checks) !== 0) {
            return $checks;
        }
        $ins                          = new stdClass();
        $ins->kAuswahlAssistentFrage  = $this->kAuswahlAssistentFrage;
        $ins->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
        $ins->kMerkmal                = $this->kMerkmal;
        $ins->cFrage                  = $this->cFrage;
        $ins->nSort                   = $this->nSort;
        $ins->nAktiv                  = $this->nAktiv;
        $id                           = $this->db->insert('tauswahlassistentfrage', $ins);

        if ($id > 0) {
            return $primary ? $id : true;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function updateQuestion()
    {
        $checks = $this->checkQuestion(true);
        if (\count($checks) !== 0) {
            return $checks;
        }
        $upd                          = new stdClass();
        $upd->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
        $upd->kMerkmal                = $this->kMerkmal;
        $upd->cFrage                  = $this->cFrage;
        $upd->nSort                   = $this->nSort;
        $upd->nAktiv                  = $this->nAktiv;

        $this->db->update(
            'tauswahlassistentfrage',
            'kAuswahlAssistentFrage',
            $this->kAuswahlAssistentFrage,
            $upd
        );

        return true;
    }

    /**
     * @param array $questionIDs
     * @return bool
     */
    public function deleteQuestion(array $questionIDs): bool
    {
        foreach (\array_map('\intval', $questionIDs) as $questionID) {
            $this->db->delete(
                'tauswahlassistentfrage',
                'kAuswahlAssistentFrage',
                $questionID
            );
        }

        return true;
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
        if ($this->kAuswahlAssistentGruppe < 1) {
            $checks['kAuswahlAssistentGruppe'] = 1;
        }
        if ($this->kMerkmal < 1) {
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
        $question = $this->db->select(
            'tauswahlassistentfrage',
            'kMerkmal',
            $characteristicID,
            'kAuswahlAssistentGruppe',
            $groupID
        );

        return isset($question->kAuswahlAssistentFrage) && $question->kAuswahlAssistentFrage > 0;
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
