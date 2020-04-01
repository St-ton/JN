<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\InArrayStrict;
use Tests\BaseTestCase;

/**
 * Class InArrayStrictTest
 * @package Services\JTL\Validation\Rules
 */
class InArrayStrictTest extends BaseTestCase
{
    public function test()
    {
        $rule = new InArrayStrict([10, 12]);
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate('12')->isValid());
    }
}
