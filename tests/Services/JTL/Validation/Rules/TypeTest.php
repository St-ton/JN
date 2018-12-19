<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use PHPUnit\Framework\TestCase;

/**
 * Class TypeTest
 * @package Services\JTL\Validation\Rules
 */
class TypeTest extends TestCase
{
    public function test()
    {
        $rule = new Type('integer');
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate('10')->isValid());
    }
}
