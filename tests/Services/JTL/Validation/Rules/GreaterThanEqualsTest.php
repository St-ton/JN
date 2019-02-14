<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\GreaterThanEquals;
use PHPUnit\Framework\TestCase;

/**
 * Class GreaterThanEqualsTest
 * @package Services\JTL\Validation\Rules
 */
class GreaterThanEqualsTest extends TestCase
{
    public function test()
    {
        $rule = new GreaterThanEquals(10);
        $this->assertTrue($rule->validate(11)->isValid());
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(9)->isValid());
    }
}
