<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionObject;

/**
 * Class BaseTestCase
 * @package Tests
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $refl = new ReflectionObject($this);

        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && \strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_') !== 0) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}
