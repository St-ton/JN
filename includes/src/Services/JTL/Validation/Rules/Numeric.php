<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class Numeric
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that $value is numeric
 *
 * No transform
 */
class Numeric implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return \is_numeric($value)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'not numeric', $value);
    }
}
