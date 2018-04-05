<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class EqualsStrict
 * @package Services\JTL\Validation\Rules
 *
 * Validates, that $value strictly equals a specified value.
 *
 * No transform
 */
class EqualsStrict implements RuleInterface
{
    protected $eq;

    /**
     * EqualsStrict constructor.
     * @param mixed $eq
     */
    public function __construct($eq)
    {
        $this->eq = $eq;
    }

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        return $value === $this->eq
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'does not equal expected value', $value);
    }
}
