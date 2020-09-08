<?php declare(strict_types=1);

namespace JTL\Backend\Wizard\Steps;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\DB\DbInterface;
use JTL\Services\JTL\AlertServiceInterface;

/**
 * Class AbstractStep
 * @package JTL\Backend\Wizard\Stepst
 */
abstract class AbstractStep implements StepInterface
{
    /**
     * @var Collection
     */
    protected $questions;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var AlertServiceInterface
     */
    protected $alertService;

    /**
     * AbstractStep constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     */
    public function __construct(DbInterface $db, AlertServiceInterface $alertService)
    {
        $this->db           = $db;
        $this->alertService = $alertService;
        $this->questions    = new Collection();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function setQuestions(Collection $questions): void
    {
        $this->questions = $questions;
    }

    /**
     * @inheritDoc
     */
    public function addQuestion(QuestionInterface $question): void
    {
        $this->questions->push($question);
    }

    /**
     * @inheritDoc
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @inheritDoc
     */
    public function answerQuestionByID(int $questionID, $value): QuestionInterface
    {
        $question = $this->questions->first(function (QuestionInterface $question) use ($questionID) {
            return $question->getID() === $questionID;
        });
        $question->setValue($value);

        return $question;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredQuestions(): array
    {
//        $questions = $this->questions->filter(function (QuestionInterface $question) {
//            $test = $question->getDependency();
//            if ($test === null) {
//                return true;
//            }
//            foreach ($this->questions as $q) {
//                if ($q->getID() === $test)
//            }
//        })
        return \array_filter($this->questions->toArray(), function (QuestionInterface $question) {
            $test = $question->getDependency();
            if ($test === null) {
                return true;
            }
            foreach ($this->questions as $q) {
                if ($q->getID() === $test) {
                    return !empty($q->getValue());
                }
            }

            return false;
        });
    }
}
