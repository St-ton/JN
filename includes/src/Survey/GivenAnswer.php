<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

/**
 * Class GivenAnswer
 * @package Survey
 */
class GivenAnswer
{
    /**
     * @var int
     */
    private $questionID = 0;

    /**
     * @var mixed
     */
    private $answer;

    /**
     * @var string
     */
    private $questionType;

    /**
     * @param int      $answerID
     * @param int|null $matrixID
     * @return bool
     */
    public function isActive(int $answerID, int $matrixID = null): bool
    {
        $type = $this->getQuestionType();
        if ($type === 'matrix_single') { // @todo
            if (!is_array($this->answer)) {
                return false;
            }
            foreach ($this->answer as $item) {
                list($answerOption, $matrixOption) = explode('_', $item);
                if ((int)$answerOption === $answerID && (int)$matrixOption === $matrixID) {
                    return true;
                }
            }

            return false;
        }
        if ($type === 'multiple_single' || $type === 'multiple_multi' || $type === 'select_single') {
            if (!is_array($this->answer)) {
                return false;
            }
            foreach ($this->answer as $item) {
                if ((int)$item === $answerID) {
                    return true;
                }
            }
        }

        return false;
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
     * @param int $idx
     * @return mixed
     */
    public function getAnswer(int $idx = null)
    {
        return $idx !== null
            ? $this->answer[$idx] ?? null
            : $this->answer;
    }

    /**
     * @param mixed $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * @return mixed
     */
    public function getQuestionType()
    {
        return $this->questionType;
    }

    /**
     * @param mixed $questionType
     */
    public function setQuestionType($questionType)
    {
        $this->questionType = $questionType;
    }
}
