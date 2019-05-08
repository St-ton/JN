Sichere Plugins schreiben
=========================

Plugins haben vollständigen Zugriff auf den Shop. Es ist daher unerlässlich, dass jeder Pluginentwickler größten Wert
auf die Sicherheit der eigenen Plugins legt. Dieser Guide soll Pluginentwicklern dabei helfen, ihre Plugins gemäß
den gängigen Sicherheitsstandards zu entwickeln und die Sicherheit des gesamten JTL-Shop Ökosystems zu stärken.


Validierung
~~~~~~~~~~~

Zunächst sollten sämtliche Eingabewerte für SQL-Queries validiert werden. Eine Validierung der Daten ist ein erster
Schritt in die richtige Richtung um SQL-Injections (allgemeine Hinweise dazu sind z.B. unter https://www.owasp.org/index.php/Testing_for_SQL_Injection_(OTG-INPVAL-005) zu finden) und andere Probleme zu vermeiden.
Als Beispiel könnten die von PHP bereitgestellten Validierungsfunktionen genutzt werden:

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
~~~~~~~~~~~~~~~~~~~

Die einzige wirklich sichere Variante um SQL-Injections zu verhindern ist es, ausschließlich Prepared Statements zur
Parametrisierung von SQL-Queries zu verwenden. Bei der Verwendung von Prepared Statements ist es unmöglich, eine
SQL-Injection zu erzeugen. Wenn man sich nur auf die Validierung der Daten verlässt, vergiss man früher oder später,
einen Wert ausreichen zu validieren. Zudem können Freitextfelder nicht entsprechend validiert werden.

Der JTL-Shop stellt eine einfache Möglichkeit bereit, Prepared Statements auszuführen (empfohlene Variante):

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
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Wir werden nur noch Plugins zertifizieren, die ausschließlich Prepared Statements verwenden. Wir
empfehlen daher allen Plugin-Entwicklern, den eigenen Code auf Prepared Statements umzustellen bzw. neuen Code
ausschließlich mit Prepared Statements zu entwickeln.
