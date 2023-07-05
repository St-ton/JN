Alerts
======

.. |br| raw:: html

   <br />

Alle Alerts (Meldungen wie z. B. Fehler/Hinweise) werden ab JTL-Shop 5.0 mit dem Alert-Service erzeugt.

Den *Alert*-Service beziehen Sie über die Shop-Klasse:

.. code-block:: php

    <?php

    use JTL\Services\JTL\AlertServiceInterface;

    $alertHelper = Shop::Container()->getAlertService();

Eine Meldung wird mithilfe der Methode ``addAlert()`` erzeugt. |br|
Diese Methode kennt die drei Parameter *type*, *message* und *key*. Für den Parameter *type* können Sie die Konstanten
der *Alert*-Klasse nutzen:

+--------------------+--------------------------------------------+
| Konstante          | Wert                                       |
+====================+============================================+
| ``TYPE_PRIMARY``   | *primary*                                  |
+--------------------+--------------------------------------------+
| ``TYPE_SECONDARY`` | *secondary*                                |
+--------------------+--------------------------------------------+
| ``TYPE_SUCCESS``   | *success*                                  |
+--------------------+--------------------------------------------+
| ``TYPE_DANGER``    | *danger*                                   |
+--------------------+--------------------------------------------+
| ``TYPE_WARNING``   | *warning*                                  |
+--------------------+--------------------------------------------+
| ``TYPE_INFO``      | *info*                                     |
+--------------------+--------------------------------------------+
| ``TYPE_LIGHT``     | *light*                                    |
+--------------------+--------------------------------------------+
| ``TYPE_DARK``      | *dark*                                     |
+--------------------+--------------------------------------------+
| ``TYPE_ERROR``     | *error* (ehemals für ``$cFehler`` genutzt) |
+--------------------+--------------------------------------------+
| ``TYPE_NOTE``      | *note* (ehemals für ``$cHinweis`` genutzt) |
+--------------------+--------------------------------------------+

Die Spalte *Wert* steht für die jeweilige Bootstrap 4 CSS-Klasse.

.. code-block:: php

    <?php

    $alertHelper->addAlert(Alert::TYPE_INFO, 'Das ist eine Testinfo!', 'testInfo');

Der Typ wird als CSS-Klasse ``alert-type`` für das jeweilige *Alert* hinzugefügt, was in diesem Beispiel einer
Bootstrap 4 *alert-info*-Klasse entspricht. |br|
Der letzte Parameter *key* stellt einen Schlüssel-String dar, über welchen ein Alert identifiziert und ggf.
überschrieben werden kann. Dieser *key* wird zudem im HTML ausgegeben (data-Attribut ``data-key``) und ist per
Javascript/CSS ansprechbar.

Optionen
--------

Des Weiteren können dem *Alert* Optionen übergeben werden. |br|
Mit der Option *dismissable* kann z. B. erzwungen werden, das *Alert* erst durch Benutzerinteraktion zu schließen.

.. code-block:: php

    <?php

    $alertHelper->addAlert(
        Alert::TYPE_INFO,
        'Das ist eine Testinfo!',
        'testInfo',
        ['dismissable' => true]
    );

Alle möglichen Optionen:

+-----------------------------+--------+---------+-----------------------------------------------------------------+
| Option                      | Typ    | Default | Beschreibung                                                    |
+=============================+========+=========+=================================================================+
| ``dismissable``             | bool   | false   | Alert wegklickbar                                               |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``fadeOut``                 | int    |  0      | Fadeout Timer (z. B. per Konstante: ``Alert::FADE_SLOW`` =9000, |
|                             |        |         | was 9 Sekunden entspricht, oder direkt einen Integer eingeben)  |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``showInAlertListTemplate`` | bool   | true    | Alert an zentraler Stelle im Header ausgeben                    |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``saveInSession``           | bool   | false   | Alert in der *SESSION* speichern (z. B. für Redirects)          |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``linkHref``                | string |         | Ganzes Alert als Link                                           |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``linkText``                | string |         | Wenn ``linkHref`` und ``linkText`` gesetzt sind, wird           |
|                             |        |         | an die Message der Text als Link angehängt                      |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``icon``                    | string |         | *Fontawesome*-Icon                                              |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``id``                      | string |         | Fügt dem HTML den Wert der ``id`` hinzu                         |
+-----------------------------+--------+---------+-----------------------------------------------------------------+

Anzeige im Frontend
-------------------

Die Alerts werden in der Smarty-Variable ``alertList`` als Collection gespeichert. Alle Alerts, bei denen
``showInAlertListTemplate === true`` gesetzt ist, werden zentral im Header ausgegeben.

.. code-block:: html+smarty

    {include file='snippets/alert_list.tpl'}

Falls Sie ein Alert statt im Header an einer speziellen Stelle in einem Template ausgeben lassen wollen,
dann setzen Sie die Option ``showInAlertListTemplate`` auf ``false``. Geben Sie dann das *Alert* an gewünschter Stelle
wie folgt aus:

.. code-block:: html+smarty

    {$alertList->displayAlertByKey('testInfo')}

