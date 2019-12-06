<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\Steps\StepInterface;
use JTL\Shop;
use stdClass;

/**
 * Class Controller
 * @package JTL\Backend\Wizard
 */
final class Controller
{
    /**
     * @var Collection
     */
    private $steps;

    /**
     * @var int
     */
    private $currentStepID;

    /**
     * @var stdClass
     */
    private $sessionData;

    /**
     * Controller constructor.
     * @param DefaultFactory $factory
     */
    public function __construct(DefaultFactory $factory)
    {
        $this->steps         = $factory->getSteps();
        $this->sessionData   = $this->getSessionData();
        $this->currentStepID = $this->sessionData->step;
    }

    /**
     * @return stdClass
     */
    private function getSessionData(): stdClass
    {
        if (!isset($_SESSION['wizard']->step)) {
            $data               = new stdClass();
            $data->step         = 1;
            $data->answers      = [];
            $_SESSION['wizard'] = $data;
        }

        return $_SESSION['wizard'];
    }

    /**
     * @param array $post
     */
    public function answerQuestions(array $post): void
    {
        $nextStep = (int)($post['newstep'] ?? 0);
        if ($nextStep === 0) {
            return;
        }
        $step = $this->getActiveStep();
        foreach ($step->getQuestions() as $question) {
            /** @var QuestionInterface $question */
            $question->answerFromPost($post);
            $this->saveAnswer($question);
        }
        $this->setCurrentStepID($nextStep);
        $_SESSION['wizard'] = $this->sessionData;
        if ($nextStep === -1) {
            $this->finish();
        }
    }

    /**
     *
     */
    private function finish(): void
    {
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                $question->loadAnswer($this->sessionData->answers);
                $question->save();
            }
        }
        Shop::Container()->getCache()->flushAll();
        unset($_SESSION['wizard']);
    }

    /**
     * @param QuestionInterface $question
     */
    private function saveAnswer(QuestionInterface $question): void
    {
        $this->sessionData->answers[$question->getID()] = $question->getValue();
    }

    /**
     * @return Collection
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    /**
     * @param Collection $steps
     */
    public function setSteps(Collection $steps): void
    {
        $this->steps = $steps;
    }

    /**
     * @return int
     */
    public function getCurrentStepID(): int
    {
        return $this->currentStepID;
    }

    /**
     * @param int $currentStepID
     */
    public function setCurrentStepID(int $currentStepID): void
    {
        $this->currentStepID      = $currentStepID;
        $_SESSION['wizard']->step = $currentStepID;
    }

    /**
     * @return StepInterface|null
     */
    public function getActiveStep(): ?StepInterface
    {
        $step = $this->steps->first(function (StepInterface $step) {
            return $step->getID() === $this->currentStepID;
        });
        if ($step === null) {
            return null;
        }
        foreach ($step->getQuestions() as $question) {
            /** @var QuestionInterface $question */
            $question->loadAnswer($this->sessionData->answers);
        }

        return $step;
    }

    /**
     * @return StepInterface|null
     */
    public function getPreviousStep(): ?StepInterface
    {
        return $this->steps->first(function (StepInterface $step) {
            return $step->getID() === $this->currentStepID - 1;
        });
    }

    /**
     * @return StepInterface|null
     */
    public function getNextStep(): ?StepInterface
    {
        return $this->steps->first(function (StepInterface $step) {
            return $step->getID() === $this->currentStepID + 1;
        });
    }
}
