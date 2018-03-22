<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


class LessThanEqualsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new LessThanEquals(10);
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate(9)->isValid());
    }
}
