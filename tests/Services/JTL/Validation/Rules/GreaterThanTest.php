<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\GreaterThan;
use PHPUnit\Framework\TestCase;

/**
 * Class GreaterThanTest
 * @package Services\JTL\Validation\Rules
 */
class GreaterThanTest extends TestCase
{
    public function test()
    {
        $rule = new GreaterThan(10);
        $this->assertTrue($rule->validate(11)->isValid());
        $this->assertFalse($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate(9)->isValid());
    }
}
