<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

use DB\DbInterface;
use DB\ReturnType;
use function Functional\first;
use function Functional\map;
use Tightenco\Collect\Support\Collection;

/**
 * Class SurveyQuestion
 * @package JTL
 */
class SurveyQuestion
{
    use \MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $surveyID = 0;

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var bool
     */
    private $freeField = false;

    /**
     * @var bool
     */
    private $required = true;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var Collection
     */
    private $matrixOptions;

    /**
     * @var Collection
     */
    private $answerOptions;

    /**
     * @var array
     */
    private $givenAnswer = [];

    /**
     * @var array
     */
    private static $mapping = [
        'kUmfrageFrage'            => 'ID',
        'kUmfrage'                 => 'SurveyID',
        'cTyp'                     => 'Type',
        'cName'                    => 'Name',
        'cBeschreibung'            => 'Description',
        'nSort'                    => 'Sort',
        'nFreifeld'                => 'FreeField',
        'nNotwendig'               => 'Required',
        'oUmfrageMatrixOption_arr' => 'MatrixOptions',
        'oUmfrageFrageAntwort_arr' => 'AnswerOptions',
    ];

    /**
     * SurveyQuestion constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db            = $db;
        $this->matrixOptions = new Collection();
        $this->answerOptions = new Collection();
    }

    /**
     * @param \stdClass $data
     * @return $this
     */
    public function map(\stdClass $data): self
    {
        foreach (\get_object_vars($data) as $var => $value) {
            if (($mapping = self::getMapping($var)) !== null) {
                $method = 'set' . $mapping;
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function mapGroup(array $data): self
    {
        $baseData = first($data);
        $this->map($baseData);
        if (!empty($baseData->answerID)) {
            foreach ($data as $question) {
                $answer = new AnswerOption();
                $answer->setID((int)$question->answerID);
                $answer->setQuestionID((int)$question->kUmfrageFrage);
                $answer->setSort((int)$question->answerSort);
                $answer->setName($question->answerName);
                $this->answerOptions->push($answer);
            }
            $this->answerOptions = $this->answerOptions->unique()->sortBy(function (AnswerOption $e) {
                return $e->getSort();
            });
        }
        if (!empty($baseData->matrixID)) {
            foreach ($data as $question) {
                $matrix = new MatrixOption();
                $matrix->setID((int)$question->matrixID);
                $matrix->setQuestionID((int)$question->kUmfrageFrage);
                $matrix->setSort((int)$question->matrixSort);
                $matrix->setName($question->matrixName);
                $this->matrixOptions->push($matrix);
            }
            $this->matrixOptions = $this->matrixOptions->unique()->sortBy(function (MatrixOption $e) {
                return $e->getSort();
            });
        }

        return $this;
    }

    /**
     * @param int $id
     * @return SurveyQuestion
     */
    public function load(int $id): self
    {
        $question = $this->db->queryPrepared(
            'SELECT tumfragefrage.*, 
            tumfragefrageantwort.kUmfrageFrageAntwort AS answerID, 
            tumfragefrageantwort.cName AS answerName, 
            tumfragefrageantwort.nSort AS answerSort,
            tumfragematrixoption.kUmfrageMatrixOption AS matrixID, 
            tumfragematrixoption.cName AS matrixName, 
            tumfragematrixoption.nSort AS matrixSort
                FROM tumfragefrage
                LEFT JOIN tumfragefrageantwort
                    ON tumfragefrage.kUmfrageFrage = tumfragefrageantwort.kUmfrageFrage
                LEFT JOIN tumfragematrixoption
                    ON tumfragefrage.kUmfrageFrage = tumfragematrixoption.kUmfrageFrage
                WHERE tumfragefrage.kUmfrageFrage = :id',
            ['id' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $question = map($question, function (\stdClass $e) {
            return $e->kUmfragefrage;
        });
        $this->mapGroup($question);

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getSurveyID(): int
    {
        return $this->surveyID;
    }

    /**
     * @param int $surveyID
     */
    public function setSurveyID(int $surveyID)
    {
        $this->surveyID = $surveyID;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return bool
     */
    public function getFreeField(): bool
    {
        return $this->hasFreeField();
    }

    /**
     * @return bool
     */
    public function hasFreeField(): bool
    {
        return $this->freeField;
    }

    /**
     * @param bool $freeField
     */
    public function setFreeField(bool $freeField)
    {
        $this->freeField = $freeField;
    }

    /**
     * @return bool
     */
    public function getRequired(): bool
    {
        return $this->isRequired();
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required)
    {
        $this->required = $required;
    }

    /**
     * @return Collection
     */
    public function getMatrixOptions(): Collection
    {
        return $this->matrixOptions;
    }

    /**
     * @param Collection $matrixOptions
     */
    public function setMatrixOptions(Collection $matrixOptions)
    {
        $this->matrixOptions = $matrixOptions;
    }

    /**
     * @return Collection
     */
    public function getAnswerOptions(): Collection
    {
        return $this->answerOptions;
    }

    /**
     * @param Collection $answerOptions
     */
    public function setAnswerOptions(Collection $answerOptions)
    {
        $this->answerOptions = $answerOptions;
    }

    /**
     * @param int $idx
     * @return array
     */
    public function getGivenAnswer(int $idx = null): array
    {
        return $idx !== 0
            ? $this->givenAnswer[$idx] ?? null
            : $this->givenAnswer;
    }

    /**
     * @param array $givenAnswer
     */
    public function setGivenAnswer(array $givenAnswer)
    {
        $this->givenAnswer = $givenAnswer;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $res       = \get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
