JTL Debug
=========

*******************
Was ist JTL Debug?
*******************

JTL Debug ist ein, in JTL-Shop integriertes, Plugin zur Ausgabe von Template- und Shop-Informationen zur Unterstützung von Template- und Plugin-Entwicklungen. Mit diesem Plugin können Sie verschiedene Template-Informationen ausgeben:

* Smartyvariablen
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
------------

JTL Debug wird mit JTL-Shop ausgeliefert und lässt sich im Shop-Backend über Plugins -> Pluginverwaltung installieren. Anschließend können Sie in der Pluginverwaltung die Einstellungen von JTL Debug konfigurieren, in dem Sie auf den Button mit den Zahnrädern klicken.

Einstellungen
-------------

Standardmäßig wird JTL Debug im Frontend Ihres Shops über die Tastenkombination STRG + Enter aufgerufen. Sie können JTL Debug auch über einen GET-Parameter aufrufen, statt über die Tastenkombination.
Stellen Sie dazu die Einstellung "Nur bei GET-Parameter aktivieren" auf Ja. Der Name des GET-Parameters ist im Standard "jtl-debug".

Unter dem Punkt 'Ausgabe' können Sie angeben, welche Informationen Ihnen JTL Debug anzeigen lassen soll.

Frontend
--------

Wenn Sie in Ihrem Shop-Frontend nun STRG + Enter drücken, öffnet sich die Debug-Ausgabe. Hier können Sie bequem über die eingebaute Suchfunktion nach Variablen und Werten suchen.

.. image:: /_images/jtl-shop_debug_frontend.jpg