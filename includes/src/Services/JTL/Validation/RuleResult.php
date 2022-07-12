<?php

namespace JTL\Services\JTL\Validation;

/**
 * Class RuleResult
 * @package JTL\Services\JTL\Validation
 */
class RuleResult implements RuleResultInterface
{
    /**
     * ValidationResult constructor.
     * @param bool   $isValid
     * @param string $messageId
     * @param mixed  $transformedValue
     */
    public function __construct(protected $isValid, protected $messageId, protected $transformedValue = null)
    {
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @inheritdoc
     */
    public function getTransformedValue()
    {
        return $this->transformedValue;
    }
}
