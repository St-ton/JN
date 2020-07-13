Hooks
=====

.. |br| raw:: html

   <br />

Hooks bieten Plugins die Möglichkeit, an vordefinierten Stellen in die Ausführungslogik des Onlineshops einzugreifen
und ggf. übergebene Daten bzw. das Ausgabedokument zu manipulieren. |br|
Möglich ist dies an allen Stellen im Code des Onlineshops, an denen explizit die Funktion
``executeHook(int $nHook, array $args_arr)`` ausgeführt wird.

Eine Liste aller verfügbaren Hooks finden Sie im Kapitel ":doc:`Liste aller Hooks </shop_plugins/hook_list>`". |br|
Wie Sie dem Onlineshop mitteilen, welche Hooks Ihr Plugin nutzen möchte, erfahren Sie im Kapitel zur ``info.xml``
im Abschnitt ":ref:`label_infoxml_hooks`".

**Beispiel:**

Ausführen eines Hooks *vor dem Speichern eines News-Kommentars, in der Datenbank*:

.. code-block:: php

    executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$oNewsKommentar]);

Auf diesen Hook registrierte Plugins können in der entsprechenden Hook-Datei das Array ``$args_arr`` nutzen
und ggf. modifizieren.

Nutzt ein Plugin z. B. den oben genannten Hook ``HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN`` (ID 34), lässt sich
zum Beispiel der dort übergebene Kommentar verändern.

.. code-block:: php

    <?php

    if (!empty($args_arr['comment'])) {
        $args_arr['comment'] .= '<br>Dieser Text wird an nicht-leere Kommentare angehängt!';
    }

Ab JTL-Shop 5.x existiert eine neue Alternative zu den bekannten Hooks in JTL-Shop - der *EventDispatcher*. |br|
Wie Sie Gebrauch von diesem neuen Feature machen, finden Sie im Kapitel "Bootstrapping"
unter ":ref:`label_bootstrapping_eventdispatcher`".

Manipulation des DOM
--------------------

Einer der häufigsten Anwendungsfälle ist, eigenen HTML-Code in das Ausgabedokument zu schreiben. |br|
Hierzu dient der Hook ``HOOK_SMARTY_OUTPUTFILTER`` (ID 140). Dort kann via PHPQuery beliebiger Inhalt in den DOM
eingefügt werden. |br|
In einem trivialen Beispiel könnte ein Plugin den Inhalt eines Templates via Smarty rendern und
an den Body des HTML-Dokuments anhängen:

.. code-block:: php

    <?php

    $template = $oPlugin->cFrontendPfad . 'templates/' . 'example.tpl';
    pq('body')->append($smarty->fetch($template));


Eine Übersicht über PHPQuery finden Sie in dessen `Dokumentation <https://code.google.com/archive/p/phpquery>`_.
