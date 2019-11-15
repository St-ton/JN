Sichere Plugins schreiben
=========================

.. |br| raw:: html

   <br />

Plugins haben vollständigen Zugriff auf den Shop. |br|
Es ist daher unerlässlich, dass jeder Pluginentwickler größten Wert auf die Sicherheit der eigenen Plugins legt.

Dieser Guide soll Pluginentwicklern dabei helfen, ihre Plugins gemäß den gängigen Sicherheitsstandards zu entwickeln
und die Sicherheit des gesamten JTL-Shop Ökosystems zu stärken.

Validierung
-----------

Zunächst sollten sämtliche Eingabewerte für SQL-Queries validiert werden. |br|
Eine Validierung der Daten ist ein erster Schritt in die richtige Richtung, um *SQL-Injections* (allgemeine Hinweise
dazu sind z.B. unter
"`Testing for SQL Injection (OTG-INPVAL-005) <https://www.owasp.org/index.php/Testing_for_SQL_Injection_(OTG-INPVAL-005)>`_"
zu finden) und andere Probleme zu vermeiden.

Als gutes Beispiel könnten die, von PHP bereitgestellten, Validierungsfunktionen genutzt werden:

.. code-block:: php

    <?php
    // validiert, dass es sich bei der Variable um eine Ganzzahl handelt.
    $productId = filter_input(INPUT_POST, 'productId', FILTER_VALIDATE_INT);
    if (!$productId || $productId < 0) {
        // Der Wert ist nicht gültig. Die Verarbeitung sollte abgebrochen werden
        exit();
    }
    // andernfalls kann mit dem Wert weitergearbeitet werden


Prepared Statements
-------------------

Die einzig wirklich sichere Variante um *SQL-Injections* zu verhindern ist es, ausschließlich *Prepared Statements* zur
Parametrisierung von SQL-Queries zu verwenden. |br|
Bei der Verwendung von *Prepared Statements* ist es unmöglich, eine *SQL-Injection* zu erzeugen. Wenn man sich nur
auf die Validierung der Daten verlässt, vergiss man früher oder später, einen Wert ausreichend zu validieren. Zudem
können Freitextfelder gar nicht entsprechend validiert werden.

Der JTL-Shop stellt eine einfache Möglichkeit bereit, *Prepared Statements* auszuführen: |br|
(empfohlene Variante)

.. code-block:: php

    <?php

    $db = JTL\Shop::Container()->getDB();

    // validiert, dass es sich bei der Variable um eine Ganzzahl handelt.
    $productId = filter_input(INPUT_POST, 'productId', FILTER_VALIDATE_INT);
    if (!$productId || $productId < 0) {
        // Der Wert ist nicht gültig. Die Verarbeitung sollte abgebrochen werden
        exit();
    }

    $query = "
        SELECT cArtNr, cName, cBeschreibung
        FROM tartikel
        WHERE kArtikel = :productId
    ";
    $productInfo = $db->queryPrepared(
        $query,
        ['productId' => $productId],
        JTL\DB\ReturnType::ARRAY_OF_OBJECTS
    );

Hinweis zu Pluginzertifizierungen
---------------------------------

.. important::

    JTL wird nur noch Plugins **zertifizieren**, die ausschließlich *Prepared Statements* verwenden.

Wir empfehlen daher allen Plugin-Entwicklern, den eigenen Code auf *Prepared Statements* umzustellen bzw. neuen Code
ausschließlich mit *Prepared Statements* zu entwickeln.
