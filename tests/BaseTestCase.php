<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionObject;

abstract class BaseTestCase extends TestCase
{
    protected function tearDown()
    {
        $refl = new ReflectionObject($this);

        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}