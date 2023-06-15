Cache
=====

.. |br| raw:: html

   <br />

Since JTL-Shop 4 an object cache is provided by the class ``JTLCache``, or ``JTL\Cache\JTLCache``, and the
corresponding back end classes in ``<Shop-Root>/classes/CachingMethods/``, or ``<Shop-Root>/includes/src/Cache/Methods/``, which can
also be used in plug-ins.

The configuration is done in the back end via the menu items "*System -> Maintenance -> Cache*" (up to JTL-Shop 4.x) and
"*System -> Cache*" (as of JTL-Shop 5.x).

JTL-Shop supports the following caching methods by default:

* Redis
* Memcache(d)
* APC
* Files
* Files (extended)
* XCache

In addition, cache entries are grouped via groups and tags (as of JTL-Shop 4).

Cache group tags
----------------

The available default groups are:

+--------------------------------+--------------------------------+
| Group                          | Description                    |
+================================+================================+
| ``CACHING_GROUP_CATEGORY``     | Categories                     |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_LANGUAGE``     | Language values                |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_TEMPLATE``     | Templates and template options |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_OPTION``       | General options                |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_PLUGIN``       | Plug-ins and options           |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_CORE``         | Important core data            |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_OBJECT``       | General objects                |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_BOX``          | Boxes                          |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_NEWS``         | News items/Archive             |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_ATTRIBUTE``    | Attributes                     |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_MANUFACTURER`` | Manufacturer data              |
+--------------------------------+--------------------------------+

Why tags?
---------

When a given piece of data is stored under a unique ID, it is difficult to then invalidate this
entry again.

Either the exact ID must be known or entries have to be deleted all at once.
The latter would lead to frequent rebuilding of the cache. On the other hand, cache IDs must be as
exact as possible. For example, if a product object is to be cached, its data will depend on
a number of factors such as current language, customer group, and so on.

If, for example, product data has changed due to synchronisation with JTL-Wawi, this entry must now be
invalidated. This must be done either by deleting all cache IDs or by deleting all allowed values individually.
This means that cache IDs would have to be generated for all languages and all customer groups, and then all of them would have to be deleted.

This is easier done with tags:

Each product is tagged with at least two tags in the cache, in addition to its unique ID:
``CACHING_GROUP_ARTICLE`` and ``CACHING_GROUP_ARTICLE_$kArtikel``. |br|
If item data for the product with ``$kArtikel`` "*12345*" has changed, the
cache tag ``CACHING_GROUP_ARTICLE_12345`` will be cleared and all other data will remain.

This is exactly what happens automatically, for example in dbeS, when product data is received there. |br|
The procedure with categories is the same.

The procedure for saving options in the back end is similar: |br|
As soon as the user clicks on “*Save*", all cached entries with the ``CACHING_GROUP_OPTION`` tag will
be deleted. By saving plug-in options, the ``CACHING_GROUP_PLUGIN_$kPlugin`` group
is automatically deleted.

Another advantage of tags is that the user has the option to exclude individual areas of the JTL-Shop from
caching. |br|
All default tags can therefore be deactivated individually via the back end, so that write attempts in these groups
are no longer possible and read operations always return *FALSE*.

General approach to reading/saving

    1. Retrieve class instance via ``Shop::Cache()``
    2. Generate cache ID
    3. Search for the respective entry in the cache with
       ``mixed|bool JTLCache::get(string $cacheID [,callable $callback = null, mixed $customData = null])``
    4. Upon *hit*, return directly
    5. Upon *miss*, calculate data
    6. Save data in the cache via
       ``bool JTLCache::set(string $cacheID, mixed $content [, array $tags = null, int $expiration = null])`` and provide
       with tags

**Example:**

.. code-block:: php

    <?php
    class testClass
    {
        private $cache = null;

        private $myCacheTag = 'myOwnTag';

        public function __construct () {
            $this->cache = Shop::Cache();
        }

        public function test () {
            $cacheID = 'tct_' . Shop::$kSprache;
            if (($myObject = $this->cache->get($cacheID)) === false) {
                //not found in cache
                $myObject = $this->doSomethingThatTakesSomeTime();
                $this->cache->set($cacheID, $myObject, [CACHING_GROUP_OPTION, $this->myCacheTag]);
            }

            return $myObject;
        }
    }

The fourth parameter of the ``set()`` function can also be used to set a custom cache validity in
seconds. By default, the value configured in the back end is applied.

Short form
""""""""""

A separate cache instance is not always practical. The short form can also suffice in this case:

.. code-block:: php

    $myObject = Shop::Cache()->get($cacheID);
    Shop::Cache()->set($cacheID, $myObject, $tags);
    Shop::Cache()->delete($cacheID);

You can find a list of all available methods via the ``string|null JTLCache::map(string $method)`` function.

General invalidation
--------------------

.. important::

    If affected data changes, for example when synchronizing with JTL-Wawi or through user interaction, the
    caches (represented by the *CacheID*) must be flushed.

This can be done via ``$cache->flush($cacheID)``, or the short form ``Shop::Cache()->delete(string $cacheID)``,
to delete the ID or via ``$cache->flushTags(array $tags)``, or ``Shop::Cache()->flushTags(array $tags)``, to
delete entire tags.

**Example:**

.. code-block:: php

    <?php
    class testClass
    {
        // [...]

        /**
         * return int - the number of deleted IDs
         */
        public function invalidate () {
            return $this->cache->flushTags([$this->myCacheTag]);
        }
    }

Generating IDs
--------------

*Cache IDs* should be as unique as possible, but at the same time not too complex in their computing,
so as not to compromise the cache speed.

In general, all factors that influence the calculation of a value should be included in the ID. |br|
With JTL-Shop this often concerns the current language (``$_SESSION['kSprache']``, or ``Shop::$kSprache``), the
customer group (``$_SESSION['Kunde']->kKundengruppe``), or the currency (``$_SESSION['Waehrung']->kWaehrung``).

The ``JTLCache::getBaseID()`` function attempts to consider the most common influencing factors to generate a base ID
that can be used as part of the CacheID. |br|
Its signature looks as follows:

.. code-block:: php

    string JTLCache::getBaseID([bool $hash = false, bool $customerID = false, bool $customerGroup = true, bool $currencyID = true, bool $sslStatus = true])

The first parameter specifies whether an *md5 hash* should be generated. The other parameters specify
which factors are to be considered.

It would be practical, for example, to combine this *base ID* with an abbreviation of the function name
such as ``$cacheID = 'mft_' . Shop::Cache()->getBaseID()`` if the respective line
is in a function called "*myFunctionTest*".

Cache IDs and tags in plug-ins
------------------------------

The ``$oPlugin`` objects that can be used in hook files have the automatically generated ``pluginCacheID`
and ``pluginCacheGroup`` attributes. These can be used to avoid having to calculate IDs independently. |br|
Also, these are automatically invalidated when saving options in the plug-in back end.

Boolean values in the cache
---------------------------

If boolean values are also to be stored in the cache, a cross-check of the get result
against ``JTLCache::RES_SUCCESS`` using the ``JTLCache::getResultCode()`` function is necessary, since ``JTLCache::get()`` returns *FALSE* in
the event of an error. Thus, it is not possible to distinguish an explicitly stored boolean value from a
failed reading operation.

**Example:**

.. code-block:: php

    $result = Shop::Cache()->get($cacheID);
    if (Shop::Cache()->getResultCode() === JTLCache::RES_SUCCESS) {
        //ok
    } else {
        //Cache miss - JTLCache::RES_FAIL
    }

Setting/reading multiple values
-------------------------------

Multiple values can be read simultaneously via ``JTLCache::getMulti(array $cacheIDs)`` and set
via ``JTLCache::setMulti(array $keyValue, array|null $tags[, int|null $expiration])``.

**Example:**

.. code-block:: php

    $foo = [
        'key1' => 'value1',
        'key2' => 222
    ];
    $write = $cache->setMulti($foo, ['tag1', 'tag2'], 60);
    Shop::dbg($write);
    // output: TRUE

    // request 3 keys while just 2 are set
    $keys = ['key1', 'key2', 'key3'];
    $read = $cache->getMulti($keys);
    Shop::dbg($read);
    // output:
    //
    // array(3) {
    //     [" key1 "] => string(6) "value1"
    //     [" key2 "] => int (222)
    //     [" key3 "] => bool(false)
    // }

Hooking
-------

Caching also has the advantage that certain hooks do not have to be executed more often than necessary, as is the case with
the ``HOOK_ARTIKEL_CLASS_FUELLEARTIKEL`` (110) hook. |br|
To allow plug-ins to also add their own cache tags
it is advisable to pass the intended tags to the hook as well.

**Example:**

.. code-block:: php

    $cacheTags = [CACHING_GROUP_ARTICLE . '_' . $this->kArtikel, CACHING_GROUP_ARTICLE];
    executeHook(HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, [
        'oArtikel'  => &$this,
        'cacheTags' => &$cacheTags,
        'cached'    => false
        ]
    );
    $cache->set($key, $this, $cacheTags);

Due to multiple requests from developers, *hook 110* is now executed upon a cache hit. |br|
In this case, the passed parameter ``cached`` is set to *TRUE*. If you program a plug-in which
modifies properties of an item once, please make sure to execute complex logic only
if the parameter is *FALSE*. |br|
Afterwards, your changes are automatically saved in the cache and do **not** need to be executed
again.

This way, a plug-in can add its own tag and react to changes
to the plug-in options, for instance, and flush the affected caches 
(see `jtl_example_plugin <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_test>`_).

Note the following order:

    1. Define default cache tags
    2. Execute hook with data and tags
    3. Save data.

This is the only way that the data that may have been modified by a plug-in can be both stored in the cache and
invalidated by it.

Which caching method?
---------------------

In general, all implemented caching methods are functional, but due to their particularities, they can only be recommended for all
scenarios to a certain extent.

Files cache
"""""""""""

The *file* cache is the slowest and most inflexible cache method for lots of files and also has problems with simultaneous access.
Therefore, it should only be used as a last resort. |br|
However, it is always available and can be significantly accelerated by relocating the cache folder to a RAM-based
file system.

File (extended) cache
"""""""""""""""""""""

The *(extended) files* method, included since JTL-Shop 4.05, tries to work around these drawbacks by way of
`Symlinks <https://de.wikipedia.org/wiki/Symbolische_Verkn%C3%BCpfung>`_. |br|
Here, subfolders are created in the ``templates_c/filecache/`` folder for each tag that contains symlinks to the
individual cache entries. This allows better concurrency when writing
new entries. |br|
However, under still unclear circumstances, faulty links may be created so that the
cache folder can no longer be cleared. This is currently (as of February 2020) being looked into.

APC cache
"""""""""

*APC* is the fastest variant, however it has
scaling issues in a practical test with high load and lots of entries. In the range of about 3-4 GB of data, it also becomes highly fragmented and performance
can drop.

Redis cache
"""""""""""

*Redis* is most suitable variant for large amounts of data. |br|
In addition to operating fast in the range of several gigabytes, it can also be used
as a session handler <https://github.com/phpredis/phpredis#php-session-handler>`_.

Memcached cache
"""""""""""""""

Essentially the same is true for *memcached* as for *Redis*, but the former has been tested less.

XCache cache
""""""""""""

*XCache* has not been tested yet and is only implemented for the sake of completeness.

