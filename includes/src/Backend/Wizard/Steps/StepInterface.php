<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard\Steps;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\QuestionInterface;

/**
 * Interface StepInterface
 * @package JTL\Backend\Wizard\Steps
 */
interface StepInterface
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     */
    public function setTitle(string $title): void;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @param Collection $questions
     */
    public function setQuestions(Collection $questions): void;

    /**
     * @param QuestionInterface $question
     */
    public function addQuestion(QuestionInterface $question): void;

    /**
     * @return Collection
     */
    public function getQuestions(): Collection;

    /**
     * @param int   $questionID
     * @param mixed $value
     * @return QuestionInterface
     */
    public function answerQuestionByID(int $questionID, $value): QuestionInterface;

    /**
     * @return QuestionInterface[]
     */
    public function getFilteredQuestions(): array;
}
