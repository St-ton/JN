<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use PHPUnit\Framework\TestCase;

/**
 * Class LessThanTest
 * @package Services\JTL\Validation\Rules
 */
class LessThanTest extends TestCase
{
    public function test()
    {
        $rule = new LessThan(10);
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate(9)->isValid());
    }
}
