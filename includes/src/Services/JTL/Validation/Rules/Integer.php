<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class Integer
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that $value is an integer
 *
 * Transforms value to int
 */
class Integer implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        $result = \filter_var($value, \FILTER_VALIDATE_INT);

        return $result !== false
            ? new RuleResult(true, '', $result)
            : new RuleResult(false, 'invalid integer', $value);
    }
}
