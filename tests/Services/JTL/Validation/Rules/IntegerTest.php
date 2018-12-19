<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use PHPUnit\Framework\TestCase;

/**
 * Class IntegerTest
 * @package Services\JTL\Validation\Rules
 */
class IntegerTest extends TestCase
{
    public function test()
    {
        $rule = new Integer();
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate('10')->isValid());
        $this->assertFalse($rule->validate(10.5)->isValid());
        $this->assertFalse($rule->validate('10.5')->isValid());
        $result = $rule->validate('10');
        $this->assertTrue(is_int($result->getTransformedValue()));
    }
}
