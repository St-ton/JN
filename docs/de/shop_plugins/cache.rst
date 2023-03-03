Cache
=====

.. |br| raw:: html

   <br />

Über die Klasse ``JTLCache`` bzw. ``JTL\Cache\JTLCache`` sowie die zugehörigen Backend-Klassen
in ``<Shop-Root>/classes/CachingMethods/`` bzw. ``<Shop-Root>/includes/src/Cache/Methods/`` wird seit JTL-Shop 4 ein
Objektcache bereitgestellt, welcher auch in Plugins genutzt werden kann.

Die Konfiguration erfolgt im Backend über den Menüpunkt "*System -> Wartung -> Cache*" (bis JTL-Shop 4.x) und
"*System -> Cache*" (ab JTL-Shop 5.x).

Standardmäßig unterstützt JTL-Shop die folgenden Caching-Methoden:

* Redis
* Memcache(d)
* APC
* Dateien
* Dateien (erweitert)
* XCache

Darüber hinaus erfolgt eine Gruppierung von Cache-Einträgen über Gruppen und Tags (seit JTL-Shop 4).

Cache-Group-Tags
----------------

Die zur Verfügung stehenden Standard-Gruppen lauten:

+--------------------------------+--------------------------------+
| Gruppe                         | Beschreibung                   |
+================================+================================+
| ``CACHING_GROUP_CATEGORY``     | Kategorien                     |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_LANGUAGE``     | Sprachwerte                    |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_TEMPLATE``     | Templates und Templateoptionen |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_OPTION``       | Allgemeine Optionen            |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_PLUGIN``       | Plugins und Optionen           |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_CORE``         | Wichtige Core-Daten            |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_OBJECT``       | Allgemeine Objekte             |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_BOX``          | Boxen                          |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_NEWS``         | Newseinträge/Archiv            |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_ATTRIBUTE``    | Attribute                      |
+--------------------------------+--------------------------------+
| ``CACHING_GROUP_MANUFACTURER`` | Herstellerdaten                |
+--------------------------------+--------------------------------+

Warum Tags?
-----------

Wenn ein beliebiges Datum unter einer eindeutigen ID gespeichert wird, ist es schwierig, diesen Eintrag wieder zu
invalidieren.

Entweder müsste dazu die genaue ID bekannt sein oder es müssten sämtliche Einträge auf einmal gelöscht werden.
Letzteres würde zu einem sehr häufigen Neuaufbau des Caches führen. Andererseits müssen Cache-IDs aber so genau wie
möglich sein. Falls beispielsweise eine Produktobjekt im Cache gespeichert werden soll, hängen dessen Daten von
verschiedenen Faktoren wie aktueller Sprache, Kundengruppe etc. ab.

Haben sich z. B. durch die Synchronisation mit JTL-Wawi Produktdaten geändert, muss dieser Eintrag nun aber invalidiert
werden. Entweder indem alle Cache-IDs gelöscht werden, oder indem alle zulässigen Werte einzeln gelöscht werden.
So müssten also für alle Sprachen und alle Kundengruppen Cache-IDs generiert und anschließend alle gelöscht werden.

Einfacher ist dies mit Tags:

Jedes Produkt wird im Cache zusätzlich zur eindeutigen ID mit (mindestens) zwei Tags versehen:
``CACHING_GROUP_ARTICLE`` und ``CACHING_GROUP_ARTICLE_$kArtikel``. |br|
Falls sich nun Artikeldaten für das Produkt mit ``$kArtikel`` "*12345*" geändert haben, wird der
Cache-Tag ``CACHING_GROUP_ARTICLE_12345`` geleert - alle anderen Daten bleiben im Cache erhalten.

Genau dies geschieht automatisch, beispielsweise in dbeS, wenn dort Produktdaten ankommen. |br|
Das Verfahren mit Kategorien ist analog.

Ähnlich ist es beim Speichern von Optionen im Backend: |br|
Sobald der Nutzer dort auf "*Speichern*" klickt, werden alle mit dem Cache-Tag ``CACHING_GROUP_OPTION`` versehenen
Einträge gelöscht. Das Speichern von Plugin-Optionen invalidiert automatisch die
Gruppe ``CACHING_GROUP_PLUGIN_$kPlugin``.

Ein weiterer Vorteil der Tags ist die Möglichkeit, dass der Nutzer einzelne Bereiche von JTL-Shop gezielt vom
Caching ausnehmen kann. |br|
Über das Backend sind daher alle Standard-Tags jeweils einzeln deaktivierbar, sodass Schreibversuche in diesen Gruppen
nicht mehr möglich sind und Leseoperationen stets *FALSE* zurückgeben.

Generelles Vorgehen beim Lesen/Speichern

    1. Klasseninstanz holen via ``Shop::Cache()``
    2. CacheID generieren
    3. mit ``mixed|bool JTLCache::get(string $cacheID [,callable $callback = null, mixed $customData = null])``
       im Cache nach entsprechendem Eintrag suchen
    4. bei *Hit* direkt zurückgeben
    5. bei *Miss* Daten berechnen
    6. Daten im Cache über
       ``bool JTLCache::set(string $cacheID, mixed $content [, array $tags = null, int $expiration = null])`` speichern
       und dabei mit Tags versehen

**Beispiel:**

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

Über den vierten Parameter der ``set()``-Funktion kann außerdem eine eigene Cache-Gültigkeit in Sekunden
gesetzt werden. Standardmäßig wird der im Backend konfigurierte Wert genommen.

Kurzform
""""""""

Eine eigene Cache-Instanz ist nicht immer sinnvoll. Hier kann auch die Kurzform ausreichen:

.. code-block:: php

    $myObject = Shop::Cache()->get($cacheID);
    Shop::Cache()->set($cacheID, $myObject, $tags);
    Shop::Cache()->delete($cacheID);

Eine Liste aller verfügbarer Methoden ist mittels der Funktion ``string|null JTLCache::map(string $method)`` zu finden.

Generelles Invalidieren
-----------------------

.. important::

    Falls sich betroffene Daten ändern, z. B. beim Abgleich mit JTL-Wawi oder durch Nutzerinteraktion, müssen
    die Caches (repräsentiert durch die *CacheID*) gelöscht werden.

Hierzu kann via ``$cache->flush($cacheID)``, bzw. der Kurzform ``Shop::Cache()->delete(string $cacheID)``,
die ID gelöscht werden oder via ``$cache->flushTags(array $tags)`` bzw. ``Shop::Cache()->flushTags(array $tags)``
ganze Tags gelöscht werden.

**Beispiel:**

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

Generierung von IDs
-------------------

*Cache-IDs* sollten möglichst einzigartig sein, gleichzeitig aber auch in ihrer Berechnung nicht zu komplex,
um den Geschwindigkeitsvorteil des Caches nicht wieder zu verspielen.

Generell sollten alle Faktoren, die die Berechnung eines Wertes beeinflussen, in die ID mit einbezogen werden. |br|
Dies betrifft bei JTL-Shop häufig die aktuelle Sprache (``$_SESSION['kSprache']`` bzw. ``Shop::$kSprache``), die
Kundengruppe (``$_SESSION['Kunde']->kKundengruppe``) oder die Währung (``$_SESSION['Waehrung']->kWaehrung``).

Die Funktion ``JTLCache::getBaseID()`` versucht, die gängigsten Einflussfaktoren zu bedenken und so eine Basis-ID
zu generieren, die als Teil der CacheID verwendet werden kann. |br|
Ihre Signatur sieht wie folgt aus:

.. code-block:: php

    string JTLCache::getBaseID([bool $hash = false, bool $customerID = false, bool $customerGroup = true, bool $currencyID = true, bool $sslStatus = true])

Der erste Parameter gibt dabei an, ob ein *md5-Hash* generiert werden soll. Die weiteren Parameter geben an,
welche Faktoren bedachte werden sollen.

Zweckmäßig wäre es beispielsweise, diese *Basis-ID* mit einer Abkürzung des Funktionsnamens zu kombinieren,
wie beispielsweise ``$cacheID = 'mft_' . Shop::Cache()->getBaseID()``, wenn die entsprechende Zeile
in einer Funktion namens "*myFunctionTest*" steht.

CacheIDs und Tags in Plugins
----------------------------

Die in Hook-Dateien verwendbaren ``$oPlugin``-Objekte haben die automatisch generierten Attribute ``pluginCacheID``
sowie ``pluginCacheGroup``. Diese können verwendet werden, um nicht selbständig IDs berechnen zu müssen. |br|
Außerdem werden diese beim Speichern von Optionen im Plugin-Backend automatisch invalidiert.

Boolsche Werte im Cache
-----------------------

Falls auch boolsche Werte im Cache gespeichert werden sollen, ist eine Prüfung des get-Ergebnisses
gegen ``JTLCache::RES_SUCCESS`` mithilfe der Funktion ``JTLCache::getResultCode()`` notwendig, da ``JTLCache::get()``
im Fehlerfall *FALSE* zurückgibt. So ist es nicht möglich, einen explizit gespeicherten boolschen Wert von einem
fehlgeschlagenen Lesevorgang zu unterscheiden.

**Beispiel:**

.. code-block:: php

    $result = Shop::Cache()->get($cacheID);
    if (Shop::Cache()->getResultCode() === JTLCache::RES_SUCCESS) {
        //ok
    } else {
        //Cache miss - JTLCache::RES_FAIL
    }

Mehrere Werte setzen/lesen
--------------------------

Über ``JTLCache::getMulti(array $cacheIDs)`` können mehrere Werte gleichzeitig ausgelesen
und über ``JTLCache::setMulti(array $keyValue, array|null $tags[, int|null $expiration])`` gesetzt werden.

**Beispiel:**

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

Caching hat auch den Vorteil, dass gewisse Hooks nicht häufiger ausgeführt werden müssen als nötig - wie z. B.
Hook ``HOOK_ARTIKEL_CLASS_FUELLEARTIKEL`` (110). |br|
Um Plugins die Möglichkeit zu geben, auch eigene Cache-Tags
hinzufügen zu lassen, ist es angebracht, die vorgesehenen Tags ebenfalls an den Hook zu übergeben.

**Beispiel:**

.. code-block:: php

    $cacheTags = [CACHING_GROUP_ARTICLE . '_' . $this->kArtikel, CACHING_GROUP_ARTICLE];
    executeHook(HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, [
        'oArtikel'  => &$this,
        'cacheTags' => &$cacheTags,
        'cached'    => false
        ]
    );
    $cache->set($key, $this, $cacheTags);

Aufgrund vielfacher Wünsche von Entwicklern wird der *Hook 110* nun bei einem Cache-Hit ausgeführt. |br|
Der übergebene Parameter ``cached`` ist in diesem Fall auf *TRUE* gesetzt. Falls Sie ein Plugin programmieren, welches
einmalig Eigenschaften eines Artikels modifiziert, achten Sie bitte darauf, komplexe Logik nur auszuführen,
wenn der Parameter *FALSE* ist. |br|
Anschließend werden Ihre Änderungen automatisch im Cache gespeichert und müssen **nicht** erneut
durchgeführt werden.

Auf diese Weise kann ein Plugin einen eigenen Tag hinzufügen und beispielsweise bei Änderungen
an den Plugin-Optionen reagieren und die betroffenen Caches leeren
(vgl. `jtl_example_plugin <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_test>`_).

Beachten Sie dabei die Reigenfolge:

    1. Standard-Cache-Tags definieren
    2. Hook mit Daten und Tags ausführen
    3. Daten speichern.

Nur so können die durch ein Plugin evtl. modifizierten Daten auch im Cache gespeichert und von diesem
invalidiert werden.

Welche Caching-Methode?
-----------------------

Generell sind alle implementierten Caching-Methoden funktional, aufgrund ihrer Eigenheiten aber nur bedingt für alle
Szenarien zu empfehlen.

Dateien-Cache
"""""""""""""

Der *Dateien*-Cache ist im Falle von vielen Dateien die langsamste und unflexibelste Cache-Methode, hat außerdem Probleme bei gleichzeitigen Zugriffen
und sollte daher nur im Notfall genutzt werden. |br|
Allerdings ist er immer verfügbar und kann durch Auslagerung des Cache-Ordners auf ein RAM-basiertes Dateisystem
deutlich beschleunigt werden.

Dateien(erweitert)-Cache
""""""""""""""""""""""""

Die seit JTL-Shop 4.05 enthaltene Methode *Dateien (erweitert)* versucht, diese Nachteile durch
`Symlinks <https://de.wikipedia.org/wiki/Symbolische_Verkn%C3%BCpfung>`_ zu umgehen. |br|
Hierbei werden im Ordner ``templates_c/filecache/`` für jeden Tag Unterordner angelegt, die Symlinks zu den
einzelnen Cache-Einträgen enthalten. Hierdurch kann eine bessere Parallelität beim Schreiben von neuen Einträgen
erreicht werden. |br|
Unter bislang ungeklärten Umständen kann es jedoch vorkommen, dass fehlerhafte Links erstellt werden, sodass der
Cache-Ordner nicht mehr geleert werden kann. Dies wird aktuell (Stand: Februar 2020) noch untersucht.

APC-Cache
"""""""""

*APC* ist die schnellste Variante, hat im Praxistest bei hoher Belastung und vielen Einträgen aber
Skalierungsprobleme. Zumindest im Bereich von ca. 3-4 GB Daten wird er außerdem stark fragmentiert und die Leistung
kann einbrechen.

Redis-Cache
"""""""""""

Die für große Datenmengen am besten geeignete Variante ist *Redis*. |br|
Auch im Bereich von mehreren Gigabyte arbeitet sie schnell und kann außerdem
auch `als Session-Handler genutzt werden <https://github.com/phpredis/phpredis#php-session-handler>`_.

Memcache(d)-Cache
"""""""""""""""""

Für *memcache(d)* gilt prinzipiell dasselbe wie für *Redis*, allerdings ist es weniger getestet.

XCache-Cache
""""""""""""

*XCache* wurde bislang noch nicht getestet und ist nur der Vollständigkeit halber implementiert.

