<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\LessThan;
use Tests\BaseTestCase;

/**
 * Class LessThanTest
 * @package Services\JTL\Validation\Rules
 */
class LessThanTest extends BaseTestCase
{
    public function test()
    {
        $rule = new LessThan(10);
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate(9)->isValid());
    }
}
