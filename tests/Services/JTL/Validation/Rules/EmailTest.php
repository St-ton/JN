<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\Email;
use PHPUnit\Framework\TestCase;

/**
 * Class EmailTest
 * @package Services\JTL\Validation\Rules
 */
class EmailTest extends TestCase
{
    public function test()
    {
        $rule = new Email();
        $this->assertTrue($rule->validate('martin.schophaus@jtl-software.com')->isValid());
        $this->assertFalse($rule->validate('martin.schophaus@ jtl-software.com')->isValid());
    }
}
