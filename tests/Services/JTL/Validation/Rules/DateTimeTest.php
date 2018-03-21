<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rule = new DateTime('Y-m-d');
        $this->assertTrue($rule->validate('2019-10-10')->isValid());
        $this->assertFalse($rule->validate('1b9-10-10')->isValid());
        $this->assertFalse($rule->validate('2019-10-10 some malicious code')->isValid());
    }
}
