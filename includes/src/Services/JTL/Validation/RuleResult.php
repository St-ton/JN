<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

/**
 * Class ValidationResult
 * @package Services\JTL\Validation
 */
class RuleResult implements RuleResultInterface
{
    protected $isValid;
    protected $messageId;
    protected $transformedValue;

    /**
     * ValidationResult constructor.
     * @param bool   $isValid
     * @param string $messageId
     * @param mixed  $transformedValue
     */
    public function __construct($isValid, $messageId, $transformedValue = null)
    {
        $this->isValid          = $isValid;
        $this->messageId        = $messageId;
        $this->transformedValue = $transformedValue;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @return string
     */
    public function getMessageId()
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
