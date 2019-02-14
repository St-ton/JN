<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\Numeric;
use PHPUnit\Framework\TestCase;

/**
 * Class NumericTest
 * @package Services\JTL\Validation\Rules
 */
class NumericTest extends TestCase
{
    public function test()
    {
        $rule = new Numeric();
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate('10')->isValid());
        $this->assertFalse($rule->validate('10 b')->isValid());
    }
}
