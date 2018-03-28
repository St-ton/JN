<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


class NumericTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new Numeric();
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate('10')->isValid());
        $this->assertFalse($rule->validate('10 b')->isValid());
    }
}
