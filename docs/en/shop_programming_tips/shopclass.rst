The shop class
==============

.. |br| raw:: html

   <br />

In later versions of JTL-Shop, the ``shop`` class is of great importance. |br|
It serves primarily as a central registry for, previously exclusively, global variables such as *NiceDB* or
*Smarty*, but it is also used to create and output instances for the new object cache.

In earlier JTL-Shop versions, constructs like in the example below, were necessary to carry out SQL queries:

.. code-block:: php

    // outdated!
    //
    $Article = $GLOBALS['DB']->executeQuery('SELECT * FROM tartikel WHERE kArtikel = 2344', 1);


Templates were rendered as follows: |br|

.. code-block:: php

    // outdated!
    //
    global $smarty;
    $smarty->assign('myvar', 123);
    $smarty->assign('myothervar', 'foobar');
    $smarty->display('mytemplate.tpl');

Now in JTL-Shop 5.x, class instances of *NiceDB* and *Smarty* can and should be obtained via the ``shop`` class
.

Für JTL-Shop 4.x gilt folgende Vorgehensweise als bevorzugt:

.. code-block:: php

    $product = Shop::DB()->query('SELECT * FROM tartikel WHERE kArtikel = 2344', 1);

As of JTL-Shop 5.0, the following method is preferred:

.. code-block:: php

   $product = Shop::Container()->getDB()->queryPrepared(
       'SELECT * FROM tartikel WHERE kArtikel = :artID',
       ['artID' => $articleID],
       ReturnType::SINGLE_OBJECT
   );

As of JTL-Shop 5.0, the following is the preferential method for Smarty:

.. code-block:: php

    Shop::Smarty()
        ->assign('myvar', 123)
        ->assign('myothervar', 'foobar')
        ->display('mytemplate.tpl');

The ``JTLSmarty::assign(string $tpl_var, mixed $value)`` method has now been made "*chainable*" to
increase code clarity.  |br|
Additionally, the function names of the *NiceDB* class have been simplified somewhat, and also made *statically* available
via mapping (compare function ``NiceDB::map(string $method)``).

Language functions
------------------

Language functions should also be used via the *shop* class.

The following method was common in previous versions of JTL-Shop:

.. code-block:: php

    $GLOBALS['Sprache']->gibWert('basketAllAdded', 'messages');  // outdated! (standard in Shop 3.x)

Because of the options provided by the *shop* class, it now looks like:

.. code-block:: php

    Shop::Lang()->get('basketAllAdded', 'messages');

Caching
-------

Use of the cache is performed similarly to that of the language functions, and is further explained in section ":doc:`Cache </shop_plugins/cache>`"
.

Online shop URL
---------------

To retrieve the URL of the online shop, the ``Shop::getURL([bool $bForceSSL = false]) : string` method
was introduced.

.. attention::

    We strongly recommend using this variant, instead of the outdated ``URL_SHOP`` constant,|br|
    because ``Shop::getURL()`` also takes into account any potential *SSL* configuration. |br|

The output is given always **without the final slash**.

GET parameter
-------------

Additionally, the manipulation of *GET parameters* and the parsing of *SEO URLs* has been moved to the *shop* class
. |br|
The central entry points here are the ``Shop::run()`` and ``Shop::getParameters()`` functions, which are executed
by all PHP files directly called up in the shop root.

Debugging
---------

The ``Shop::dbg(mixed $content[, bool $die, string $prepend]) : void`` function allows for quick and dirty *debugging*.

As the first parameter, it will receive any content for output. If the second parameter is set to
``true``, further execution of the code can be halted. The third parameter may contain a text
, which will appear as an explanation before the debug output. |br|
Essentially, this corresponds to a ``var_dump()`` embedded with ``<pre>`` tags and followed by ``die()``, if necessary.

