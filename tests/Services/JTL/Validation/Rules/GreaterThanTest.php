<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

/**
 * Class GreaterThanTest
 * @package Services\JTL\Validation\Rules
 */
class GreaterThanTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new GreaterThan(10);
        $this->assertTrue($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(9)->isValid());
    }
}
