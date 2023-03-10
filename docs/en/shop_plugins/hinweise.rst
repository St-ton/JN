Hinweise, Tipps & Tricks
========================

.. |br| raw:: html

   <br />

.. role:: strike
   :class: strike

Seit JTL-Shop 4.0 gibt es einige Möglichkeiten, um die Entwicklung von Plugins für den JTL-Shop zu vereinfachen.

Konstanten
----------

Im ersten Schritt können in der ``[Shop-Root]/includes/config.JTL-Shop.ini.php`` einige Konstanten definiert werden:

.. code-block:: php

    //backtrace bei SQL-Exceptions auslösen
    define('NICEDB_EXCEPTION_BACKTRACE', true);

    //Backtrace ggf. via echo im Frontend ausgeben
    define('NICEDB_EXCEPTION_ECHO', true);

    //alle durch PHP verursachten Fehler, Warnungen und Hinweise im Frontend anzeigen
    define('SHOP_LOG_LEVEL', E_ALL);

    //alle Fehler, Warnungen und Hinweise bei Wawi-Abgleich anzeigen
    define('SYNC_LOG_LEVEL', E_ALL);

    //alle Fehler, Warnungen und Hinweise im Backend anzeigen
    define('ADMIN_LOG_LEVEL', E_ALL);

    //alle Fehler, Warnungen und Hinweise in Templates anzeigen
    define('SMARTY_LOG_LEVEL', E_ALL);

    //Smarty-Templates bei jedem Seitenaufruf neu kompilieren (Work-around für Smarty 3.1.27 bei aktiviertem OpCache)
    define('SMARTY_FORCE_COMPILE', true);

    //Fallbacks für alte Templates deaktivieren
    define('TEMPLATE_COMPATIBILITY', false);

Abgleich mit JTL-Wawi
"""""""""""""""""""""

Falls zu Debug-Zwecken der Abgeich mit JTL-Wawi näher untersucht werden soll, lässt sich das Löschen der übertragenen
XML-Dateien folgendermaßen verhindern:

.. code-block:: php

    define('KEEP_SYNC_FILES', true);

Plugin-Profiler
"""""""""""""""

Darüber hinaus können eventuelle Performance-Probleme mit Plugins anhand des Plugin-Profilers untersucht werden.

.. code-block:: php

    define('PROFILE_PLUGINS', true);

Sobald eine Seite im Frontend aufgerufen wird, findet sich im Backend unter "System -> Profiler" (bis JTL-Shop 4.x)
bzw. "Fehlerbehebung -> Plugin-Profiler" (ab JTL-Shop 5.x) im Tab "*Plugins*" eine genauere Analyse der ausgeführten
Plugins und deren Hooks.

XHProf / Tideways
"""""""""""""""""

Falls *XHProf* oder *Tideways* auf dem Server installiert sind, kann über die Konstante

.. code-block:: php

    define('PROFILE_SHOP', true);

auch der gesamte Code des Onlineshops analysiert werden.

SQL-Queries
"""""""""""

Sämtliche über die NiceDB-Klasse ausgeführten SQL-Queries können via

.. code-block:: php

    define('PROFILE_QUERIES', true);

im Profiler gespeichert werden. |br|
Unter "System -> Profiler" (bis JTL-Shop 4.x), bzw. "Plugin-Profiler" (ab JTL-Shop 5.x) sind sie anschließend
im Tab "*SQL*" zu sehen.

Alternativ lassen sie sich via

.. code-block:: php

    define('PROFILE_QUERIES_ECHO', true);

auch direkt im Frontend anzeigen.

In beiden Fällen kann der Informationsgehalt über

.. code-block:: php

    //verbosity level. 0-3
    define('DEBUG_LEVEL', 0);

gesteuert werden. Je höher der Wert, desto mehr Informationen werden gespeichert bzw. ausgegeben.


.. _label_hinweise_wkchecksum:

Checksumme für den Warenkorb
----------------------------

Mit der Version 4.05 von JTL-Shop wurde im Warenkorb eine Checksumme zur Prüfung auf Konsistenz eingeführt
("Breaking Change"). |br|
Mit dieser Prüfung soll verhindert werden, dass während der Anzeige der Bestellzusammenfassung für den Kunden im
Hintergrund Änderungen an den gekauften Artikeln durchgeführt werden, die dem Kunden nicht angezeigt werden. Solche
Änderungen könnten z. B. Preisänderungen durch einen Abgleich mit JTL-Wawi oder parallele Abverkäufe sein. |br|

Eine solche Änderung wird durch den Vergleich der Prüfsumme direkt vor dem Speichern der Bestellung
mit der Meldung quittiert:

.. code-block:: console

    Ihr Warenkorb wurde aufgrund von Preis- oder Lagerbestandsänderungen aktualisiert.
    Bitte prüfen Sie die Warenkorbpositionen.

Der Kunde wird dann zurück zum Warenkorb geleitet.

.. important::

    Ein Plugin, das direkt den Warenkorb manipuliert (um z. B. einen speziellen Rabatt einzufügen), muss selbst dafür
    sorgen, die Prüfsumme nach den eigenen Änderungen zu aktualisieren, damit die Bestellung nicht in einer Schleife
    endet.

Die Aktualisierung erfolgt durch den statischen Aufruf der Methode ``refreshChecksum()`` der Klasse ``Warenkorb``
mit dem aktuellen Warenkorb als Parameter.

.. code-block:: php

    Warenkorb::refreshChecksum($_SESSION['Warenkorb']);

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

Eine simple *Registry* zum Speichern von beliebigen Werten innerhalb eines Requests kann über die Shop-Klasse erreicht
werden. |br|
Hierfür sind die Funktionen ``Shop Shop::get(string $key)`` zum Auslesen, ``bool Shop::has(string $key)`` zum
Prüfen sowie ``mixed Shop::set(string $key, mixed $value)`` zum Setzen vorhanden.

Beispiel:

.. code-block:: php

    //file1.php
    Shop::set('my-plugin-var01', ['foo' => 'bar']);

    //file2.php, später aufgerufen
    $test  = Shop::has('my-plugin-var01'); //TRUE
    $data  = Shop::get('my-plugin-var01'); //array('foo' => 'bar')
    $test2 = Shop::has('NOT-my-plugin-var01'); //FALSE

SQL
---

JTL-Shop 4 vereinfacht einige häufige Aufrufe der NiceDB-Klasse, sodass nicht mehr auf das globale NiceDB-Objekt
zugegriffen werden muss und die Methoden-Namen leichter zu merken sind. Die Parameter sind dabei unverändert geblieben.
Eine Übersicht findet sich in der folgenden Tabelle.

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

Inbesondere ab Version 4.0 wird dringend geraten, die Funktionen ``NiceDB::insert()``, ``NiceDB::delete()`` und
``NiceDB::update()`` anstelle von ``NiceDB::executeQuery()`` zu nutzen. |br|
Nur diese Varianten nutzen *Prepared Statements*!

Ab JTL-Shop Version 5.x, und besonders im Object-Kontext, wird auf diese Methoden nicht mehr direkt und statisch
zugegriffen, sondern via *Dependency Injection Container*. Ein Beispiel sehen Sie hier:

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

Selektieren einzelner Zeilen
""""""""""""""""""""""""""""

Insbesondere bei der Behandlung von Nutzereingaben ist es fahrlässig, unbehandelte POST- oder GET-Parameter direkt
in SQL-Queries zu integrieren!

**Negativ-Beispiel:**

.. code-block:: php

    $row = $GLOBALS['NiceDB']->executeQuery("SELECT * FROM my_table WHERE id = " . $_POST['id'], 1);

Falls es sich bei der Spalte ``id`` um einen numerischen Datentyp handelt, sollte zumindest ein Datentyp-Casting
vorgenommen werden, z. B. mittels ``(int)$_POST['id']``.

Der präferierte Weg wäre jedoch die Nutzung der Methode ``NiceDB::selectSingleRow()``.

Das obige "Negativ-Beispiel" ließe sich damit wie folgt umschreiben:

**Positiv-Beispiel:**

.. code-block:: php

    $result = Shop::DB()->select('my_table', 'id', (int)$_POST['id']);

.. hint::

    ``Shop::DB()->query()`` ist analog zu ``$GLOBALS['NiceDB']->executeQuery($sql, 1)`` |br|
    bzw. ``Shop::DB()->query($sql, 1)`` mit zweitem Parameter auf "1" gesetzt, was für "single fetched object" steht.

    Hierbei sind allerdings nur einfache *WHERE*-Bedingungen mit *AND*-Verknüpfungen möglich.

Einfügen von Zeilen
"""""""""""""""""""

Analog zum Selektieren ein Beispiel mit einem *Insert*:

**Unsichere Variante:**

.. code-block:: php

    $i = $GLOBALS['NiceDB']->executeQuery("
        INSERT INTO my_table
            ('id', 'text', 'foo')
            VALUES (" . $_POST['id'] . ", '" . $_POST['text'] . "', '" . $_POST['foo'] . "')", 3
    );

**Bessere Variante:**

.. code-block:: php

    $obj       = new stdClass();
    $obj->id   = (int) $_POST['id'];
    $obj->text = $_POST['text'];
    $obj->foo  = $_POST['foo'];
    $i = Shop::DB()->insert('my_table', $obj);

Löschen von Zeilen
""""""""""""""""""

**Unsichere Variante:**

.. code-block:: php

    $GLOBALS['NiceDB']->executeQuery("
        DELETE FROM my_table
            WHERE id = " . $_POST['id'], 3
    );

**Bessere Variante:**

.. code-block:: php

    Shop::DB()->delete('my_table', 'id', (int) $_POST['id']);

Bei erweiterten WHERE-Klauseln mit *AND*-Bedingung können zwei Arrays mit jeweils allen Keys und allen Values
übergeben werden:

.. code-block:: php

    Shop::DB()->delete('my_table', array('id', 'foo'), array(1, 'bar'));
    // --> DELETE FROM my_table WHERE id = 1 AND 'foo' = 'bar'

Aktualisieren von Zeilen
""""""""""""""""""""""""

**Unsichere Variante:**

.. code-block:: php

    $GLOBALS['NiceDB']->executeQuery("
        UPDATE my_table
            SET id = " . $_POST['new_id'] . ",
                foo = '" . $_POST['foo'] . "',
                bar = 'test'
            WHERE id = " . $_POST['id'], 3
    );

**Bessere Variante:**

.. code-block:: php

    $obj      = new stdClass();
    $obj->id  = (int) $_POST['new_id'];
    $obj->foo = $_POST['foo'];
    $obj->bar = 'test';
    Shop::DB()->update('my_table', 'id', (int) $_POST['id'], $obj);

.. important::

    Sollte es nicht möglich sein, die beschriebenen Methoden zu nutzen, so sollten sämtliche potentiell
    gefährlichen Werte über ``Shop::DB()->escape()`` zuvor maskiert, bzw. im Fall von Numeralen konvertiert, werden.

Änderungen von JTL-Shop 3.x zu JTL-Shop 4.x
-------------------------------------------

Eine kurze Übersicht von Änderungen in JTL-Shop 4:

* ``smarty->assign()`` kann nun *gechaint* werden:

.. code-block:: php

    $smarty->assign('var_1', 1)
           ->assign('var_2', 27)
           ->assign('var_3', 'foo');

* Die Klasse ``Shop`` bildet einen zentralen Einstiegspunkt für häufig verwendete Funktionalitäten:

.. code-block:: php

    Shop::Cache()->flushAll(); //Objektcache leeren

    $arr = Shop::DB()->query($sql, 2); //Alias für $GLOBALS['DB']->executeQuery()

    $translated = Shop::Lang()->get('newscommentAdd', 'messages'); //Alias für $GLOBALS['Sprache']->gibWert()

    $shopURL = Shop::getURL(); //statt URL_SHOP, prüft auf SSL

    $conf = Shop::getSettings(array(CONF_GLOBAL, CONF_NEWS)); //Alias für $GLOBALS['Einstellungen']...

    Shop::dbg($someVariable, false, 'Inhalt der Variablen:'); //Schnelles Debugging

    $smarty = Shop::Smarty(); //Alias für globales Smarty-Objekt

    Shop::set('my_key', 42); //Registry-Setter

    $value = Shop::get('my_key'); //Registry-Getter - 42

    $hasValue = Shop::has('some_other_key'); //Registry-Prüfung - false
