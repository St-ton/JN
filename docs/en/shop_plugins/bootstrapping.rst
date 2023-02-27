Bootstrapping
=============

.. |br| raw:: html

   <br />

Within the context of the JTL-Shop, Bootstrapping refers to the initialisation of a plug-in for subsequent use of
the *EventDispatcher*.

Structure
---------

The central starting point for the bootstrap is the ``Bootstrap.php`` file in the main directory of a plug-in. |br|
In this file, the ``Bootstrap`` class must be created in the *namespace* of the plug-in and this must implement
the interface ``JTL\Plugin\BootstrapperInterface``.

The interface looks like this:

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

    The ``boot()`` method of the ``Bootstrap`` class should only be used for the purpose of registering hooks. |br|
    This method is of crucial importance: **It will be called up with every front end AND back end
    call.** Therefore, an error in ``boot()`` can then lead to the back end being completely blocked (and with that, the
    possibility of uninstalling the problematic plug-in at all.

    Examples of this are programming errors like infinite loops, unresponsive servers of third party providers
    and the like. |br|
    **A "stop" in the application at this point, will also stop the admin interface!**

Implementable methods
"""""""""""""""""""""""""

+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| Methods                                                                 | Implementation information                                                             |
+=========================================================================+========================================================================================+
| ``installed()``                                                         | Will be called up right after the installation of a plug-in. |br|                      |
|                                                                         | It is therefore suitable for logic that needs to be executed once, |br|                |
|                                                                         | but not suitable for migrations.                                                       |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| ``updated($oldVersion, $newVersion)``                                   | Will be executed in the back end of the JTL-Shop after plug-in updates.                |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| ``enabled()``                                                           | Will be executed after a plug-in is activated.                                         |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| ``disabled()``                                                          | Will be executed after a plug-in is deactivated.                                       |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| ``boot(Dispatcher $dispatcher)``                                        | Is called up as early as possible in the process of each request in the JTL-Store |br| |
|                                                                         | (in both the front end and the back end, as well as during |br|                        |
|                                                                         | synchronisation with JTL-Wawi).                                                        |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| ``uninstalled(bool $deleteData = true)``                                | Will be executed after a plug-in is completely uninstalled in the back end. |br|       |
|                                                                         | In the case that the parameter is TRUE, this indicates that the user wants plug-in     |
|                                                                         | data |br|                                                                              |
|                                                                         | to be permanently deleted (data like database tables).                                 |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| ``renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty)`` | Can be used to render HTML code for plug-in tabs, |br|                                 |
|                                                                         | for example via ``$smarty->fetch()``.                                                  |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+
| ``prepareFrontend(LinkInterface $link, JTLSmarty $smarty)``             | Can be used to define variables in Smarty |br|                                         |
|                                                                         | before displaying *front links*. |br|                                                  |
|                                                                         | In this case, these should return as TRUE.                                             |
+-------------------------------------------------------------------------+----------------------------------------------------------------------------------------+

.. _label_bootstrapping_eventdispatcher:

The EventDispatcher
-------------------

Within the ``boot()`` method, the *EventListener* can be registered, which offers a more flexible alternative
to hooks. |br|
Compared to the hooks registered via ``info.xml``, the *EventListener* can be dynamically generated.

Every hook automatically generates the name ``shop.hook.<HOOK-ID>``. |br|
For example, to use the hook ``HOOK_ARTIKEL_CLASS_FUELLEARTIKEL``, the following
can be written within the ``boot()`` method:

.. code-block:: php

    $dispatcher->listen('shop.hook.' . \HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, function (array $args) {
        $args['oArtikel']->cName = 'Neuer Name';
    });

This offers an advantage in that the listener can be registered as dependent on a plug-in option. Therefore, unlike
the static hooks that are registered in ``info.xml``, the hook is not always executed.|br|
The object-oriented context of the bootstrap should not be left
out, as hooks can only call up PHP files with functional code.

Starting from JTL-Shop 5.0.0, the priority, as is the case with the hook node ``<priority>`` of ``info.xml``, can be specified as
a third parameter:

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

See also "Die info.xml", in :ref:`label_infoxml_hooks`.

Within the bootstrapper there is always access to the instance of the plug-in via ``$this->getPlugin()`, so that the use of
the PluginHelper can be avoided.
The database can also be accessed via ``$this->getDB()`` and object cache can be accessed via ``$this->getCache()``.
It is, therefore, not necessary to retrieve these instances via the DI container ``Shop::Container()->getDB()`` or
``Shop::Container()->getCache()``.
