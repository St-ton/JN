Die Shop-Klasse
===============

.. |br| raw:: html

   <br />

Ab Shop Version 4.x kommt der Klasse ``Shop`` eine zentrale Bedeutung zu. |br|
Sie dient in erster Linie als zentrale Registry für ehemals ausschließlich globale Variablen wie die *NiceDB* oder
*Smarty*, dient aber auch der Erzeugung und Ausgabe von Instanzen für den neuen Objektcache.

Während vor Shop Version 4.x Konstrukte wie

.. code-block:: php

    $Article = $GLOBALS['DB']->executeQuery('SELECT * FROM tartikel WHERE kArtikel = 2344', 1);  // veraltet!


nötig waren, um SQL-Abfragen auszuführen |br|
oder Templates via

.. code-block:: php

    // veraltet!
    //
    global $smarty;
    $smarty->assign('myvar', 123);
    $smarty->assign('myothervar', 'foobar');
    $smarty->display('mytemplate.tpl');

gerendert wurden, können und sollten nun Klasseninstanzen von *NiceDB* und *Smarty* über die ``Shop``-Klasse bezogen
werden.

Für Shop Version 4.x gilt folgende Vorgehensweise als bevorzugt:

.. code-block:: php

    $Article = Shop::DB()->query('SELECT * FROM tartikel WHERE kArtikel = 2344', 1);

bzw. ab Shop Version 5.0:

.. code-block:: php

   $Article = Shop::Contrainer()->getDB()->queryPrepared(
       'SELECT * FROM tartikel WHERE kArtikel = :artID',
       ['artID' => $articleID],
       ReturnType::SINGLE_OBJECT);

und für Smarty (in beiden Versionen von JTL-Shop):

.. code-block:: php

    Shop::Smarty()
        ->assign('myvar', 123)
        ->assign('myothervar', 'foobar')
        ->display('mytemplate.tpl');

Die Methode ``JTLSmarty::assign(string $tpl_var, mixed $value)`` wurde ab Shop 4.x nun "*chainable*" gemacht, um die
Übersichtlichkeit im Code zu erhöhen.  |br|
Zudem wurden die Funktionsnamen der *NiceDB*-Klasse etwas vereinfacht und über ein Mapping auch *statisch* verfügbar
gemacht (vgl. Funktion ``NiceDB::map(string $method)``).

Sprachfunktionen
----------------

Auch *Sprachfunktionen* sollten nun über die *Shop*-Klasse genutzt werden.

In JTL-Shop vor Version 4.x war diese Vorgehensweise üblich:

.. code-block:: php

    $GLOBALS['Sprache']->gibWert('basketAllAdded', 'messages');  // veraltet! (üblich in Shop 3.x)

Durch die Möglichkeiten der *Shop*-Klasse wird daraus:

.. code-block:: php

    Shop::Lang()->get('basketAllAdded', 'messages');

Caching
-------

Die Nutzung des *Caches* erfolgt analog den Sprachfunktionen und wird im Kapitel ":doc:`Cache </shop_plugins/cache>`"
näher erläutert.

Shop-URL
--------

Um die URL des Shops zu beziehen, wurde die Methode ``Shop::getURL([bool $bForceSSL = false]) : string`` eingeführt.

.. attention::

    Wir empfehlen dringend, diese Variante zu nutzen, anstelle der veralteten Konstante ``URL_SHOP``, |br|
    da ``Shop::getURL()`` auch eine eventuelle Konfiguration von *SSL* berücksichtigt. |br|

Die Ausgabe erfolgt stets **ohne abschließenden Slash**.

GET-Parameter
-------------

Außerdem wurde die Behandlung von *GET-Parametern* und das Parsen von *SEO-URLs* in die *Shop*-Klasse
verlagert. |br|
Die zentralen Einstiegspunkte sind dabei die Funktionen ``Shop::run()`` und ``Shop::getParameters()``, die von allen
direkt aufgerufenen PHP-Dateien in der Shop-Root ausgeführt werden.

Debugging
---------

Die Funktion ``Shop::dbg(mixed $content[, bool $die, string $prepend]) : void`` erlaubt "quick-and-dirty" *Debugging*.

Als ersten Parameter erhält sie beliebigen Inhalt zur Ausgabe. Über den zweiten Parameter kann - wenn dieser auf
``true`` gesetzt wird - die weitere Ausführung des Codes unterbunden werden und der dritte Parameter kann einen Text
beinhalten, der vor der Debug-Ausgabe als Erläuterung erscheinen soll. |br|
Dies entspricht im Wesentlichen einem von ``<pre>``-Tags umhüllten ``var_dump()`` mit ggf. anschließendem ``die()``.

