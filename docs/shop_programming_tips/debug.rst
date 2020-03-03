Debug
=====

Plugin "JTL-Debug"
------------------

.. |br| raw:: html

   <br />

*JTL Debug* ist ein in JTL-Shop integriertes Plugin zur Ausgabe von Template- und Onlineshop-Informationen zur
Unterstützung der Template- und Plugin-Entwicklung.  |br|
Mit diesem Plugin können Sie sich zur Laufzeit des Onlineshops verschiedene Template-Informationen anzeigen lassen:

* Smarty-Variablen
* aktive Hooks
* PHP-Fehler
* Session
* POST-Objekte
* GET-Objekte
* COOKIE-Objekte
* Script-Speicherverbrauch
* phpinfo()
* Cache-Informationen
* NiceDB-Profiler
* Plugin-Profiler

Dies ist sehr nützlich bei der Entwicklung eines eigenen Templates oder Plugins.

Installation
""""""""""""

*JTL Debug* wird mit JTL-Shop ausgeliefert und lässt sich im Shop-Backend über die Pluginverwaltung installieren. |br|
Anschließend können Sie, in der Pluginverwaltung, die Einstellungen von *JTL Debug* konfigurieren und den Umfang der
angezeigten Informationen Ihren Bedürfnissen anpassen.

Einstellungen
"""""""""""""

Standardmäßig wird *JTL Debug* im Frontend Ihres Shops über die Tastenkombination ``[STRG]+[Enter]`` aufgerufen. |br|
Sie können *JTL Debug* auch über einen GET-Parameter aufrufen, statt über die Tastenkombination. Stellen Sie dazu die
Einstellung "*Nur bei GET-Parameter aktivieren*" auf "Ja". |br|
Der Name des GET-Parameters ist standardmäßig ``jtl-debug``.

Unter dem Punkt "Ausgabe" können Sie angeben, welche Informationen Ihnen *JTL Debug* anzeigen soll.

Frontend
""""""""

Wenn Sie im Frontend Ihres Onlineshops nun ``[STRG]+[Enter]`` drücken, öffnet sich die Debug-Ausgabe. |br|
Hier können Sie bequem über die eingebaute Suchfunktion nach Variablen und Werten suchen.

.. image:: /_images/debug_plugin.png

.. _label_debugbar:

Frontend Debug-Bar
------------------

Als weitere Debug-Möglichkeit stellt JTL-Shop die `PHP Debug Bar <https://github.com/maximebf/php-debugbar>`_ bereit,
welcher über die Konstante ``SHOW_DEBUG_BAR`` aktiviert werden kann. |br|
Setzen Sie diese Konstante in der Shop-Config (``includes/config.JTL-Shop.ini.php``) auf ``true``, um im unteren
Bildschirmbereich ebenfalls Debug-Informationen angezeigt zu bekommen:

.. image:: /_images/php_debug_bar.png
