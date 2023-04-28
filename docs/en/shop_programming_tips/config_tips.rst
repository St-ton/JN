Configuration Tips
===================

.. |br| raw:: html

   <br />

The following *define functions* for the ``config.JTL-Shop.ini.php`` have been deployed in order to make
development easier, to improve debugging, or to enable the configuration of parameters, without having to edit core
files:

+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| Constant                               | Purpose                                                                                        |
+=========================================+===============================================================================================+
| ``DB_SOCKET``                           | allows the definition of a socket to connect to the database                                  |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SHOP_LOG_LEVEL``                      | defines the value for error_reporting in the front end                                        |
|                                         | (for example ``E_ALL`` or ``0``)                                                              |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SYNC_LOG_LEVEL``                      | defines error display in *dbeS*                                                               |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``ADMIN_LOG_LEVEL``                     | defines error display in the back end                                                         |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SMARTY_LOG_LEVEL``                    | defines error display in *Smarty* - particularly important                                    |
|                                         | for template development                                                                      |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_SHOP``                        | activates the *XHprof* profiler                                                               |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_QUERIES``                     | allows debugging of SQL queries                                                               |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_QUERIES_ECHO``                | provides statistics about the number of queries on each page,                                 |
|                                         | if DEBUG_QUERIES is set                                                                       |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``DEBUG_LEVEL``                         | configures the verbosity of this debug output                                                 |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SMARTY_USE_SUB_DIRS``                 | can activate use of sub-directories for complied Smarty templates                             |
|                                         |                                                                                               |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``JOBQUEUE_LIMIT_M_EXPORTE``            | configures exports- this is important, if Cronjob is used                                     |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``JOBQUEUE_LIMIT_JOBS``            | configures exports- this is important, if Cronjob is used                                          |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SAVE_BOT_SESSION``                    | creates new sessions for every bot call command                                               |
|                                         | (See more: :doc:`botsessions` )                                                               |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``PROFILE_PLUGINS``                     | of BOOL data type. Activates the plug-in profiler, which outputs the runtimes of |br|         |
|                                         | individual hooks and files in the back end under ``admin/profiler.php``                       |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``FILTER_SQL_QUERIES``                  | filters out duplicates during SQL debugging                                                   |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``NICEDB_EXCEPTION_BACKTRACE``          | outputs the entire back trace of a NiceDB exception                                           |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``NICEDB_EXCEPTION_ECHO``               | outputs the exception message string                                                          |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``ADMIN_MIGRATION``                     | allows the display of all database migrations in the back end                                 |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``NICEDB_DEBUG_STMT_LEN``               | establishes the length of the SQL queries that have been output with ``PROFILE_QUERIES`` |br| |
|                                         | (default: 500 characters)                                                                     |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``KEEP_SYNC_FILES``                     | files sent during the last Wawi synchronisation will not be deleted                           |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SHOW_DEBUG_BAR``                      | shows the PHP debug bar in the front end (See more: Section :ref:`label_debugbar` )           |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+
| ``SAFE_MODE``                           | deactivates all plug-ins and any elements derived from plug-ins (Portlets, Widgets, Payment   |
|                                         | etc.)                                                                                         |
+-----------------------------------------+-----------------------------------------------------------------------------------------------+

**Example:**

In the following example, **ALL** possible constants have been listed, in order to illustrate which values can
be accepted.

.. caution::

    This example is not meant to be applied without adjustment to a currently operating online store! |br|
    Please proceed very carefully with these constants, as sometimes a lot of data is output here!

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

    // enables printing of all warnings/infos/errors for the shop front end
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

    // (don't) echo ouput into front end
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

    // filters duplicates during sql-debugging
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
