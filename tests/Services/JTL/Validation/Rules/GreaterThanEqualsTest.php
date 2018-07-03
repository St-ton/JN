<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

/**
 * Class GreaterThanEqualsTest
 * @package Services\JTL\Validation\Rules
 */
class GreaterThanEqualsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new GreaterThanEquals(10);
        $this->assertTrue($rule->validate(11)->isValid());
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(9)->isValid());
    }
}
