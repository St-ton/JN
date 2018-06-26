<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Eloquent\Pathogen\Path;

/**
 * Class InPathTest
 * @package Services\JTL\Validation\Rules
 */
class InPathTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $testDir = __DIR__ . '/InPathTestDir';

        $rule = new InPath($testDir);

        // relativePaths
        $this->assertTrue($rule->validate('test.txt')->isValid());
        $this->assertFalse($rule->validate('../test.txt')->isValid());

        // absolutePaths
        $this->assertTrue($rule->validate($testDir . '/test.txt')->isValid());
        $this->assertFalse($rule->validate('/var/www/test.txt')->isValid());
        $this->assertFalse($rule->validate($testDir . '/../test.txt')->isValid());
    }

    public function test_baseIsPathogenPath()
    {
        $testDir = __DIR__ . '/InPathTestDir';

        $rule = new InPath(Path::fromString($testDir));

        // relativePaths
        $this->assertTrue($rule->validate('test.txt')->isValid());
        $this->assertFalse($rule->validate('../test.txt')->isValid());

        // absolutePaths
        $this->assertTrue($rule->validate($testDir . '/test.txt')->isValid());
        $this->assertFalse($rule->validate('/var/www/test.txt')->isValid());
        $this->assertFalse($rule->validate($testDir . '/../test.txt')->isValid());
    }

    public function test_pathIsPathogenPath()
    {
        $testDir = __DIR__ . '/InPathTestDir';

        $rule = new InPath($testDir);

        // relativePaths
        $this->assertTrue($rule->validate(Path::fromString('test.txt'))->isValid());
        $this->assertFalse($rule->validate(Path::fromString('../test.txt'))->isValid());

        // absolutePaths
        $this->assertTrue($rule->validate(Path::fromString($testDir . '/test.txt'))->isValid());
        $this->assertFalse($rule->validate(Path::fromString('/var/www/test.txt'))->isValid());
        $this->assertFalse($rule->validate(Path::fromString($testDir . '/../test.txt'))->isValid());
    }
}
