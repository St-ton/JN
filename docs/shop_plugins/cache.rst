Cache
=====

Über die Klasse *JTLCache* in ``<Shop-Root>/classes/class.JTL-Shop.JTLCache.php`` sowie die zugehörigen Backends in ``<Shop-Root>/classes/CachingMethods`` wird seit Shop 4 ein auch für Plugins nutzbarer Objektcache bereitgestellt.

Die Konfiguration erfolgt im Backend über den Menüpunkt *System->Cache*.

Standardmäßig unterstützt der Shop die folgenden Methoden:

* Redis
* Memcache(d)
* APC
* Dateien
* Dateien (erweitert)
* XCache

Darüber hinaus erfolgt eine Gruppierung von Cache-Einträgen im Gegensatz zu Shop 3.18+ über Gruppen und Tags.
Die Standard-Gruppen lauten

+----------------------------+--------------------------------+
| Gruppe                     | Beschreibung                   |
+============================+================================+
| CACHING_GROUP_CATEGORY     | Kategorien                     |
+----------------------------+--------------------------------+
| CACHING_GROUP_LANGUAGE     | Sprachwerte                    |
+----------------------------+--------------------------------+
| CACHING_GROUP_TEMPLATE     | Templates und Templateoptionen |
+----------------------------+--------------------------------+
| CACHING_GROUP_OPTION       | allgemeine Optionen            |
+----------------------------+--------------------------------+
| CACHING_GROUP_PLUGIN       | Plugins und Optionen           |
+----------------------------+--------------------------------+
| CACHING_GROUP_CORE         | wichtige Core-Daten            |
+----------------------------+--------------------------------+
| CACHING_GROUP_OBJECT       | allgemeine Objekte             |
+----------------------------+--------------------------------+
| CACHING_GROUP_BOX          | Boxen                          |
+----------------------------+--------------------------------+
| CACHING_GROUP_NEWS         | Newseinträge/Archiv            |
+----------------------------+--------------------------------+
| CACHING_GROUP_ATTRIBUTE    | Attribute                      |
+----------------------------+--------------------------------+
| CACHING_GROUP_MANUFACTURER | Herstellerdaten                |
+----------------------------+--------------------------------+


Warum Tags?
-----------

Wenn ein beliebiges Datum unter einer eindeutigen ID gespeichert wird, ist es schwierig, diesen Eintrag wieder zu invalidieren.

Entweder müsste dazu die genaue ID bekannt sein oder sämtliche Einträge auf einmal gelöscht werden. Letzteres würde zu einem sehr häufigen Neuaufbau des Caches führen.
Andererseit müssen Cache-IDs aber so genau wie möglich sein. Falls beispielsweise eine Produktobjekt im Cache gespeichert werden soll, hängen dessen Daten von verschiedenen Faktoren wie aktueller Sprache, Kundengruppe etc. ab.

Haben sich z.B. durch Synchronisation mit der Wawi Produktdaten geändert, muss dieser Eintrag nun aber invalidiert werden. Entweder, indem alle Cache-IDs gelöscht werden, oder indem alle zulässigen Werte einzeln gelöscht werden.
So müsste man also für alle Sprachen und alle Kundengruppen Cache-IDs generieren und anschließend alle löschen.

Einfacher ist dies mit Tags: Jedes Produkt wird im Cache zusätzlich zur eindeutigen ID mit (mindestens) zwei Tags versehen: CACHING_GROUP_ARTICLE und CACHING_GROUP_ARTICLE_$kArtikel.
Falls sich nun Artikeldaten für das Produkt mit $kArtikel 12345 geändert haben, wird der Cache-Tag *CACHING_GROUP_ARTICLE_12345* geleert - alle anderen Daten bleiben im Cache erhalten.

Genau dies geschieht automatisch beispielsweise in dbeS, wenn dort Produtkdaten ankommen. Das Verfahren mit Kategorien ist analog.

Ähnlich ist es beim Speichern von Optionen im Backend: sobald der Nutzer dort auf "Speichern" klickt, werden alle mit dem Cache-Tag CACHING_GROUP_OPTION versehenen Einträge gelöscht.
Und das Speichern von Plugin-Optionen invalidiert automatisch die Gruppe *CACHING_GROUP_PLUGIN_$kPlugin*.

Weiterer Vorteil der Tags ist, dass der Nutzer einzelne Bereiche des Shopsystems gezielt vom Caching ausnehmen kann.
Über das Backend sind daher alle Standard-Tags jeweils einzeln deaktivierbar, sodass Schreibversuche in diesen Gruppen nicht möglich sind und Leseoperationen stets *FALSE* zurückgeben.

Generelles Vorgehen beim Lesen/Speichern

    1. Klasseninstanz holen via Shop::Cache()
    2. CacheID generieren
    3. mit ``mixed|bool JTLCache::get(string $cacheID [,callable $callback = null, mixed $customData = null])`` im Cache nach entsprechendem Eintrag suchen
    4. bei Hit direkt zurückgeben
    5. bei Miss Daten berechnen und
    6. Daten im Cache über ``bool JTLCache::set(string $cacheID, mixed $content [, array $tags = null, int $expiration = null])`` speichern und dabei mit Tags versehen


Beispiel:

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

Über den vierten Parameter der set()-Funktion kann außerdem eine eigene Cache-Gültigkeit in Sekunden gesetzt werden. Standardmäßig wird der im Backend konfigurierte Wert genommen.

Kurzform
~~~~~~~~

Eine eigene Cache-Instanz ist nicht immer sinnvoll, dann tut es auch die Kurzform:

.. code-block:: php

    $myObject = Shop::Cache()->get($cacheID);
    Shop::Cache()->set($cacheID, $myObject, $tags);
    Shop::Cache()->delete($cacheID);

Eine Liste aller verfübarer Methoden ist in der Funktion ``string|null JTLCache::map(string $method)`` zu finden.

Generelles Vorgehen beim Invalidieren
-------------------------------------

Falls sich betroffene Daten ändern -- bei Wawi-Abgleich oder durch Nutzerinteraktion -- müssen die CacheIDs gelöscht werden.

Hierzu kann via $cache->flush($cacheID) bzw. die Kurzform Shop::Cache()->delete(string $cacheID) die ID gelöscht werden oder via $cache->flushTags(array $tags) bzw. Shop::Cache()->flushTags(array $tags) ganze Tags gelöscht werden.

Beispiel

.. code-block:: php

    <?php
    class testClass
    {
        [...]
        /**
        * **return int - the number of deleted IDs
        */
        public function invalidate () {
            return $this->cache->flushTags([$this->myCacheTag]);
        }
    }

Generierung von IDs
-------------------

CacheIDs sollten natürlich möglichst einzigartig sein, gleichzeitig aber auch in Ihrer Berechnung nicht zu komplex, um den Geschwindigkeitsvorteil des Caches nicht wieder zu verspielen.

Generell sollten alle Faktoren, die die Berechnung eines Wertes beeinflussen in die ID mit einbezogen werden.
Dies betrifft im Shop häufig die aktuelle Sprache (``$_SESSION['kSprache']`` bzw. ``Shop::$kSprache``), Kundengruppe (``$_SESSION['Kunde']->kKundengruppe`` oder Währung (``$_SESSION['Waehrung']->kWaehrung``).

Die Funktion

.. code-block:: php

    string JTLCache::getBaseID([bool $hash = false, bool $customerID = false, bool $customerGroup = true, bool $currencyID = true, bool $sslStatus = true])

versucht, die gängisten Einflussfaktoren zu bedenken und so eine Basis-ID zu generieren, die als Teil der CacheID verwendet werden kann.

Der erste Parameter gibt dabei an, ob ein md5-Hash generiert werden soll und die weiteren, welche Faktoren bedachte werden sollen.

Zweckmäßig wäre es beispielsweise, diese Basis-ID mit einer Abkürzung des Funktionsnamens zu kombinieren, in der die ID erstellt wird - wie ``$cacheID = 'mft_' . Shop::Cache()->getBaseID()``, wenn die entsprechende Zeile in einer Funktion namens "myFunctionTest" ist.

CacheIDs und Tags in Plugins
----------------------------

Die in Hook-Dateien verwendbaren ``$oPlugin``-Objekte haben die automatisch generierten Attribute *pluginCacheID* sowie *pluginCacheGroup*. Diese können verwendet werden, um nicht selbständig IDs berechnen zu müssen.
Außerdem werden diese beim Speichern von Optionen im Plugin-Backend automatisch invalidiert.


Weiteres
--------

Falls auch boolsche Werte im Cache gespeichert werden sollen, ist eine Prüfung des get-Ergebnisses gegen *JTLCache::RES_SUCCESS* mithilfe der Funktion ``JTLCache::getResultCode()`` notwendig, da ``JTLCache::get()`` im Fehlerfall *FALSE* zurückgibt.
So ist es nicht möglich, einen explizit gespeicherten boolschen Wert vom einem fehlgeschlagenen Lesevorgang zu unterscheiden.

Beispiel

.. code-block:: php

    $result = Shop::Cache()->get($cacheID);
    if (Shop::Cache()->getResultCode() === JTLCache::RES_SUCCESS) {
        //ok
    } else {
        //Cache miss - JTLCache::RES_FAIL
    }


Gleichzeitiges Setzen/Lesen mehrere Werte
-----------------------------------------

Über ``JTLCache::getMulti(array $cacheIDs)`` können mehrere Werte gleichzeitig ausgelesen sowie über ``JTLCache::setMulti(array $keyValue, array|null $tags[, int|null $expiration])`` gesetzt werden.

Beispiel

.. code-block:: php

    $foo = [
        'key1' => 'value1',
        'key2' => 222
    ];
    $write = $cache->setMulti($foo, ['tag1', 'tag2'], 60);
    Shop::dbg($write); //TRUE

    //request 3 keys while just 2 are set
    $keys = ['key1', 'key2', 'key3'];
    $read = $cache->getMulti($keys);
    Shop::dbg($res2);
    /*
    array(3) {
        [" key1 "] => string(6) "value1"
        [" key2 "] => int (222)
        [" key3 "] => bool(false)
    } */

Hooking
-------

Caching hat auch den Vorteil, dass gewisse Hooks nicht öfter ausgeführt werden müssen - wie z.B. Hook 110 (*HOOK_ARTIKEL_CLASS_FUELLEARTIKEL*).
Um Plugins, die durch diese Hooks übergebene Daten modifizieren, die Möglichkeit zu geben, auch eigene Cache-Tags hinzuzufügen, ist es angebracht, die vorgesehenen Tags ebenfalls an den Hook zu übergeben.

.. code-block:: php

    $cacheTags = [CACHING_GROUP_ARTICLE . '_' . $this->kArtikel, CACHING_GROUP_ARTICLE];
    executeHook(HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, [
        'oArtikel'  => &$this,
        'cacheTags' => &$cacheTags,
        'cached'    => false
        ]
    );
    $cache->set($key, $this, $cacheTags);

Aufgrund vielfachen Wunsches von Entwicklern wird der Hook 110 nun auf bei einem Cache-Hit ausgeführt.
Der übergebene Parameter *cached* ist in diesem Fall auf *TRUE* gesetzt. Falls Sie ein Plugin programmieren, das einmalig Eigenschaften eines Artikels modifiziert, achten Sie bitte darauf, komplexe Logik nur auszuführen, wenn der Parameter *FALSE* ist.
Anschließend werden Ihre Änderungen automatisch im Cache mit gespeichert und brauchen **nicht** erneut durchgeführt zu werden.

Auf diese Weise kann ein diesen Hook nutzendes Plugin einen eigenen Tag hinzufügen und beispielsweise bei Änderungen an den Plugin-Optionen reagieren und die betroffenen Caches leeren (vgl. jtl_example_plugin).
Dabei ist die Reihenfolge wichtig: erst Standard-Cache-Tags definieren, dann Hook mit Daten und Tags ausführen, anschließend Daten speichern. Nur so können die durch ein Plugin evtl. modifizierten Daten auch im Cache gespeichert und von diesem Invalidiert werden.

Welches Backend?
----------------

Generell sind alle implementierten Backends funktional, aufgrund ihrer Eigenheiten aber nur bedingt für alle Szenarien zu empfehlen.

Der **Dateien**-Cache ist der langsamste und unflexibelste, hat außerdem Probleme bei gleichzeitigen Zugriffen und sollte daher nur im Notfall genutzt werden. Allerdings ist er immer verfügbar und kann durch Auslagerung des Cache-Ordners auf ein RAM-basiertes Dateisystem deutlich beschleunigt werden.

Die seit Version 4.05 enthaltene Methode **Dateien (erweitert)** versucht, diese Nachteile durch `Symlinks <https://de.wikipedia.org/wiki/Symbolische_Verkn%C3%BCpfung>`_ zu umgehen.
Hierbei werden im Ordner ``templates_c/filecache`` für jeden Tag Unterordner angelegt, die Symlinks zu den einzelnen Cache-Einträgen enthalten. Hierdurch kann eine bessere Parallelität beim Schreiben von neuen Einträgen erreicht werden.
Unter bislang ungeklärten Umständen kann es jedoch vorkommen, dass fehlerhafte Links erstellt werden, sodass der Cache-Ordner nicht mehr geleert werden kann. Dies wird aktuell (Stand: Februar 2017) noch untersucht.

**APC** ist die schnellste Variante, hat im Praxistest bei hoher Belastung und vielen Einträgen aber Skalierungsprobleme. Zumindest im Bereich von ca. 3-4GB Daten wird er außerdem stark fragmentiert und die Leistung kann einbrechen.

Die für große Datenmengen am besten geeignet Variante ist **Redis**. Auch im Bereich von mehreren Gigabyte arbeitet sie schnell und kann außerdem auch `als Session-Handler genutzt werden <https://github.com/phpredis/phpredis#php-session-handler>`_.

Für **memcache(d)** gilt prinzipiell dasselbe, allerdings ist es weniger getestet.

**XCache** wurde bislang nicht getestet und ist nur der Vollständigkeit halber implementiert.

