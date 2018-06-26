<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class Equals
 * @package Services\JTL\Validation\Rules
 */
class EqualsLax implements RuleInterface
{
    protected $expected;

    /**
     * Equals constructor.
     * @param mixed $expected
     */
    public function __construct($expected)
    {
        $this->expected = $expected;
    }

    /**
     * @param mixed $value
     * @return RuleResult
     */
    public function validate($value)
    {
        return $this->expected == $value
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'values not equal', $value);
    }
}
