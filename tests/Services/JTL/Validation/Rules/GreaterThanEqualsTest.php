<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\GreaterThanEquals;
use Tests\BaseTestCase;

/**
 * Class GreaterThanEqualsTest
 * @package Services\JTL\Validation\Rules
 */
class GreaterThanEqualsTest extends BaseTestCase
{
    public function test()
    {
        $rule = new GreaterThanEquals(10);
        $this->assertTrue($rule->validate(11)->isValid());
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(9)->isValid());
    }
}
