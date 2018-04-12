<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class Between
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that the $value is between an $lower and an $upper bound.
 *
 * No transform.
 */
class Between implements RuleInterface
{
    protected $lower;
    protected $upper;

    /**
     * Between constructor.
     * @param mixed $lower
     * @param mixed $upper
     */
    public function __construct($lower, $upper)
    {
        $this->lower = $lower;
        $this->upper = $upper;
    }

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        if ($value < $this->lower) {
            return new RuleResult(false, 'value too low', $value);
        }

        if ($value > $this->upper) {
            return new RuleResult(false, 'value too high', $value);
        }

        return new RuleResult(true, '', $value);
    }
}
