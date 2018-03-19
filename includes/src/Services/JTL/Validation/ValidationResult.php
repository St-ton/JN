<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

use function Functional\none;

/**
 * Class ValidationResult
 * @package Services\JTL\Validation
 */
class ValidationResult implements ValidationResultInterface
{
    protected $ruleResults = [];
    protected $value;
    protected $unfilteredValue;

    /**
     * ValidationResult constructor.
     * @param mixed $unfilteredValue
     */
    public function __construct($unfilteredValue)
    {
        $this->unfilteredValue = $unfilteredValue;
    }

    /**
     * @inheritdoc
     */
    public function addRuleResult(RuleResultInterface $ruleResult)
    {
        $this->ruleResults[] = $ruleResult;
    }

    /**
     * @inheritdoc
     */
    public function getRuleResults()
    {
        return $this->ruleResults;
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return none($this->ruleResults, function (RuleResultInterface $item) {
            return !$item->isValid();
        });
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getValue($default = null)
    {
        return $this->isValid() ? $this->value : $default;
    }

    /**
     * @inheritdoc
     */
    public function getValueInsecure()
    {
        return $this->unfilteredValue;
    }
}
