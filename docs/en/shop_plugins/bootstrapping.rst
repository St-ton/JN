Bootstrapping
=============

.. |br| raw:: html

   <br />

Bootstrapping bezeichnet im Kontext von JTL-Shop die Initialisierung eines Plugins zur anschließenden Nutzung
des *EventDispatchers*.

Struktur
--------

Zentraler Einstiegspunkt des Bootstrappers ist die Datei ``Bootstrap.php`` im Hauptverzeichnis eines Plugins. |br|
In dieser Datei muss die Klasse ``Bootstrap`` im *namespaces* des Plugins angelegt werden und diese muss
das Interface ``JTL\Plugin\BootstrapperInterface`` implementieren.

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
         * @param bool $deleteData
         * @return mixed
         */
        public function uninstalled(bool $deleteData);

        /**
         * @return mixed
         */
        public function enabled();

        /**
         * @return mixed
         */
        public function disabled();

        /**
         * @param string $oldVersion
         * @param string $newVersion
         * @return mixed
         */
        public function updated($oldVersion, $newVersion);

        /**
         * @param int         $type
         * @param string      $title
         * @param null|string $description
         */
        public function addNotify($type, $title, $description = null);

        /**
         * @return PluginInterface
         */
        public function getPlugin(): PluginInterface;

        /**
         * @return DbInterface
         */
        public function getDB(): DbInterface;

        /**
         * @param DbInterface $db
         */
        public function setDB(DbInterface $db): void;

        /**
         * @return JTLCacheInterface
         */
        public function getCache(): JTLCacheInterface;

        /**
         * @param JTLCacheInterface $cache
         */
        public function setCache(JTLCacheInterface $cache): void;

        /**
         * @param string    $tabName
         * @param int       $menuID
         * @param JTLSmarty $smarty
         * @return string
         */
        public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string;

        /**
         * @param LinkInterface $link
         * @param JTLSmarty     $smarty
         * @return bool
         */
        public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool;
    }

.. danger::

    Die Methode ``boot()`` der Klasse ``Bootstrap`` sollte ausschließlich dazu dienen, Hooks zu registrierten. |br|
    Dieser Methode kommt eine zentrale Bedeutung zu: **Sie wird bei jedem Frontend- UND Backend-Aufruf
    aufgerufen.** Ein Fehler in ``boot()`` kann deshalb dazu führen, dass das komplette Backend (und somit auch die
    Möglichkeit, das fehlerhafte Plugin zu deinstallieren) versperrt ist.

    Beispiele hierfür wären Programmierfehler wie Endlosschleifen, nicht antwortende Server von Drittanbietern
    und dergleichen. |br|
    **Ein "Stopp" der Applikation an dieser Stelle stoppt auch die Administrationsobfläche!**

Implementierbare Methoden
"""""""""""""""""""""""""

+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| Methode                                                                 | Hinweis zur Implementierung                                                       |
+=========================================================================+===================================================================================+
| ``installed()``                                                         | Wird unmittelbar nach der Installation eines Plugins aufgerufen. |br|             |
|                                                                         | Bietet sich daher für Logik an, die einmalig ausgeführt werden muss, |br|         |
|                                                                         | aber für Migrationen ungeeignet ist.                                              |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| ``updated($oldVersion, $newVersion)``                                   | Wird nach der Plugin-Aktualisierung über das Backend von JTL-Shop ausgeführt.     |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| ``enabled()``                                                           | Wird ausgeführt, nachdem ein Plugin aktiviert wurde.                              |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| ``disabled()``                                                          | Wird ausgeführt, nachdem ein Plugin deaktiviert wurde.                            |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| ``boot(Dispatcher $dispatcher)``                                        | Wird möglichst früh im Verlauf eines jeden Requests in JTL-Shop aufgerufen |br|   |
|                                                                         | (sowohl im Kontext des Front- und Backends als auch während eines |br|            |
|                                                                         | Abgleich mit JTL-Wawi).                                                           |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| ``uninstalled(bool $deleteData = true)``                                | Wird ausgeführt, nachdem ein Plugin im Backend komplett deinstalliert wurde. |br| |
|                                                                         | Falls der Parameter TRUE ist, wünscht der Nutzer, dass Plugin-Daten |br|          |
|                                                                         | permanent gelöscht werden sollen (bspw. Datenbanktabellen).                       |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| ``renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty)`` | Kann genutzt werden, um HTML-Code für eigene Plugin-Tabs auszugeben, |br|         |
|                                                                         | beispielsweise via ``$smarty->fetch()``.                                          |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+
| ``prepareFrontend(LinkInterface $link, JTLSmarty $smarty)``             | Kann genutzt werden, um in Smarty eigene Variablen vor der Anzeige von |br|       |
|                                                                         | *Fontend Links* zu definieren. |br|                                               |
|                                                                         | Sie sollte in diesem Fall TRUE zurückgeben.                                       |
+-------------------------------------------------------------------------+-----------------------------------------------------------------------------------+

.. _label_bootstrapping_eventdispatcher:

Der EventDispatcher
-------------------

Innerhalb der ``boot()``-Methode können *EventListener* registriert werden, die sich als flexiblere Alternative
zu Hooks anbieten. |br|
Im Vergleich zu den via ``info.xml`` registrierten Hooks können *EventListener* dynamisch generiert werden.

Jeder Hook erzeugt automatisch ein Event mit dem Namen ``shop.hook.<HOOK-ID>``. |br|
Um also beispielsweise den Hook ``HOOK_ARTIKEL_CLASS_FUELLEARTIKEL`` zu nutzen, lässt sich Folgendes
innerhalb der ``boot()``-Methode schreiben:

.. code-block:: php

    $dispatcher->listen('shop.hook.' . \HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, function (array $args) {
        $args['oArtikel']->cName = 'Neuer Name';
    });

Dies hat den Vorteil, dass der Listener in Abhängigkeit einer Plugin-Option registriert werden kann. Somit wird
der Hook, anders als bei statischen Hooks, die in der ``info.xml`` registriert wurden, nicht immer ausgeführt.|br|
Auch muss so der objektorientierte Kontext des Bootstrappers nicht verlassen werden, während Hooks jeweils nur
PHP-Dateien mit funktionalem Code aufrufen können.

Ab JTL-Shop 5.0.0 kann zudem auch die Priorität, ähnlich dem Hook-Knoten ``<priority>`` der ``info.xml``, als
dritter Parameter angegeben werden:

.. code-block:: php
   :emphasize-lines: 10

    /**
     * @inheritdoc
     */
    public function boot(Dispatcher $dispatcher)
    {
        parent::boot($dispatcher);
        $dispatcher->listen(
            'shop.hook.' . \HOOK_ARTIKEL_CLASS_FUELLEARTIKEL,
            function () { /* do something */ },
            10
        );
    }

Siehe auch Abschnitt "Die info.xml", :ref:`label_infoxml_hooks`.

Innerhalb des Bootstrappers besteht via ``$this->getPlugin()`` immer Zugriff auf die Instanz des Plugins, sodass
die Nutzung des PluginHelpers vermieden werden kann.
Auch besteht via ``$this->getDB()`` Zugriff auf die Datenbank sowie via ``$this->getCache()`` auf den Objektcache.
Es ist daher nicht nötig, diese Instanzen über den DI-Container ``Shop::Container()->getDB()`` oder
``Shop::Container()->getCache()`` zu holen.
