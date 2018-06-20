<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

use DB\DbInterface;
use DB\ReturnType;

/**
 * Class SurveyQuestion
 * @package JTL
 */
class SurveyQuestion
{
    const TYPE_MULTI = 'multiple_multi';

    const TYPE_MULTI_SINGLE = 'multiple_single';

    const TYPE_SELECT = 'select_single';

    const TYPE_SELECT_MULTI = 'select_multi';

    const TYPE_TEXT_SMALL = 'text_klein';

    const TYPE_TEXT_BIG = 'text_gross';

    const TYPE_MATRIX = 'matrix_single';

    const TYPE_MATRIX_MULTI = 'matrix_multi';

    const TYPE_TEXT_STATIC = 'text_statisch';

    const TYPE_TEXT_PAGE_CHANGE = 'text_statisch_seitenwechsel';

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
     * @var array
     */
    private static $mapping = [
        'kUmfrageFrage' => 'ID',
        'kUmfrage'      => 'SurveyID',
        'cTyp'          => 'Type',
        'cName'         => 'Name',
        'cBeschreibung' => 'Description',
        'nSort'         => 'Sort',
        'nFreifeld'     => 'FreeField',
        'nNotwendig'    => 'Required',
    ];

    /**
     * SurveyQuestion constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $value
     * @return string|null
     */
    private function getMapping($value)
    {
        return self::$mapping[$value] ?? null;
    }

    /**
     * @param \stdClass $data
     * @return $this
     */
    public function map(\stdClass $data): self
    {
        foreach (get_object_vars($data) as $var => $value) {
            if (($mapping = $this->getMapping($var)) !== null) {
                $method = 'set' . $mapping;
                $this->$method($value);
            }
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
            'SELECT * 
                FROM tumfragefrage
                WHERE kUmfrageFrage = :id',
            ['id' => $id],
            ReturnType::SINGLE_OBJECT
        );
        if ($question !== null) {
            $this->map($question);
        }

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
    public function isFreeField(): bool
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
        $res       = get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
