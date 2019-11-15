Themes editieren
================

.. |br| raw:: html

    <br />

Um Farben und Abstände im Template zu ändern, passen Sie Ihr *Theme* Ihren Wünschen entsprechend an. |br|
Hierfür gibt es je nach Shop-Version und verwendetem Template verschiedene Werkzeuge rund um den Shop.

Für Shop-Versionen ab 5.0 wird das Plugin "*JTL Theme-Editor*" verwendet. Er kann für die Templates EVO und NOVA
eingesetzt werden.

In den Shop-Versionen 4.x, welche standardmäßig mit dem EVO-Template ausgeliefert werden, war das
Plugin "*Evo-Editor*" für diese Aufgabe vorgesehen. Ab Shop 5.0 übernimmt das Plugin "*JTL Theme-Editor*" diese
Aufgaben für das EVO- und das NOVA-Template. |br|
Zusätzlich stellt der Shop über die Template-Einstellungen noch die Möglichkeit bereit, mit dem "*Evo-LiveStyler*",
das Evo-Template geringfügig anzupassen, ohne ein Plugin dafür installieren zu müssen.

Das Plugin "JTL Theme-Editor"
-----------------------------

Wie schon eingangs erwähnt, ist dieses Plugin für Shops ab Version 5.0 vorgesehen.

Mit diesem Plugin ist es möglich, die Themes der mit JTL-Shop 5.0 ausgelieferten Templates "EVO" und "NOVA"
anzupassen. |br|
Sie finden dieses Plugin zum Beispiel im aktuellen
gitlab-Repository "`Theme Editor <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_theme_editor>`_".

Sobald Sie das Plugin "*JTL Theme-Editor*" erfolgreich installiert haben, gelangen Sie über dessen
"Einstellungen"-Button zur Editor-Ansicht.

.. image:: /_images/jtl_theme_editor_00.png

Hier haben Sie nun die Möglichkeit, Anpassungen am aktuellen Theme vorzunehmen. |br|

.. image:: /_images/jtl_theme_editor_01.png

Um nun Ihre Änderungen auch wirksam zu machen, müssen Sie Ihr Theme nur noch kompilieren — hierzu dient der
entsprechende Button im Theme-Editor.

Das Plugin "JTL Evo-Editor"
---------------------------

Das Plugin "*Evo-Editor*" wurde speziell für das JTL-Template "EVO", der Shop-Versionen 4.x, konzipiert. |br|
Er funktioniert äquivalent dem "*JTL Theme-Editor*", ist aber ausschließlich nur für das EVO-Template anwendbar.

Dieses Plugin ist ebenfalls im entsprechenden
gitlab-Repository "`Evo Editor <https://gitlab.com/jtl-software/jtl-shop/legacy-plugins/evo-editor>`_" zu finden.

Der "Evo-LiveStyler"
--------------------

Diese Erweiterung wurde speziell für das EVO-Template entwickelt und existiert in keinem anderen JTL-Template.

Mit dem "*Evo-LiveStyler*" ist es ganz einfach, Änderungen an Farben, Schriften und Abständen, live im Frontend,
vorzunehmen.

.. warning::

    Änderungen, die hier vorgenommen werden, können nicht durch ein Wechseln des *Themes* rückgängig
    gemacht werden. |br|
    Der "*Evo-LiveStyler*" ist nicht für den Einsatz in einer Produktiv-Umgebung gedacht!

Um den "*Evo-LiveStyler*" verwenden zu können, müssen Sie zunächst das Plugin "*Evo-Editor*" in der Pluginverwaltung
installieren und aktivieren. |br|
Aktivieren Sie als Nächstes den "*Evo-LiveStyler*" in den "Template-Einstellungen" unter der Rubrik
"Entwickler-Einstellungen".

.. important::

    Der "*Evo-LiveStyler*" kann nur verwendet werden, wenn Sie als Admin eingeloggt sind und sowohl das
    Backend als auch das Frontend über das gleiche Protokoll (also jeweils ``http://`` oder ``https://``)
    aufgerufen werden!


Um nun Änderungen am Theme vornehmen zu können, gehen Sie bitte in das Frontend Ihres Shops. Sie sehen nun oben links
einen Button mit dem Titel ``LiveStyler öffnen``. |br|
Dieser Button öffnet eine Ansicht der Art:

.. image:: /_images/livestyler.jpg

Der "*Evo-LiveStyler*" zeigt Ihnen alle Variablen und deren Werte. Diese Variablen verändern das Aussehen Ihres Shops
bezüglich Farben, Abständen und Schriftgrößen.
Alle verfügbaren Variablen finden Sie auf der entsprechenden Seite
zu `Bootstrap 3.4 <https://getbootstrap.com/docs/3.4/customize/#less-variables>`_.

Wenn Sie die gewünschten Änderungen vorgenommen haben, können Sie auf den Button ``Vorschau`` klicken, um zu
sehen, wie Ihre Änderungen wirken. |br|
Sind Sie zufrieden, klicken Sie auf den Button ``Speichern``.

