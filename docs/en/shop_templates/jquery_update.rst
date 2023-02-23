Update auf jQuery 3.x (ab JTL-Shop 5.0)
=======================================

.. |br| raw:: html

   <br />

Mit Shop-Version 5.0.0 wurde für das Javascript Framework `jQuery <https://jquery.com/>`_ ein größerer Versionssprung
vollzogen.

.. hint::

    Ab Version 5.x nutzt JTL-Shop **jQuery 3.x**.

Dieser Versionssprung ist inbesondere für Plugin- und Templateentwickler relevant, die die Funktionen von *jQuery*
intensiv nutzen.

Um hier Fehler im Frontend zu vermeiden, bieten die Entwickler von jQuery in Form einer eigenen
Migrationslösung Unterstützung an. |br|
Hierbei handelt es sich um einen API-Wrapper, der die Funktionalitäten von jQuery 3.x so modifiziert, dass sie
genutzt werden können, als würden sie von JQuery 1.12/2.x bereitgestellt. Allerdings werden für alle
nicht mit 3.x konformen Frameworkzugriffe entsprechende Warnungen in der Browser-JS-Konsole ausgegeben.

Sie finden diese Migrationslösung unter `jQueryMigrate <https://github.com/jquery/jquery-migrate>`_.

Dieser API-Wrapper ist dafür gedacht, Fehler in bestehenden Applikationen aufzuspüren und auszumerzen.

Für Legacy-Code, dessen Migration aus Kosten/Nutzen-Gründen nicht mehr auf Version 3.x angepasst wird,
bieten die Entwickler von jQuery zudem eine produktiv nutzbare, minifizierte Version
von *jQueryMigrate* an. |br|
Diese Version gibt lediglich einen kurzen Hinweis zum Ladezeitpunkt aus, bzw. eine gesonderte Meldung, wenn sie in
der entsprechenden Applikation eine nicht unterstützte jQuery-Version vorfindet.

.. note::

    *jQuery-Migrate* unterstützt nur die *jQuery*-Versionen 1.12.x und 2.2.x. |br|
    In JTL-Shop wird bis zu Version 4.06 *jQuery 1.12* eingesetzt.

Weitere Informationen finden Sie im offiziellen `Upgrade Guide von jQuery <https://jquery.com/upgrade-guide/3.0/>`_.

*jQueryMigrate* sollte nicht dauerhaft in Plugins und/oder Templates eingebaut werden, da es sich um eine Hilfestellung
zum Versionswechsel handelt. |br|
Wir empfehlen eine temporäre Einbindung, um Konformität mit *jQuery 3.x* herzustellen.

Dabei wird *jQueryMigrate* **nach** dem eigentlichen *jQuery*-Framework geladen:

.. code-block:: html
   :emphasize-lines: 2

   <script src="https://code.jquery.com/jquery-3.0.0.js"></script>
   <script src="https://code.jquery.com/jquery-migrate-3.1.0.js"></script>

Sobald Sie alle Anpassungen vorgenommen haben, können Sie die *jQueryMigrate*-Zeile wieder entfernen.

jQueryMigrate Helper-Plugin
---------------------------

Noch einfacher lässt sich das Hinzufügen von *jQueryMigrate* in den Shop erledigen, wenn Sie das Helper-Plugin
"*jtl_jquerymigrate_helper*" verwendeen. |br|
Es ist hier zu beziehen:  `JTL gitlab repository <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_jquerymigrate_helper>`_.

Dieses Plugin bindet nach der Installation *jQueryMigrate 3.1.1-pre* in das Shop-Frontend ein und entfernt es
mittels Plugin-Deinstallation ebenso einfach wieder aus dem Frontend.

In der Javascript-Konsole des Browsers werden alle *nicht jQuery-3 konformen*
API-Zugriffe angezeigt, angeführt durch den String ``JQMIGRATE``.

.. image:: /_images/jqm_scrcapt.png

Für Shop-Version 4.x steht ein gesonderter Branch namens ``shop-v4`` zur Verfügung:
`branch shop-v4 <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_jquerymigrate_helper/tree/shop-v4>`_

Das Plugin "*jtl_jquerymigrate_helper*" bringt zusätzlich die minifizierte Version von *jQueryMigrate* mit, die
prinzipiell für den produktiven Einsatz aktiv bleiben kann. |br|
Allerdings wird hiervon dringend abgeraten. |br|
Die einzige Ausnahme besteht, wie oben bereits erwähnt, in Applikationen, deren Überarbeitung ein ungünstiges
Kosten-Nutzen-Verhältnis aufweist. Bei diesen kann die minifizierte Version von *jQueryMigrate* dauerhaft produktiv aktiviert
bleiben.

.. attention::

    Für alle Varianten der Einbindung gilt: Nach erfolgter Überarbeitung der Applikation bitte *JQueryMigrate* wieder
    deaktivieren!
