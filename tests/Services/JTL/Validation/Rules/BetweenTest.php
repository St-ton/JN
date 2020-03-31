<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\Between;
use Tests\BaseTestCase;

/**
 * Class BetweenTest
 * @package Services\JTL\Validation\Rules
 */
class BetweenTest extends BaseTestCase
{
    public function test()
    {
        // works this numbers
        $rule = new Between(10, 20);
        $this->assertTrue($rule->validate(15)->isValid());
        $this->assertTrue($rule->validate(10)->isValid()); // includes lower bound
        $this->assertTrue($rule->validate(20)->isValid()); // includes upper bound
        $this->assertFalse($rule->validate(30)->isValid());

        // works with strings (alphanumerical)
        $rule = new Between('ab', 'cc');
        $this->assertTrue($rule->validate('bb')->isValid());
        $this->assertTrue($rule->validate('b')->isValid());
        $this->assertTrue($rule->validate('ab')->isValid());
        $this->assertFalse($rule->validate('aa')->isValid());

        // should work with everything, that is comparable ;-)
    }
}
