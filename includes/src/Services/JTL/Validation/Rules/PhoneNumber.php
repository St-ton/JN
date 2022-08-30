<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class PhoneNumber
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value is an valid phone number
 *
 * No transform
 */
class PhoneNumber implements RuleInterface
{
    public const REGEX = '/^[\d\-\(\)\/\+\s]+$/';

    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return \preg_match(self::REGEX, $value)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'invalid phone number', $value);
    }
}
