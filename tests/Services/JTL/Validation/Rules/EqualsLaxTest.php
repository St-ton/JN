<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use PHPUnit\Framework\TestCase;

/**
 * Class EqualsLaxTest
 * @package Services\JTL\Validation\Rules
 */
class EqualsLaxTest extends TestCase
{
    public function test()
    {
        $rule = new EqualsLax(10);
        $this->assertTrue($rule->validate('10')->isValid());
        $this->assertFalse($rule->validate('11')->isValid());
        $this->assertFalse($rule->validate(11)->isValid());

        $rule = new EqualsLax('10');
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate('11')->isValid());
    }
}
