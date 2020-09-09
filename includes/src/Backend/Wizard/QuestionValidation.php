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
     * @var int
     */
    private $validationError;

    /**
     * QuestionValidation constructor.
     * @param QuestionInterface $question
     * @param bool              $defaultValidation
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
            $this->setValidationError(QuestionValidationCode::ERROR_REQUIRED);

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
            $this->setValidationError(QuestionValidationCode::INVALID_EMAIL);

            return false;
        }

        return true;
    }

    /**
     * @param bool $pluginMsg
     * @return bool
     */
    public function checkSSL(bool $pluginMsg = false): bool
    {
        if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') && !$this->valueIsEmpty()) {
            $pluginMsg
                ? $this->setValidationError(QuestionValidationCode::ERROR_SSL_PLUGIN)
                : $this->setValidationError(QuestionValidationCode::ERROR_SSL);

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
        return $this->mapCode($this->validationError ?? QuestionValidationCode::OK);
    }

    /**
     * @param int $validationError
     */
    private function setValidationError(int $validationError): void
    {
        $this->validationError = $validationError;
    }

    /**
     * @param int $code
     * @return string
     */
    private function mapCode(int $code):string
    {
        switch ($code) {
            case QuestionValidationCode::OK:
                $error = '';
                break;
            case QuestionValidationCode::ERROR_REQUIRED:
                $error = __('validationErrorRequired');
                break;
            case QuestionValidationCode::INVALID_EMAIL:
                $error = __('validationErrorIncorrectEmail');
                break;
            case QuestionValidationCode::ERROR_SSL_PLUGIN:
                $error = __('validationErrorSSLPlugin');
                break;
            case QuestionValidationCode::ERROR_SSL:
                $error = __('validationErrorSSL');
                break;
            default:
                $error = '';
                break;
        }

        return $error;
    }
}
