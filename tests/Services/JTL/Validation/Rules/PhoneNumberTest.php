<?php

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\PhoneNumber;
use Tests\BaseTestCase;

/**
 * Class PhoneNumberTest
 * @package Services\JTL\Validation\Rules
 */
class PhoneNumberTest extends BaseTestCase
{
    public function test()
    {
        $allValid = [
            '+49 (0) 2131 12345',
            '(044) 664 123 45 67',
            '664 123 4567',
            '(700) 555-4141',
            '10-10-220'
        ];

        $allInvalid = [
            'This is an invalid phone number!',
            ''
        ];

        $rule = new PhoneNumber();

        foreach ($allValid as $valid) {
            $this->assertTrue($rule->validate($valid)->isValid());
        }

        foreach ($allInvalid as $invalid) {
            $this->assertFalse($rule->validate($invalid)->isValid());
        }
    }
}
