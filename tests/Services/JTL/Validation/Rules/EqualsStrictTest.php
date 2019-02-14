<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\EqualsStrict;
use PHPUnit\Framework\TestCase;

/**
 * Class EqualsStrictTest
 * @package Services\JTL\Validation\Rules
 */
class EqualsStrictTest extends TestCase
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
