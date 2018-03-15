<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class LessThan
 * @package Services\JTL\Validation\Rules
 */
class LessThan implements RuleInterface
{
    protected $value;

    /**
     * LessThan constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        return $value < $this->value
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'value too high', $value);
    }
}
