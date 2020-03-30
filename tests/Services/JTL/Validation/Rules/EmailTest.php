<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\Email;
use Tests\BaseTestCase;

/**
 * Class EmailTest
 * @package Services\JTL\Validation\Rules
 */
class EmailTest extends BaseTestCase
{
    public function test()
    {
        $rule = new Email();
        $this->assertTrue($rule->validate('martin.schophaus@jtl-software.com')->isValid());
        $this->assertFalse($rule->validate('martin.schophaus@ jtl-software.com')->isValid());
    }
}
