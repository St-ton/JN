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
        if ($this->question->isRequired() && $this->valueIsEmpty()) {
            $this->setValidationError(__('validationErrorRequired'));

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
            && !empty($this->question->getValue())
            && Text::filterEmailAddress($this->question->getValue()) === false
        ) {
            $this->setValidationError(__('validationErrorIncorrectEmail'));

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function checkSSL(): bool
    {
        if ((empty($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] !== 'off')) && !$this->valueIsEmpty()) {
            $this->setValidationError(__('validationErrorSSL'));

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function valueIsEmpty(): bool
    {
        return empty($this->question->getValue())
                || (\is_array($this->question->getValue()) && \count(\array_filter($this->question->getValue())) === 0);
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
