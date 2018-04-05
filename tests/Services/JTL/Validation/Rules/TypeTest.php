<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


class TypeTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new Type('integer');
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate('10')->isValid());
    }
}
