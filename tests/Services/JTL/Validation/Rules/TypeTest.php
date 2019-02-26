<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Tests\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\Rules\Type;
use Tests\BaseTestCase;

/**
 * Class TypeTest
 * @package Services\JTL\Validation\Rules
 */
class TypeTest extends BaseTestCase
{
    public function test()
    {
        $rule = new Type('integer');
        $this->assertTrue($rule->validate(10)->isValid());
        $this->assertFalse($rule->validate('10')->isValid());
    }
}
