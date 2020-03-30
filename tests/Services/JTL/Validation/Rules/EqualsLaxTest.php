<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\EqualsLax;
use Tests\BaseTestCase;

/**
 * Class EqualsLaxTest
 * @package Services\JTL\Validation\Rules
 */
class EqualsLaxTest extends BaseTestCase
{
    public function test()
    {
        $rule = new EqualsLax(10);
        $this->assertTrue($rule->validate('10')->isValid());
        $this->assertFalse($rule->validate('11')->isValid());
        $this->assertFalse($rule->validate(11)->isValid());

        $rule = new EqualsLax('10');
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate('11')->isValid());
    }
}
