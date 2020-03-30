<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\LessThanEquals;
use Tests\BaseTestCase;

/**
 * Class LessThanEqualsTest
 * @package Services\JTL\Validation\Rules
 */
class LessThanEqualsTest extends BaseTestCase
{
    public function test()
    {
        $rule = new LessThanEquals(10);
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate(9)->isValid());
    }
}
