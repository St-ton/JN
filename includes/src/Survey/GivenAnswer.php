<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Survey;

/**
 * Class GivenAnswer
 * @package JTL\Survey
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
        if ($type === QuestionType::MATRIX_SINGLE) {
            if (!\is_array($this->answer)) {
                return false;
            }
            foreach ($this->answer as $item) {
                [$answerOption, $matrixOption] = \explode('_', $item);
                if ((int)$answerOption === $answerID && (int)$matrixOption === $matrixID) {
                    return true;
                }
            }

            return false;
        }
        if ($type === QuestionType::MULTI_SINGLE
            || $type === QuestionType::MULTI
            || $type === QuestionType::SELECT_SINGLE
        ) {
            if (!\is_array($this->answer)) {
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
    public function setQuestionID(int $questionID): void
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
    public function setAnswer($answer): void
    {
        $this->answer = $answer;
    }

    /**
     * @return string|null
     */
    public function getQuestionType(): ?string
    {
        return $this->questionType;
    }

    /**
     * @param mixed $questionType
     */
    public function setQuestionType($questionType): void
    {
        $this->questionType = $questionType;
    }
}
