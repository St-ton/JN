<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

/**
 * Class AnswerOption
 * @package Survey
 */
class AnswerOption
{
    use \MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $questionID = 0;

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var array
     */
    private static $mapping = [
        'kUmfrageFrageAntwort' => 'ID',
        'kUmfrageFrage'        => 'QuestionID',
        'nSort'                => 'Sort',
        'cName'                => 'Name'
    ];

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
    public function getQuestionID(): int
    {
        return $this->questionID;
    }

    /**
     * @param int $questionID
     */
    public function setQuestionID(int $questionID)
    {
        $this->questionID = $questionID;
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
}
