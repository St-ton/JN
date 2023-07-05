<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class InArrayStrict
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value is in a specified list of items
 */
class InArrayStrict implements RuleInterface
{
    /**
     * WhitelistStrict constructor.
     * @param array $whitelist
     */
    public function __construct(protected array $whitelist)
    {
    }

    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return \in_array($value, $this->whitelist, true)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'value not in whitelist', $value);
    }
}
