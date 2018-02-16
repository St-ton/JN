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
        $locator = new ContainerBase();
        $locator->setSingleton(ContainerInterface::class, function ($locator) {
            $this->assertInstanceOf(ContainerInterface::class, $locator);

            return new ContainerBase();
        });
        $this->assertTrue($locator->getInstance(ContainerInterface::class) instanceof ContainerBase);

        $locator->setSingleton(ContainerInterface::class, new ContainerBase());
        $this->assertTrue($locator->getInstance(ContainerInterface::class) instanceof ContainerBase);
    }

    public function test_setSingleton_passIntegerInsteadOfCallableOrObject_throwsInvalidArgumentException()
    {
        $locator = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $locator->setSingleton(ContainerInterface::class, 10);
    }

    public function test_setSingleton_passInvalidInterface_throwsInvalidArgumentException()
    {
        $locator = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $locator->setSingleton(10, function () {
        });
    }

    public function test_getInstance_missingDeclaration_throwsServiceNotFoundException()
    {
        $locator = new ContainerBase();
        $this->expectException(ServiceNotFoundException::class);
        $locator->getInstance('doesnotexist');
    }

    public function test_factory_happyPath()
    {
        $locator = new ContainerBase();
        $locator->setFactory(ContainerInterface::class, function ($locator) {
            $this->assertInstanceOf(ContainerInterface::class, $locator);

            return new ContainerBase();
        });
        $instance1 = $locator->getNew(ContainerInterface::class);
        $instance2 = $locator->getNew(ContainerInterface::class);
        $this->assertInstanceOf(ContainerInterface::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function test_setFactory_invalidInterface_throwsInvalidArgumentException()
    {
        $locator = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $locator->setFactory(10, function () {

        });
    }

    public function test_setFactory_invalidCallable_throwsInvalidArgumentException()
    {
        $locator = new ContainerBase();
        $this->expectException(\InvalidArgumentException::class);
        $locator->setFactory(ContainerInterface::class, 10);
    }

    public function test_getNew_missingDeclaration_throwsServiceNotFoundException()
    {
        $locator = new ContainerBase();
        $this->expectException(ServiceNotFoundException::class);
        $locator->getNew('doesnotexist');
    }


    // EXTENSIBILITY

    public function test_singletonDecorator_happyPath()
    {
        require_once 'HelloWorldServiceInterface.php';
        require_once 'HelloWorldService.php';
        require_once 'HelloWorldTrimmingServiceDecorator.php';

        $locator           = new ContainerBase();
        $helloWorldService = new HelloWorldService();
        $locator->setSingleton(HelloWorldServiceInterface::class, $helloWorldService);
        $helloWorldService = $locator->getInstance(HelloWorldServiceInterface::class);
        $decorator         = new HelloWorldTrimmingServiceDecorator($helloWorldService);
        $locator->setSingleton(HelloWorldServiceInterface::class, $decorator);
        /** @var HelloWorldServiceInterface $finalService */
        $finalService = $locator->getInstance(HelloWorldServiceInterface::class);
        $this->assertEquals('Hello World', $finalService->getHelloWorldString());
    }

    public function test_factoryDecorator_happyPath()
    {
        require_once 'HelloWorldServiceInterface.php';
        require_once 'HelloWorldService.php';
        require_once 'HelloWorldTrimmingServiceDecorator.php';

        $locator = new ContainerBase();
        $locator->setFactory(HelloWorldServiceInterface::class, function () {
            return new HelloWorldService();
        });
        $factory = $locator->getFactory(HelloWorldServiceInterface::class);
        $locator->setFactory(HelloWorldServiceInterface::class, function () use ($factory) {
            return new HelloWorldTrimmingServiceDecorator($factory());
        });
        /** @var HelloWorldServiceInterface $service */
        $service = $locator->getNew(HelloWorldServiceInterface::class);
        $this->assertEquals('Hello World', $service->getHelloWorldString());
        $this->assertNotSame($service, $locator->getNew(HelloWorldServiceInterface::class));
    }
}
