<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class Type
 * @package Services\JTL\Validation\Rules
 *
 * Validates that the value is of the specified type
 *
 * No transform
 */
class Type implements RuleInterface
{
    protected $expected;

    /**
     * Type constructor.
     * @param mixed $expected
     */
    public function __construct(string $expected)
    {
        $this->expected = $expected;
    }

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        return $this->expected === \gettype($value)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'invalid type', $value);
    }
}
