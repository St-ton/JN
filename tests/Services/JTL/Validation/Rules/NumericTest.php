<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\Numeric;
use Tests\BaseTestCase;

/**
 * Class NumericTest
 * @package Services\JTL\Validation\Rules
 */
class NumericTest extends BaseTestCase
{
    public function test()
    {
        $rule = new Numeric();
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate('10')->isValid());
        $this->assertFalse($rule->validate('10 b')->isValid());
    }
}
