<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use JTL\Helpers\Text;

/**
 * Class QuestionValidation
 * @package JTL\Backend\Wizard
 */
class QuestionValidation
{
    /**
     * @var Question
     */
    private $question;

    /**
     * @var string
     */
    private $validationError = '';

    /**
     * QuestionValidation constructor.
     * @param QuestionInterface $question
     * @param bool $defaultValidation
     */
    public function __construct(QuestionInterface $question, bool $defaultValidation = true)
    {
        $this->question = $question;

        if ($defaultValidation) {
            $this->defaultValidation();
        }
    }

    /**
     * @return bool
     */
    public function checkRequired(): bool
    {
        if ($this->question->isRequired() && empty($this->question->getValue())) {
            $this->setValidationError('Pflichtfeld');

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function checkEmail(): bool
    {
        if ($this->question->getType() === QuestionType::EMAIL
            && Text::filterEmailAddress($this->question->getValue()) === false
        ) {
            $this->setValidationError('Keine Email');

            return false;
        }

        return true;
    }

    /**
     *
     */
    private function defaultValidation(): void
    {
        if ($this->checkRequired()) {
            $this->checkEmail();
        }
    }

    /**
     * @return string
     */
    public function getValidationError(): string
    {
        return $this->validationError;
    }

    /**
     * @param $validationError
     */
    private function setValidationError($validationError): void
    {
        $this->validationError = $validationError;
    }
}
