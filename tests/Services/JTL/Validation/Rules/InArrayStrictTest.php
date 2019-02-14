<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\InArrayStrict;
use PHPUnit\Framework\TestCase;

/**
 * Class InArrayStrictTest
 * @package Services\JTL\Validation\Rules
 */
class InArrayStrictTest extends TestCase
{
    public function test()
    {
        $rule = new InArrayStrict([10, 12]);
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate('12')->isValid());
    }
}
