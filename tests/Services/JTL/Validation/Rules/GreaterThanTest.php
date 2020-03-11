<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\GreaterThan;
use Tests\BaseTestCase;

/**
 * Class GreaterThanTest
 * @package Services\JTL\Validation\Rules
 */
class GreaterThanTest extends BaseTestCase
{
    public function test()
    {
        $rule = new GreaterThan(10);
        $this->assertTrue($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(9)->isValid());
    }
}
