<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class GreaterThan
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that $value is greater than a specified value
 *
 * No transform
 */
class GreaterThanEquals implements RuleInterface
{
    protected $gt;

    /**
     * GreaterThan constructor.
     * @param mixed $gt
     */
    public function __construct($gt)
    {
        $this->gt = $gt;
    }

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        return $value >= $this->gt
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'value to small', null);
    }
}
