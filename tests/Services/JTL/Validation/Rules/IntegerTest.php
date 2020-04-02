<?php

namespace Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\Integer;
use Tests\BaseTestCase;

/**
 * Class IntegerTest
 * @package Services\JTL\Validation\Rules
 */
class IntegerTest extends BaseTestCase
{
    public function test()
    {
        $rule = new Integer();
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertTrue($rule->validate('10')->isValid());
        $this->assertFalse($rule->validate(10.5)->isValid());
        $this->assertFalse($rule->validate('10.5')->isValid());
        $result = $rule->validate('10');
        $this->assertTrue(is_int($result->getTransformedValue()));
    }
}
