<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

/**
 * Class InArrayStrictTest
 * @package Services\JTL\Validation\Rules
 */
class InArrayStrictTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new InArrayStrict([10, 12]);
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate('12')->isValid());
    }
}
