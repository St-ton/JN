<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\EqualsStrict;
use Tests\BaseTestCase;

/**
 * Class EqualsStrictTest
 * @package Services\JTL\Validation\Rules
 */
class EqualsStrictTest extends BaseTestCase
{
    public function test()
    {
        $rule = new EqualsStrict(10);
        $this->assertFalse($rule->validate('10')->isValid());
        $this->assertTrue($rule->validate(10)->isValid());

        $rule = new EqualsStrict('10');
        $this->assertFalse($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate('10')->isValid());
    }
}
