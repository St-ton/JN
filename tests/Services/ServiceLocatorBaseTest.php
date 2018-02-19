<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Exceptions\ServiceNotFoundException;

require_once __DIR__ . '/../bootstrap.php';

class ServiceLocatorBaseTest extends \PHPUnit_Framework_TestCase
{
    public function test_singleton_happyPath()
    {
        $container = new ContainerBase();
        $container->setSingleton(ContainerInterface::class, function ($locator) {
            $this->assertInstanceOf(ContainerInterface::class, $locator);

            return new ContainerBase();
        });
        $this->assertTrue($container->getInstance(ContainerInterface::class) instanceof ContainerBase);
    }

    public function test_setSingleton_passIntegerInsteadOfCallableOrObject_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $container->setSingleton(ContainerInterface::class, 10);
    }

    public function test_setSingleton_passInvalidInterface_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $container->setSingleton(10, function () {
        });
    }

    public function test_getInstance_missingDeclaration_throwsServiceNotFoundException()
    {
        $container = new ContainerBase();
        $this->expectException(ServiceNotFoundException::class);
        $container->getInstance('doesnotexist');
    }

    public function test_factory_happyPath()
    {
        $container = new ContainerBase();
        $container->setFactory(ContainerInterface::class, function ($locator) {
            $this->assertInstanceOf(ContainerInterface::class, $locator);

            return new ContainerBase();
        });
        $instance1 = $container->getNew(ContainerInterface::class);
        $instance2 = $container->getNew(ContainerInterface::class);
        $this->assertInstanceOf(ContainerInterface::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function test_setFactory_invalidInterface_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $container->setFactory(10, function () {

        });
    }

    public function test_setFactory_invalidCallable_throwsInvalidArgumentException()
    {
        $container = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $container->setFactory(ContainerInterface::class, 10);
    }

    public function test_getNew_missingDeclaration_throwsServiceNotFoundException()
    {
        $container = new ContainerBase();
        $this->expectException(ServiceNotFoundException::class);
        $container->getNew('doesnotexist');
    }


    // EXTENSIBILITY

    public function test_singletonDecorator_happyPath()
    {
        require_once 'HelloWorldServiceInterface.php';
        require_once 'HelloWorldService.php';
        require_once 'HelloWorldTrimmingServiceDecorator.php';

        $container = new ContainerBase();
        $container->setSingleton(HelloWorldServiceInterface::class, function () {
            return new HelloWorldService();
        });
        $inner = $container->getSingleton(HelloWorldServiceInterface::class);
        $container->setSingleton(HelloWorldServiceInterface::class, function () use ($inner) {
            return new HelloWorldTrimmingServiceDecorator($inner());
        });
        /** @var HelloWorldServiceInterface $service */
        $service = $container->getInstance(HelloWorldServiceInterface::class);
        $this->assertEquals('Hello World', $service->getHelloWorldString());
    }

    public function test_factoryDecorator_happyPath()
    {
        require_once 'HelloWorldServiceInterface.php';
        require_once 'HelloWorldService.php';
        require_once 'HelloWorldTrimmingServiceDecorator.php';

        $container = new ContainerBase();
        $container->setFactory(HelloWorldServiceInterface::class, function () {
            return new HelloWorldService();
        });
        $factory = $container->getFactory(HelloWorldServiceInterface::class);
        $container->setFactory(HelloWorldServiceInterface::class, function () use ($factory) {
            return new HelloWorldTrimmingServiceDecorator($factory());
        });
        /** @var HelloWorldServiceInterface $service */
        $service = $container->getNew(HelloWorldServiceInterface::class);
        $this->assertEquals('Hello World', $service->getHelloWorldString());
        $this->assertNotSame($service, $container->getNew(HelloWorldServiceInterface::class));
    }
}
