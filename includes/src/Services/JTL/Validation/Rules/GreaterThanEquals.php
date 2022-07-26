<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class GreaterThanEquals
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value is greater than a specified value
 *
 * No transform
 */
class GreaterThanEquals implements RuleInterface
{
    /**
     * GreaterThan constructor.
     * @param mixed $gt
     */
    public function __construct(protected $gt)
    {
    }

    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return $value >= $this->gt
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'value to small', null);
    }
}
