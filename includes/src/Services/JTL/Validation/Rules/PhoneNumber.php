<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class PhoneNumber
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that $value is an valid phone number
 *
 * No transform
 */
class PhoneNumber implements RuleInterface
{
    const REGEX = '/^[0-9\-\(\)\/\+\s]{1,}$/'; // taken from tools.Global.php function checkeTel

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        return preg_match(self::REGEX, $value)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'invalid phone number', $value);
    }
}
