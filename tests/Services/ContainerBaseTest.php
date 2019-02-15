<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Services\ContainerBase;
use JTL\Services\ContainerInterface;
use PHPUnit\Framework\TestCase;
use \Exception;
use \InvalidArgumentException;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Class ContainerBaseTest
 * @package Services
 */
class ContainerBaseTest extends TestCase
{
    public function test_singleton_happyPath()
    {
        $container = new ContainerBase();
        $container->singleton(ContainerInterface::class, function ($locator) {
            $this->assertInstanceOf(ContainerInterface::class, $locator);

            return new ContainerBase();
        });
        $this->assertTrue($container->get(ContainerInterface::class) instanceof ContainerBase);
    }

    public function test_setSingleton_passIntegerInsteadOfCallableOrObject_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(InvalidArgumentException::class);
        $container->singleton(ContainerInterface::class, 10);
    }

    public function test_setSingleton_passInvalidInterface_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(InvalidArgumentException::class);
        $container->singleton(10, function () {
        });
    }

    public function test_getInstance_missingDeclaration_throwsServiceNotFoundException()
    {
        $container = new ContainerBase();
        $this->expectException(ServiceNotFoundException::class);
        $container->get('doesnotexist');
    }

    public function test_factory_happyPath()
    {
        $container = new ContainerBase();
        $container->bind(ContainerInterface::class, function ($locator) {
            $this->assertInstanceOf(ContainerInterface::class, $locator);

            return new ContainerBase();
        });
        $instance1 = $container->get(ContainerInterface::class);
        $instance2 = $container->get(ContainerInterface::class);
        $this->assertInstanceOf(ContainerInterface::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function test_setFactory_invalidInterface_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(InvalidArgumentException::class);
        $container->bind(10, function () {

        });
    }

    public function test_setFactory_invalidCallable_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(InvalidArgumentException::class);
        $container->bind(ContainerInterface::class, 10);
    }

    public function test_getNew_missingDeclaration_throwsServiceNotFoundException()
    {
        $container = new ContainerBase();
        $this->expectException(ServiceNotFoundException::class);
        $container->get('doesnotexist');
    }


    // EXTENSIBILITY

    public function test_singletonDecorator_happyPath()
    {
        require_once 'HelloWorldServiceInterface.php';
        require_once 'HelloWorldService.php';
        require_once 'HelloWorldTrimmingServiceDecorator.php';

        $container = new ContainerBase();
        $container->singleton(HelloWorldServiceInterface::class, function () {
            return new HelloWorldService();
        });
        $inner = $container->getFactoryMethod(HelloWorldServiceInterface::class);
        $container->singleton(HelloWorldServiceInterface::class, function () use ($inner) {
            return new HelloWorldTrimmingServiceDecorator($inner());
        });
        /** @var HelloWorldServiceInterface $service */
        $service = $container->get(HelloWorldServiceInterface::class);
        $this->assertEquals('Hello World', $service->getHelloWorldString());
    }

    public function test_factoryDecorator_happyPath()
    {
        require_once 'HelloWorldServiceInterface.php';
        require_once 'HelloWorldService.php';
        require_once 'HelloWorldTrimmingServiceDecorator.php';

        $container = new ContainerBase();
        $container->bind(HelloWorldServiceInterface::class, function () {
            return new HelloWorldService();
        });
        $factory = $container->getFactoryMethod(HelloWorldServiceInterface::class);
        $container->bind(HelloWorldServiceInterface::class, function () use ($factory) {
            return new HelloWorldTrimmingServiceDecorator($factory());
        });
        /** @var HelloWorldServiceInterface $service */
        $service = $container->get(HelloWorldServiceInterface::class);
        $this->assertEquals('Hello World', $service->getHelloWorldString());
        $this->assertNotSame($service, $container->get(HelloWorldServiceInterface::class));
    }


    // CIRCULAR REFERENCES

    public function test_detectCircularReferences()
    {
        $container = new ContainerBase();
        $container->bind('id1', function (\Psr\Container\ContainerInterface $container) {
            $container->get('id2');

            return new \stdClass();
        });
        $container->bind('id2', function (\Psr\Container\ContainerInterface $container) {
            $container->get('id1');

            return new \stdClass();
        });
        $this->expectException(CircularReferenceException::class);
        $container->get('id1');
    }


    // WHAT HAPPENS, IF SINGLETON ALREADY USED

    public function test_setSingleton_setAlreadyUsed_throws()
    {
        $container = new ContainerBase();
        $container->singleton('id', function () {
            return new ContainerBase();
        });
        $container->get('id');
        $this->expectException(Exception::class);
        $container->singleton('id', function() {
            return new ContainerBase();
        });
    }

    // WHAT HAPPENS, IF SINGLETON IS OVERRIDDEN BY FACTORY AND VICE VERSA

    public function test_overrideFactoryWithSingleton_throwsException()
    {
        $container = new ContainerBase();
        $container->bind('id', function(){
            return new ContainerBase();
        });
        $this->expectException(Exception::class);
        $container->singleton('id', function(){
            return new ContainerBase();
        });
    }

    public function test_ovverrideSingletonWithFactory_throwsException()
    {
        $container = new ContainerBase();
        $container->singleton('id', function(){
            return new ContainerBase();
        });
        $this->expectException(Exception::class);
        $container->bind('id', function(){
            return new ContainerBase();
        });
    }
}
