Additional info, tips, and tricks
=================================

.. |br| raw:: html

   <br />

.. role:: strike
   :class: strike

Since earlier versions of JTL-Shop, there are some ways to simplify the development of plug-ins for the JTL shop.

Constants
---------

As a first step, some constants can be defined in ``[Shop-Root]/includes/config.JTL-Shop.ini.php``.

.. code-block:: php

    //trigger backtrace with SQL exceptions
    define('NICEDB_EXCEPTION_BACKTRACE', true);

    //if necessary, output backtrace via echo in the front end
    define('NICEDB_EXCEPTION_ECHO', true);

    //show all errors, warnings, and notices caused by PHP in the frontend
    define('SHOP_LOG_LEVEL', E_ALL);

    //show all errors, warnings, and notices during Wawi synchronisation
    define('SYNC_LOG_LEVEL', E_ALL);

    //show all errors, warnings, and notices in the back end
    define('ADMIN_LOG_LEVEL', E_ALL);

    //show all errors, warnings, and notices in templates
    define('SMARTY_LOG_LEVEL', E_ALL);

    //recompile Smarty templates on every page request (work-around for Smarty 3.1.27 with OpCache enabled)
    define('SMARTY_FORCE_COMPILE', true);

    //deactivate fallbacks for old templates
    define('TEMPLATE_COMPATIBILITY', false);

Synchronisation with JTL-Wawi
"""""""""""""""""""""""""""""

If the comparison with JTL-Wawi is to be examined more closely for debugging purposes, the deletion of the transferred
XML files can be prevented as follows:

.. code-block:: php

    define('KEEP_SYNC_FILES', true);

Plug-in profiler
""""""""""""""""

In addition, any performance issues with plug-ins can be investigated using the plug-in profiler.

.. code-block:: php

    define('PROFILE_PLUGINS', true);

As soon as a page is called in the front end, a more detailed analysis of the executed
plug-ins and their hooks can be found in the back end under "Troubleshooting -> Plug-in profiler" (as of JTL-Shop 5.x),
in the "*Plug-ins*" tab.

XHProf / Tideways
"""""""""""""""""

If *XHProf* or *Tideways* is installed on the server, then by using the following constant

.. code-block:: php

    define('PROFILE_SHOP', true);

the entire code of the online shop can be analysed.

SQL queries
"""""""""""

All SQL queries executed using the NiceDB class can be stored in the profiler via

.. code-block:: php

    define('PROFILE_QUERIES', true);

. |br|
As of JTL-Shop 5.x, they can be found under "Plug-in profiler", in
the "*SQL*" tab.

Alternatively, they can be displayed in the front end directly via

.. code-block:: php

    define('PROFILE_QUERIES_ECHO', true);

.

In both cases, informational content can be managed via

.. code-block:: php

    //verbosity level. 0-3
    define('DEBUG_LEVEL', 0);

. The higher the value, the more information is stored and output.


.. _label_hinweise_wkchecksum:

Basket checksum
---------------

In an earlier version of JTL-Shop, checksum was introduced to the basket to ensure consistency
("Breaking Change"). |br|
The purpose of running this check, is to prevent changes being made to the purchased items in the
background, which the customer does not see, while the order summary is being displayed to the customer. Such
changes could be, for example, price changes that occur during synchronisation with JTL-Wawi, or sales that take place in parallel. |br|

This kind of change is done by comparing the checksum right before saving the order, and then confirmed
with the following message:

.. code-block:: console

    Your basket has been updated, due to a change in price or inventory.
    Please review items in the basket.

The customer will then be redirected to the basket overview.

.. important::

    A plug-in that directly modifies the basket (to add a special discount, for example), must ensure
    that the checksum is updated after its own changes, so that the order does not end in a
    loop.

The update is done by statically calling the ``refreshChecksum()`` method of the ``basket``
class with the current basket as a parameter.

.. code-block:: php

    Warenkorb::refreshChecksum($_SESSION['Basket']);

Kompatibilität
--------------

Soll ein Plugin sowohl für JTL-Shop 3.x als auch 4.x genutzt werden können, bietet es sich an, die aktuelle Version
z. B. via

.. code-block:: php

    $isShopFour = version_compare(APPLICATION_VERSION, 400, '>=');

zu überprüfen.

Dabei ist zu bedenken, dass nur wenn diese Variable *TRUE* ist, die Klasse ``Shop`` zur Verfügung steht.

Registry
--------

A simple *Registry* for saving arbitrary values within a request can be accessed via the
shop class. |br|
For this purpose, the following functions are available: ``Shop Shop::get(string $key)`` to select, ``bool Shop::has(string $key)`` to verify,
and ``mixed Shop::set(string $key, mixed $value)`` to set.

Example:

.. code-block:: php

    //file1.php
    Shop::set('my-plugin-var01', ['foo' => 'bar']);

    //file2.php, call up later
    $test  = Shop::has('my-plugin-var01'); //TRUE
    $data  = Shop::get('my-plugin-var01'); //array('foo' => 'bar')
    $test2 = Shop::has('NOT-my-plugin-var01'); //FALSE

SQL
---

Earlier versions of JTL-Shop simplify some common calls to the NiceDB class, so that it is no longer necessary to access the global NiceDB object,
and the method names are easier to remember. The parameters remain unchanged.
An overview can be found in the following table.

+-------------------------------------------+--------------------------+
| Shop 3                                    | Shop 4                   |
+===========================================+==========================+
| ``$GLOBALS['NiceDB']->executeQuery()``    | ``Shop::DB()->query()``  |
+-------------------------------------------+--------------------------+
| ``$GLOBALS['NiceDB']->deleteRow()``       | ``Shop::DB()->delete()`` |
+-------------------------------------------+--------------------------+
| ``$GLOBALS['NiceDB']->selectSingleRow()`` | ``Shop::DB()->select()`` |
+-------------------------------------------+--------------------------+
| ``$GLOBALS['NiceDB']->insertRow()``       | ``Shop::DB()->insert()`` |
+-------------------------------------------+--------------------------+
| ``$GLOBALS['NiceDB']->updateRow()``       | ``Shop::DB()->update()`` |
+-------------------------------------------+--------------------------+

In particular, it is strongly advised to use the functions ``NiceDB::insert()``, ``NiceDB::delete()``, and
``NiceDB::update()`` instead of ``NiceDB::executeQuery()``. |br|
Only these variants use *prepared statements*!

As of JTL-Shop version 5.x, and especially in an object context, these methods are no longer accessed directly and statically
, but rather via the *Dependency Injection Container*. You can see an example of this here:

.. code-block:: php
   :emphasize-lines: 7

   class Example
   {
       protected $dbHandler;

       public function __constructor()
       {
           $dbHandler = Shop::Container()->getDB();
           $dbHandler->select(/*...*/);
       }
   }

Selecting individual rows
"""""""""""""""""""""""""

Especially when dealing with user input, it is negligent to integrate raw POST or GET parameters directly
in SQL queries!

**Bad example:**

.. code-block:: php

    $row = $GLOBALS['NiceDB']->executeQuery("SELECT * FROM my_table WHERE id = " . $_POST['id'], 1);

If the column ``id`` is a numeric data type, casting
should at least be carried out, like via ``(int)$_POST['id']``.

However, the preferred way would be to use the ``NiceDB::selectSingleRow()`` method.

The above "negative example" could thus be rewritten as follows:

**Good example:**

.. code-block:: php

    $result = Shop::DB()->select('my_table', 'id', (int)$_POST['id']);

.. hint::

    ``Shop::DB()->query()`` is similar to ``$GLOBALS['NiceDB']->executeQuery($sql, 1)`` |br|
    or ``Shop::DB()->query($sql, 1)`` with the second parameter set to "1", stands for a "single fetched object".

    However, only simple *WHERE* conditions with *AND* join conditions are possible here.

Inserting rows
""""""""""""""

Similar to the selecting procedure, here is an example for *inserting*:

**Insecure variant:**

.. code-block:: php

    $i = $GLOBALS['NiceDB']->executeQuery("
        INSERT INTO my_table
            ('id', 'text', 'foo')
            VALUES (" . $_POST['id'] . ", '" . $_POST['text'] . "', '" . $_POST['foo'] . "')", 3
    );

**Improved variant:**

.. code-block:: php

    $obj       = new stdClass();
    $obj->id   = (int) $_POST['id'];
    $obj->text = $_POST['text'];
    $obj->foo  = $_POST['foo'];
    $i = Shop::DB()->insert('my_table', $obj);

Deleting rows
"""""""""""""

**Insecure variant:**

.. code-block:: php

    $GLOBALS['NiceDB']->executeQuery("
        DELETE FROM my_table
            WHERE id = " . $_POST['id'], 3
    );

**Improved variant:**

.. code-block:: php

    Shop::DB()->delete('my_table', 'id', (int) $_POST['id']);

In the case of extended WHERE clauses with *AND* condition, two arrays with all keys and all values
each can be submitted:

.. code-block:: php

    Shop::DB()->delete('my_table', array('id', 'foo'), array(1, 'bar'));
    // --> DELETE FROM my_table WHERE id = 1 AND 'foo' = 'bar'

Updating rows
"""""""""""""

**Insecure variant:**

.. code-block:: php

    $GLOBALS['NiceDB']->executeQuery("
        UPDATE my_table
            SET id = " . $_POST['new_id'] . ",
                foo = '" . $_POST['foo'] . "',
                bar = 'test'
            WHERE id = " . $_POST['id'], 3
    );

**Improved variant:**

.. code-block:: php

    $obj      = new stdClass();
    $obj->id  = (int) $_POST['new_id'];
    $obj->foo = $_POST['foo'];
    $obj->bar = 'test';
    Shop::DB()->update('my_table', 'id', (int) $_POST['id'], $obj);

.. important::

    If it is not possible to use the described methods, all potentially dangerous values should be hidden beforehand
    via ``Shop::DB()->escape()``, or converted in the case of numerals.

Changes as of JTL-Shop 3.x
--------------------------

A quick overview of the changes:

* ``smarty->assign()`` can now be *chained*:

.. code-block:: php

    $smarty->assign('var_1', 1)
           ->assign('var_2', 27)
           ->assign('var_3', 'foo');

* The ``shop`` class forms a central entry point for frequently used functions:

.. code-block:: php

    Shop::Cache()->flushAll(); //Flush object cache

    $arr = Shop::DB()->query($sql, 2); //Alias for $GLOBALS['DB']->executeQuery()

    $translated = Shop::Lang()->get('newscommentAdd', 'messages'); //Alias for $GLOBALS['Language']->gibWert()

    $shopURL = Shop::getURL(); //Instead of URL_SHOP, checks SSL

    $conf = Shop::getSettings(array(CONF_GLOBAL, CONF_NEWS)); //Alias for $GLOBALS['Settings']...

    Shop::dbg($someVariable, false, 'Inhalt der Variablen:'); //Quick debugging

    $smarty = Shop::Smarty(); //Alias for global Smarty object

    Shop::set('my_key', 42); //Registry setter

    $value = Shop::get('my_key'); //Registry getter - 42

    $hasValue = Shop::has('some_other_key'); //Registry check - false
