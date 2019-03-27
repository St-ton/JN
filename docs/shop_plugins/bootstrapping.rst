Bootstrapping
=============

Bootstrapping bezeichnet im Kontext des JTL-Shops die Initialisierung eines Plugins zur anschließenden Nutzung des EventDispatchers.

Struktur
--------

Zentraler Einstiegspunkt des Bootstrappers ist die Datei *Bootstrap.php* im Hauptverzeichnis eines Plugins.
In dieser Datei muss die Klasse *Bootstrap* im Namespaces des Plugins angelegt werden und diese muss das Interface *JTL\Plugin\BootstrapperInterface* implementieren.

Das Interface sieht wie folgt aus:

.. code-block:: php

	/**
	 * Interface BootstrapperInterface
	 * @package JTL\Plugin
	 */
	interface BootstrapperInterface
	{
	    /**
	     * @param Dispatcher $dispatcher
	     */
	    public function boot(Dispatcher $dispatcher);

	    /**
	     * @return mixed
	     */
	    public function installed();

	    /**
	     * @return mixed
	     */
	    public function uninstalled();

	    /**
	     * @return mixed
	     */
	    public function enabled();

	    /**
	     * @return mixed
	     */
	    public function disabled();

	    /**
	     * @param mixed $oldVersion
	     * @param mixed $newVersion
	     * @return mixed
	     */
	    public function updated($oldVersion, $newVersion);

	    /**
	     * @param int         $type
	     * @param string      $title
	     * @param null|string $description
	     */
	    public function addNotify($type, $title, $description = null);
	}


* *installed()* wird unmittelbar nach der Installation eines Plugins aufgerufen und bietet sich daher für Logik an, die einmalig ausgeführt werden muss aber für Migrationen ungeeignet ist.
* *updated($oldVersion, $newVersion)* wird nach der Aktualisierung eines Plugins über das Shopbackend ausgeführt,
* *enabled()* nachdem ein Plugin aktiviert wurde
* *disabled()* nachdem es deaktviert wurde
* *uninstalled()* nachdem es im Backend komplett deinstalliert wurde.
* *boot(Dispatcher $dispatcher)* wird möglichst früh im Verlauf eines jeden Requests im Shop aufgerufen. Sowohl im Kontext des Front- und Backends als auch während eines Wawi-Abgleichs.

EventDispatcher
---------------

Innerhalb der *boot()*-Methode können EventListener registriert werden, die sich als flexiblere Alternative zu Hooks anbieten.
Im Vergleich zu den via info.xml registrierten Hooks können EventListener dynamisch generiert werden.

Jeder Hook erzeugt automatisch auch ein Event mit dem Namen ``shop.hook.<HOOK-ID>``.
Um also beispielsweise den Hook HOOK_ARTIKEL_CLASS_FUELLEARTIKEL zu nutzen, lässt sich folgendes innerhalb der *boot()*-Methode schreiben:

.. code-block:: php

    $dispatcher->listen('shop.hook.' . \HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, function (array $args) {
        $args['oArtikel']->cName = 'Neuer Name';
    });

Dies hat den Vorteil, dass der Listener z.B. nur in Abhängigkeit einer Plugin-Option registriert werden kann und somit anders als bei statischen Hooks die in der info.xml registriert wurden der Hook nicht immer ausgeführt werden muss.
Auch muss so der objektorientierte Kontext des Bootstrappers nicht verlassen werden, während Hooks jeweils nur PHP-Dateien mit funktionalem Code aufrufen können.

Innerhalb des Bootstrappers hat man via ``$this->getPlugin()`` immer Zugriff auf die Instanz des Plugins, via ``$this->getDB()`` auf die Datenbank sowie via ``$this->getCache()`` auf den Objektcache.
