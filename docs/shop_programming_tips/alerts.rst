Alerts
======

Alle Alerts (Meldungen wie z.B. Fehler/Hinweise) werden ab Shop 5.0.0 mit dem Alert-Service erzeugt.

.. code-block:: php

    <?php

    use Services\JTL\AlertServiceInterface;

    $alertHelper = Shop::Container()->getAlertService();

Eine Meldung wird durch die Eingabe von 3 Werten erzeugt (type, message, key). Dabei kann auf die Typen in der
Alert-Klasse zugegriffen werden.

.. code-block:: php

    <?php

    $alertHelper->addAlert(Alert::TYPE_INFO, 'Das ist eine Testinfo!', 'testInfo');

Die Typen werden als css-Klasse ``alert-type`` für das jeweilige Alert hinzugefügt. Was in unserem Beispiel z.B. einer
Bootstrap 4 alert-info Klasse entsprechen würde.

Optionen
--------

Des Weiteren können dem Alert Optionen übergeben werden. Mit der Option *dismissable*, kann z.B. die Option
eingeschalltet werden, das Alert per Klick zu schließen.

.. code-block:: php

    <?php

    $alertHelper->addAlert(
        Alert::TYPE_INFO,
        'Das ist eine Testinfo!',
        'testInfo',
        ['dismissable' => true]
    );

Alle möglichen Optionen:

+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| Option                  | Typ    | Default | Beschreibung                                                                                               |
+=========================+========+=========+============================================================================================================+
| dismissable             | bool   | false   | Alert wegklickbar.                                                                                         |
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| fadeOut                 | int    |  0      | Fadeout Timer, z.B. Alert::FADE_SLOW -> 9000, was 9 Sekunden entspricht oder direkt einen Integer eingeben.|
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| showInAlertListTemplate | bool   | true    | Alert an zentraler Stelle im header ausgeben.                                                              |
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| saveInSession           | bool   | false   | Alert in der Session speichern (z.B. für Redirects).                                                       |
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| linkHref                | string |         | Ganzes Alert als Link.                                                                                     |
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| linkText                | string |         | Wenn linkHref und linkText gesetzt sind, wird an die message der Text als Link angehangen.                 |
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| icon                    | string |         | Fontawesome-Icon.                                                                                          |
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+
| id                      | string |         | Fügt html id hinzu.                                                                                        |
+-------------------------+--------+---------+------------------------------------------------------------------------------------------------------------+

Anzeige im Frontend
-------------------

Die Alerts werden in der Smarty-Variable ``alertList`` als Collection gespeichert. Alle Alerts bei denen
``showInAlertListTemplate === true`` gesetzt ist, werden zentral im Header ausgegeben.

.. code-block:: html+smarty

    {include file='snippets/alert_list.tpl'}

Falls Sie ein Alert an einer speziellen Stelle in einem Template ausgeben lassen wollen, anstatt allgemein im Header,
dann setzen Sie die Option ``showInAlertListTemplate`` auf ``false``. Geben Sie dann das Alert an gewünschter Stelle wie
folgt aus:

.. code-block:: html+smarty

    {$alertList->displayAlertByKey('testInfo')}

