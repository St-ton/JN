<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class EqualsStrict
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value strictly equals a specified value.
 *
 * No transform
 */
class EqualsStrict implements RuleInterface
{
    /**
     * EqualsStrict constructor.
     * @param mixed $eq
     */
    public function __construct(protected $eq)
    {
    }

    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return $value === $this->eq
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'does not equal expected value', $value);
    }
}
