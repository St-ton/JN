Sichere Plugins schreiben
=========================

.. |br| raw:: html

   <br />

Plugins haben vollständigen Zugriff auf den Onlineshop. |br|
Es ist daher unerlässlich, dass jeder Plugin-Entwickler größten Wert auf die Sicherheit der eigenen Plugins legt.

Dieser Guide soll Plugin-Entwicklern dabei helfen, ihre Plugins gemäß den gängigen Sicherheitsstandards zu entwickeln
und die Sicherheit des gesamten Ökosystems von JTL-Shop zu stärken.

Validierung
-----------

Zunächst sollten sämtliche Eingabewerte für SQL-Queries validiert werden. |br|
Eine Validierung der Daten ist ein erster Schritt in die richtige Richtung, um *SQL-Injections* und andere Probleme
zu vermeiden. Allgemeine Hinweise dazu sind z. B. unter
"`Testing for SQL Injection (OTG-INPVAL-005) <https://www.owasp.org/index.php/Testing_for_SQL_Injection_(OTG-INPVAL-005)>`_"
zu finden.

Als gutes Beispiel könnten die von PHP bereitgestellten Validierungsfunktionen genutzt werden:

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

Die einzig wirklich sichere Variante zur Verhinderung von *SQL-Injections* ist es, ausschließlich *Prepared Statements*
zur Parametrisierung von SQL-Queries zu verwenden. |br|
Bei der Verwendung von *Prepared Statements* ist es unmöglich, eine *SQL-Injection* zu erzeugen. Wenn Sie sich nur
auf die Validierung der Daten verlassen, vergessen Sie früher oder später, einen Wert ausreichend zu validieren.
Zudem können Freitextfelder gar nicht entsprechend validiert werden.

JTL-Shop stellt eine einfache Möglichkeit bereit, *Prepared Statements* auszuführen. |br|
Empfohlene Variante:

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

Hinweis zu Plugin-Zertifizierungen
----------------------------------

.. important::

    JTL wird nur noch Plugins **zertifizieren**, die ausschließlich *Prepared Statements* verwenden.

Wir empfehlen daher allen Plugin-Entwicklern, den eigenen Code auf *Prepared Statements* umzustellen bzw. neuen Code
ausschließlich mit *Prepared Statements* zu entwickeln.
