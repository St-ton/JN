<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleResult;
use Services\JTL\Validation\RuleInterface;

/**
 * Class Email
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that $value is string containing a valid email
 *
 * No transform
 */
class Email implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($valid) {
            return new RuleResult(true, '', $value);
        } else {
            return new RuleResult(false, 'invalid email', $value);
        }
    }
}
