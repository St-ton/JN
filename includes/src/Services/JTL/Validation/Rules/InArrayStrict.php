<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class WhitelistStrict
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that $value is in a specified list of items
 */
class InArrayStrict implements RuleInterface
{
    protected $whitelist;

    /**
     * WhitelistStrict constructor.
     * @param mixed[] $whitelist
     */
    public function __construct(array $whitelist)
    {
        $this->whitelist = $whitelist;
    }

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        return in_array($value, $this->whitelist, true)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'value not in whitelist', $value);
    }
}
