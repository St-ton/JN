<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\DateTime;
use Tests\BaseTestCase;

/**
 * Class DateTimeTest
 * @package Services\JTL\Validation\Rules
 */
class DateTimeTest extends BaseTestCase
{
    public function test()
    {
        $rule = new DateTime('Y-m-d');
        $this->assertTrue($rule->validate('2019-10-10')->isValid());
        $this->assertFalse($rule->validate('1b9-10-10')->isValid());
        $this->assertFalse($rule->validate('2019-10-10 some malicious code')->isValid());
    }
}
