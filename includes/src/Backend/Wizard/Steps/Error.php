<?php declare(strict_types=1);

namespace JTL\Backend\Wizard\Steps;

/**
 * Class Error
 * @package JTL\Backend\Wizard\Steps
 */
class Error
{
    /**
     * @var int
     */
    protected $questionID;

    /**
     * @var int
     */
    protected $stepID;

    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var bool
     */
    protected $critical;

    /**
     * Error constructor.
     * @param int $step
     * @param int $questionID
     * @param int $code
     */
    public function __construct(int $step, int $questionID, int $code)
    {
        $this->setStepID($step);
        $this->setQuestionID($questionID);
        $this->setCode($code);
        $this->setMessageByCode();
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
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->critical;
    }

    /**
     * @param bool $critical
     */
    public function setCritical(bool $critical): void
    {
        $this->critical = $critical;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     *
     */
    private function setMessageByCode(): void
    {
        switch ($this->getCode()) {
            case ErrorCode::OK:
                $error = '';
                break;
            case ErrorCode::ERROR_REQUIRED:
                $error = __('validationErrorRequired');
                $this->setCritical(true);
                break;
            case ErrorCode::INVALID_EMAIL:
                $error = __('validationErrorIncorrectEmail');
                $this->setCritical(true);
                break;
            case ErrorCode::ERROR_SSL_PLUGIN:
                $error = __('validationErrorSSLPlugin');
                $this->setCritical(true);
                break;
            case ErrorCode::ERROR_SSL:
                $error = __('validationErrorSSL');
                $this->setCritical(true);
                break;
            default:
                $error = '';
                break;
        }

        $this->setMessage($error);
    }

    /**
     * @return int
     */
    public function getStepID(): int
    {
        return $this->stepID;
    }

    /**
     * @param int $stepID
     */
    public function setStepID(int $stepID): void
    {
        $this->stepID = $stepID;
    }
}
