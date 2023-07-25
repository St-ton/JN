Container
=========

.. |br| raw:: html

   <br />

Since JTL-Shop 5.0.0, a so-called "*Dependency Injection Container*" is available in the online shop. |br|
In the future, a large part of all JTL-Shop components will be provided via this container. Additionally, the
behaviour of the online shop can be modified or extended by plug-ins via the components registered in the container.

SOLID & dependency inversion
----------------------------

The container is used to implement the "*Dependency Inversion Principle*".  |br|
There is a wealth of information regarding this subject on the internet. Therefore, we recommend that developers first familiarise
themselves with *SOLID* and with *dependency inversion* in particular.

Container / Component retrieval
-------------------------------

.. code-block:: php

    <?php

    use JTL\Shop;
    use Services\JTL\PasswordServiceInterface;

    $container       = Shop::Container();
    $passwordService = $container->get(PasswordServiceInterface::class);
    $randomPassword  = $passwordService->generate(12);

As you can see, services and other components of JTL-Shop can be obtained via the container. |br|
The container is designed by the PHP-FIG according to PSR-11 (https://www.php-fig.org/psr/psr-11/).
In case you are using an IDE with *IntelliSense*, we have also added a method to the container for all components
provided by JTL-Shop.

.. code-block:: php

    <?php

    use JTL\Shop;
    use JTL\Services\JTL\PasswordServiceInterface;

    $container       = Shop::Container();
    $passwordService = $container->getPasswordService();
    $randomPassword  = $passwordService->generate(12);

You can see which components are provided by the JTL-Shop by using the available methods of the
``/includes/src/Services/DefaultServicesInterface.php`` interface.

Testing for existence
"""""""""""""""""""""

If you want to see if a component is available, you can do the following. |br|
(Note: All components defined in ``DefaultServicesInterface`` are always available.)

.. code-block:: php

    <?php

    use JTL\Services\JTL\PasswordServiceInterface;

    $container = Shop::Container();
    if ($container->has(PasswordServiceInterface::class)) {
        // component exists
    }

Registering custom components
-----------------------------

You have the option to register custom components in the container. |br|
For this, you first need a class that you want to provide. We recommend that you create an interface
or abstract class for each component. Only then can the *decorator pattern* be implemented (see below)

.. code-block:: php

    <?php

    interface HelloWorldGeneratorInterface
    {
        public function get();
    }

    class HelloWorldGenerator implements HelloWorldGeneratorInterface
    {
        public function get()
        {
            return " Hello World ";
        }
    }

Now, you can register the relevant component in the container:

.. code-block:: php

    <?php

    $container = JTL\Shop::Container();
    $container->setFactory(HelloWorldGeneratorInterface::class, function($container) {
        return new HelloWorldGenerator();
    });

Now, the component is available via the container and can be retrieved as follows:

.. code-block:: php

    <?php

    $container           = JTL\Shop::Container();
    $HelloWorldGenerator = $container->get(HelloWorldInterface::class);
    $HelloWorldGenerator->get(); // "Hello World" will be output

Overwriting components
----------------------

You can replace all registered components in the container. |br|
The requirement for this is that you implement the interface used or, in the case of an abstract class, that it be inherited from
the class. |br|

.. attention::
    When you overwrite a component, this will apply to the entire online shop! |br|
    So, be sure to use caution and only overwrite components if your implementation works
    reliably.

.. code-block:: php

    <?php

    class TrimmedHelloWorldGenerator implements HelloWorldGeneratorInterface
    {
        public function get()
        {
            return "Hello World";
        }
    }

    $container = Shop::Container();
    $container->setFactory(HelloWorldGeneratorInterface::class, function($container) {
        return new TrimmedHelloWorldGenerator();
    });

Extending components (*decorator pattern*)
------------------------------------------

You can extend all components available via the container, if an abstract class or interface is
available, using the *decorator pattern*.

Here is an example that extends the "*HelloWorldContainer*":

.. code-block:: php

    <?php

    // Decorator class
    class TrimmingHelloWorldGeneratorDecorator implements HelloWorldGeneratorInterface
    {
        protected $inner;

        public function __construct($inner)
        {
            $this->inner = $inner;
        }

        public function get()
        {
            return trim($this->inner->get());
        }
    }

    // Register decorator

    $container = Shop::Container();
    $originalFactoryMethod = $container->getFactory(HelloWorldGeneratorInterface::class);
    $container->setFactory(HelloWorldGeneratorInterface::class, function($container) use ($originalFactoryMethod) {
        $inner = $originalFactoryMethod($container);
        return new TrimmingHelloWorldGeneratorDecorator($inner);
    });


    // Use component
    $helloWorldGenerator = $container->get(HelloWorldGeneratorInterface::class);
    echo $helloWordGenerator->get(); // return "Hello World" instead of " Hello World "


Factory or singleton
--------------------

When you register a component in the container, you have the option to choose between *factory* and
*singleton* patterns.

.. code-block:: php

    <?php
    $container = JTL\Shop::Container();

    $container->setSingleton(HelloWorldGeneratorInterface::class, function() { /*...*/ });
    // or
    $container->setFactory(HelloWorldGeneratorInterface::class, function() { /*...*/ });

This is not to be confused with the “*factory method*"! |br|
Both a *singleton* and a *factory* need a *factory method* to take over the creation of the
relevant object. The *factory method* can be retrieved for both a *singleton* and a *factory* using the
same approach:

.. code-block:: php

    <?php
    $container = Shop::Container();
    $factoryMethod = $container->getFactoryMethod(HelloWorldGeneratorInterface::class);

With a *singleton*, the *factory method* is called up only once and only one
object exists application-wide. With a *factory*, the *factory method* is called again with each call and a new object
is created.

Hook for registration, extension, or overwriting of components
--------------------------------------------------------------

Components must be registered, extended, or overwritten as soon as possible, otherwise inconsistencies
can occur. Therefore, the ``HOOK_GLOBALINCLUDE_INC`` (131) hook should be used for this.

.. note::

    Some components cannot be overwritten because they have already been used previously.

For example, the "*DbInterface*” component cannot be overwritten.
