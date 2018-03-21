<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


class EqualsStrictTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new EqualsStrict(10);
        $this->assertFalse($rule->validate('10')->isValid());
        $this->assertTrue($rule->validate(10)->isValid());

        $rule = new EqualsStrict('10');
        $this->assertFalse($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate('10')->isValid());
    }
}
