Die Shop-Klasse
===============

.. |br| raw:: html

   <br />

Der Klasse ``Shop`` kommt eine zentrale Bedeutung zu. |br|
Sie dient in erster Linie als zentrale Registry für ehemals ausschließlich globale Variablen wie die *NiceDB* oder
*Smarty*, dient aber auch der Erzeugung und Ausgabe von Instanzen für den neuen Objektcache.

In JTL-Shop 5.x können und sollten nun Klasseninstanzen von *NiceDB* und *Smarty* über die ``Shop``-Klasse bezogen
werden.

Ab JTL-Shop 5.0 wird folgende Vorgehensweise bevorzugt:

.. code-block:: php

   $product = Shop::Container()->getDB()->queryPrepared(
       'SELECT * FROM tartikel WHERE kArtikel = :artID',
       ['artID' => $articleID],
       ReturnType::SINGLE_OBJECT
   );

Für Smarty wird in JTL-Shop 5.0 diese Vorgehensweise bevorzugt:

.. code-block:: php

    Shop::Smarty()
        ->assign('myvar', 123)
        ->assign('myothervar', 'foobar')
        ->display('mytemplate.tpl');

Die Methode ``JTLSmarty::assign(string $tpl_var, mixed $value)`` wurde "*chainable*" gemacht, um
die Übersichtlichkeit im Code zu erhöhen.  |br|

Sprachfunktionen
----------------

Auch *Sprachfunktionen* sollten nun über die *Shop*-Klasse genutzt werden.

.. code-block:: php

    Shop::Lang()->get('basketAllAdded', 'messages');

Caching
-------

Die Nutzung des *Caches* erfolgt analog den Sprachfunktionen und wird im Kapitel ":doc:`Cache </shop_plugins/cache>`"
näher erläutert.

Onlineshop-URL
--------------

Um die URL des Onlineshops zu beziehen, wurde die Methode ``Shop::getURL([bool $bForceSSL = false]) : string``
eingeführt.

.. attention::

    Wir empfehlen dringend, diese Variante anstelle der veralteten Konstante ``URL_SHOP`` zu nutzen, |br|
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

Als ersten Parameter erhält sie beliebigen Inhalt zur Ausgabe. Wird der zweite Parameter auf
``true`` gesetzt, kann die weitere Ausführung des Codes unterbunden werden. Der dritte Parameter kann einen Text
beinhalten, der vor der Debug-Ausgabe als Erläuterung erscheinen soll. |br|
Dies entspricht im Wesentlichen einem von ``<pre>``-Tags umhüllten ``var_dump()`` mit ggf. anschließendem ``die()``.

