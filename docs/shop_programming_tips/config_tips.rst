Konfigurations-Tips
===================

.. |br| raw:: html

   <br />

Die folgenden *Defines* für die ``config.JTL-Shop.ini.php`` wurden eingeführt, um die Entwicklungsarbeit zu
vereinfachen, das Debugging zu verbessern oder die Konfiguration von Parametern zu ermöglichen, ohne Core-Dateien zu
bearbeiten:

+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| Konstante                               | Zweck                                                                                         |
+=========================================+===============================================================================================+
| ``DB_SOCKET``                           | erlaubt die Definition eines Sockets zur Verbindung mit der DB                                |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SHOP_LOG_LEVEL``                      | definiert den Wert für error_reporting im Frontend                                            |
|                                         | (beispielsweise ``E_ALL`` oder ``0``)                                                         |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SYNC_LOG_LEVEL``                      | definiert Fehleranzeige in *dbeS*                                                             |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``ADMIN_LOG_LEVEL``                     | definiert Fehleranzeige im Backend                                                            |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SMARTY_LOG_LEVEL``                    | definiert Fehleranzeige in *Smarty* - insbesondere wichtig                                    |
|                                         | für die Template-Entwicklung                                                                  |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_SHOP``                        | aktiviert den Profiler *XHprof*                                                               |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_QUERIES``                     | erlaubt das Debugging von SQL-Queries                                                         |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_QUERIES_ECHO``                | gibt eine Statistik über Abfragen auf jeder Seite aus,                                        |
|                                         | wenn DEBUG_QUERIES gesetzt ist                                                                |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``DEBUG_LEVEL``                         | konfiguriert die Verbosity dieser Debug-Ausgabe                                               |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SMARTY_USE_SUB_DIRS``                 | kann die Verwendung von Unterordnern für kompilierte Smarty-Templates                         |
|                                         | aktivieren                                                                                    |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``JOBQUEUE_LIMIT_M_EXPORTE``            | konfiguriert die Exporte - wichtig, wenn Cronjob genutzt wird                                 |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``JOBQUEUE_LIMIT_JOBS``                 | konfiguriert die Exporte - wichtig, wenn Cronjob genutzt wird                                 |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SAVE_BOT_SESSION``                    | erstellt neue Sessions pro Bot-Aufruf                                                         |
|                                         | (siehe auch: :doc:`botsessions` )                                                             |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_PLUGINS``                     | vom Typ BOOL, aktiviert den Plugin-Profiler, der im Backend unter ``admin/profiler.php`` |br| |
|                                         | die Laufzeiten einzelner Hooks und Dateien aufbereitet ausgibt                                |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``FILTER_SQL_QUERIES``                  | filtert Duplikate beim SQL-Debugging aus                                                      |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``NICEDB_EXCEPTION_BACKTRACE``          | gibt den kompletten Backtrace einer NiceDB-Exception aus                                      |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``NICEDB_EXCEPTION_ECHO``               | gibt den Exception-Message-String aus                                                         |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``ADMIN_MIGRATION``                     | ermöglicht die Anzeige aller DB-Migrationen im Backend                                        |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``NICEDB_DEBUG_STMT_LEN``               | legt die Länge der mit ``PROFILE_QUERIES`` ausgegebenen SQL-Queries fest |br|                 |
|                                         | (default: 500 Zeichen)                                                                        |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``KEEP_SYNC_FILES``                     | vom letzten Wawi-Sync gesendete Dateien werden nicht gelöscht                                 |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SHOW_DEBUG_BAR``                      | blendet im Frontend die PHP-Debug-Bar ein (siehe auch: Abschnitt :ref:`label_debugbar` )      |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SAFE_MODE``                           | deaktiviert alle Plugins und aus Plugins stammende Elemente (Portlets, Widgets, Payment etc.) |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+

**Komplettes Beispiel:**

Im folgenden Beispiel sind **ALLE** möglichen Konstanten aufgelistet, um zu veranschaulichen, welche Werte sie
annehmen können.

.. caution::

    Dieses Beispiel ist nicht dazu gedacht, unangepasst in einen laufenden Onlineshop übernommen zu werden! |br|
    Bitte verfahren Sie mit diesen Konstanten sehr vorsichtig, da hier mitunter sehr viele Daten ausgegeben werden!

.. code-block:: php

    <?php
    define('PFAD_ROOT', '/var/www/html/shopname/');
    define('URL_SHOP', 'https://shop5.jtl-software.de');

    define('DB_HOST', '[hostname]');
    define('DB_NAME', '[schemename]');
    define('DB_USER', '[username]');
    define('DB_PASS', '[password]');
    define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');

    define('BLOWFISH_KEY', '123456789123456789123456');

    // don't save sessions when bot is detected
    define('SAVE_BOT_SESSION', 3);


    // All of the following constants should normally not be used in productive environments!

    // enables printing of all warnings/infos/errors for the shop frontend
    define('SHOP_LOG_LEVEL', E_ALL);

    // enables printing of all warnings/infos/errors for the dbeS sync
    define('SYNC_LOG_LEVEL', E_ALL);

    // enables printing of all warnings/infos/errors for the admin backend
    define('ADMIN_LOG_LEVEL', E_ALL);

    // enables printing of all warnings/infos/errors for the smarty templates
    define('SMARTY_LOG_LEVEL', E_ALL);

    // excplicitly show errors
    ini_set('display_errors', 1);

    // user defined cronjob vars
    define('JOBQUEUE_LIMIT_M_EXPORTE', 75000);
    define('JOBQUEUE_LIMIT_JOBS', 10);

    // support for xhprof profiler
    define('PROFILE_SHOP', false);

    // enable/disable plugin profiler
    define('PROFILE_PLUGINS', false);

    // enable/disable debugging for class.core.NiceDB
    define('PROFILE_QUERIES', false);

    // (don't) echo ouput into frontend
    define('PROFILE_QUERIES_ECHO', false);

    // debug granularity
    // 0: query counts only
    // 1: query counts, affected tables, timings
    // 2: add errors and add single statements for jtldbg
    // 3: add backtrace for jtldbg
    define('DEBUG_LEVEL', 3);

    // smarty cache uses sub directories
    define('SMARTY_USE_SUB_DIRS', true);

    // display the PHP-Debug-Bar in frontend
    define('SHOW_DEBUG_BAR', true);

    // keep sync files after WaWi-synchronization
    define('KEEP_SYNC_FILES', true);

    // filtert duplikate beim sql-debugging
    define('FILTER_SQL_QUERIES', true);

    // show all DB-migrations
    define('ADMIN_MIGRATION', true);

    // show full exception-backtrace
    define('NICEDB_EXCEPTION_BACKTRACE', true);

    // show exception-message only
    define('NICEDB_EXCEPTION_ECHO', true);

    // define the query-output-length of 'PROFILE_QUERIES'
    define('NICEDB_DEBUG_STMT_LEN', 1500);

    // disable all plugins and all elements provided by plugins, such as portlets, widgets payment methods, etc.
    define('SAFE_MODE', true);
