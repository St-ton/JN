Container
=========

.. |br| raw:: html

   <br />

Seit JTL-Shop 5.0.0 steht im JTL-Shop ein sogenannter "*Dependency Injection Container*" zur Verfügung. |br|
In Zukunft wird ein Großteil aller JTL-Shop-Komponenten über diesen Container bereitgestellt. Zudem kann das
Verhalten des Onlineshops über die im Container registrierten Komponenten von Plugins modifiziert oder erweitert werden.

SOLID & Dependency Inversion
----------------------------

Der Container dient der Umsetzung des "*Dependency Inversion Principle*".  |br|
Zu diesem Themenkomplex gibt es viele Erklärungen im Internet. Wir empfehlen Entwicklern daher zunächst, sich mit
*SOLID* und im Besonderen mit *Dependency Inversion* vertraut zu machen.

Container / Komponente holen
----------------------------

.. code-block:: php

    <?php

    use JTL\Shop;
    use Services\JTL\PasswordServiceInterface;

    $container       = Shop::Container();
    $passwordService = $container->get(PasswordServiceInterface::class);
    $randomPassword  = $passwordService->generate(12);

Wie hier zu sehen ist, können über den Container Dienste und andere Komponenten von JTL-Shop bezogen werden. |br|
Der Container ist hierbei gemäß PSR-11 von der PHP-FIG entworfen (https://www.php-fig.org/psr/psr-11/).
Für den Fall, dass Sie eine IDE mit *IntelliSense* verwenden, haben wir zudem für alle von JTL-Shop bereitgestellten
Komponenten eine Methode zum Container hinzugefügt.

.. code-block:: php

    <?php

    use JTL\Shop;
    use JTL\Services\JTL\PasswordServiceInterface;

    $container       = Shop::Container();
    $passwordService = $container->getPasswordService();
    $randomPassword  = $passwordService->generate(12);

Welche Komponenten vom JTL-Shop bereitgestellt werden, können Sie anhand der verfügbaren Methoden des Interfaces
``/includes/src/Services/DefaultServicesInterface.php`` einsehen.

Existenz prüfen
"""""""""""""""

Wenn Sie prüfen wollen, ob eine Komponente bereitsteht, können Sie dies wie folgt tun. |br|
(Hinweis: Alle in ``DefaultServicesInterface`` definierten Komponenten sind immer verfügbar.)

.. code-block:: php

    <?php

    use JTL\Services\JTL\PasswordServiceInterface;

    $container = Shop::Container();
    if ($container->has(PasswordServiceInterface::class)) {
        // die komponente existiert
    }

Eigenen Komponente registrieren
-------------------------------

Sie haben die Möglichkeit, eigene Komponenten im Container zu registrieren. |br|
Hierzu benötigen Sie zunächst eine Klasse, die Sie bereitstellen wollen. Wir empfehlen, für jede Komponente ein
Interface oder eine abstrakte Klasse zu erstellen. Nur so kann das *Decorator Pattern* eingesetzt werden (siehe unten).

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

Nun können Sie die entsprechende Komponente im Container registrieren:

.. code-block:: php

    <?php

    $container = JTL\Shop::Container();
    $container->setFactory(HelloWorldGeneratorInterface::class, function($container) {
        return new HelloWorldGenerator();
    });

Nun steht die Komponente über den Container bereit und kann wie folgt abgerufen werden:

.. code-block:: php

    <?php

    $container           = JTL\Shop::Container();
    $HelloWorldGenerator = $container->get(HelloWorldInterface::class);
    $HelloWorldGenerator->get(); // "Hello World" wird ausgegeben

Komponenten überschreiben
-------------------------

Sie können alle im Container registrierten Komponenten ersetzen. |br|
Voraussetzung hierfür ist, dass Sie das genutzte Interface implementieren oder, im Falle einer abstrakten Klasse, von
dieser erben. |br|

.. attention::
    Wenn Sie Komponenten überschreiben, gilt dies für den gesamten Onlineshop! |br|
    Seien Sie also bitte vorsichtig und überschreiben Sie nur dann Komponenten, wenn Ihre Implementation zuverlässig
    funktioniert.

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

Komponenten erweitern (*Decorator Pattern*)
-------------------------------------------

Sie können sämtliche über den Container bereitstehenden Komponenten (falls eine abstrakte Klasse oder ein Interface
bereitsteht) mit Hilfe des *Decorator Pattern* erweitern.

Hierzu ein Beispiel, das den "*HelloWorldContainer*" erweitert:

.. code-block:: php

    <?php

    // Decorator Class
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

    // Register Decorator

    $container = Shop::Container();
    $originalFactoryMethod = $container->getFactory(HelloWorldGeneratorInterface::class);
    $container->setFactory(HelloWorldGeneratorInterface::class, function($container) use ($originalFactoryMethod) {
        $inner = $originalFactoryMethod($container);
        return new TrimmingHelloWorldGeneratorDecorator($inner);
    });


    // Use Component
    $helloWorldGenerator = $container->get(HelloWorldGeneratorInterface::class);
    echo $helloWordGenerator->get(); // return "Hello World" instead of " Hello World "


Factory oder Singleton
----------------------

Wenn Sie eine Komponente im Container registrieren, haben Sie die Möglichkeit, zwischen einer *Factory* und
einem *Singleton* zu wählen.

.. code-block:: php

    <?php
    $container = JTL\Shop::Container();

    $container->setSingleton(HelloWorldGeneratorInterface::class, function() { /*...*/ });
    // oder
    $container->setFactory(HelloWorldGeneratorInterface::class, function() { /*...*/ });

Nicht zu verwechseln ist dies mit der sogenannten "*Factory Method*"! |br|
Sowohl ein *Singleton* als auch eine *Factory* benötigen eine *Factory Method*, welche die Erzeugung des jeweiligen
Objektes übernimmt. Die *Factory Method* kann sowohl für ein *Singleton* als auch für eine *Factory* auf die gleiche
Weise geholt werden:

.. code-block:: php

    <?php
    $container = Shop::Container();
    $factoryMethod = $container->getFactoryMethod(HelloWorldGeneratorInterface::class);

Bei einem *Singleton* wird die *Factory Method* nur ein einziges Mal abgerufen und nur ein einziges Objekt existiert
applikationsweit. Bei einer *Factory* wird die *Factory Method* bei jedem Abruf erneut aufgerufen und ein neues Objekt
erzeugt.

Hook zum Registrieren, Erweitern oder Überschreiben von Komponenten
-------------------------------------------------------------------

Komponenten müssen möglichst früh registriert, erweitert oder überschrieben werden, da ansonsten Inkonsistenzen
auftreten können. Daher sollte der Hook ``HOOK_GLOBALINCLUDE_INC`` (131) genutzt werden.

.. note::

    Manche Komponenten können nicht überschrieben werden, da diese schon im Voraus genutzt wurden.

Beispielsweise ist die Komponente "*DbInterface*" nicht überschreibbar.
