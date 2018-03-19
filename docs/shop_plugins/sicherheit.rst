Sichere Plugins schreiben
=========================

Plugins haben vollständigen Zugriff auf den Shop. Es ist daher unerlässlich, dass jeder Pluginentwickler größten Wert
auf die Sicherheit der eigenen Plugins legt. Dieser Guide soll Pluginentwicklern dabei helfen, ihre Plugins gemäß
den gängigen Sicherheitsstandards zu entwickeln und die Sicherheit des gesamten JTL-Shop Ökosystems zu stärken.

SQL-Injection
-------------

Bei einer SQL-Injection wird Schadcode in einen SQL-Befehl eingeschleust, der vom Programmierer eines Moduls so nicht
vorgesehen war.

Ein Beispiel für unsicheren Code (BITTE NIEMALS SO ETWAS PROGRAMMIEREN!!!):

.. code-block:: php

    <?php

    $db = Shop::Container()->getDB();
    $productId = $_POST['productId'];
    $query = "
        SELECT cArtNr, cName, cBeschreibung
        FROM tartikel
        WHERE kArtikel = $productId
    ";
    $productInfo = $db->executeQuery($query, NiceDB::RET_ARRAY_OF_OBJECTS);

Das Problem hierbei ist, dass ein Angreifer nun Schadcode über die Variable $productId in den SQL-Query einschleusen
kann.

Die Variable könnte zum beispiel folgenden Code enthalten:

.. code-block:: sql

    10; INSERT INTO tadminlogin (cLogin, cPass, cName) VALUES ('boesewicht', md5('passwort'), 'Bösewicht');

Und schon hätte der Angreifer Zugriff auf das vollständige Backend des Shops.


Validierung
~~~~~~~~~~~

Zunächst sollten sämtliche Werte validiert werden. Eine Validierung der Daten ist einer erster Schritt in die richtige
Richtung um SQL-Injection und andere Probleme zu verhindern. Als Beispiel könnten die von PHP bereitgestellten
Validierungsfunktionen genutzt werden (ACHTUNG! Auch dieses Beispiel ist nicht empfohlen!):

.. code-block:: php

    <?php

    $db = Shop::Container()->getDB();

    // validiert, dass es sich bei der Variable um eine Ganzzahl handelt.
    $productId = filter_input(INPUT_POST, 'productId', FILTER_VALIDATE_INT);
    if (!$productId || $productId < 0) {
        // Der Wert ist nicht gültig. Die Verarbeitung sollte abgebrochen werden
        exit();
    }

    $query = "
        SELECT cArtNr, cName, cBeschreibung
        FROM tartikel
        WHERE kArtikel = $productId
    ";
    $productInfo = $db->executeQuery($query, NiceDB::RET_ARRAY_OF_OBJECTS);


Prepared Statements
~~~~~~~~~~~~~~~~~~~

Die einzige wirklich sichere Variante um SQL-Injections zu verhindern ist es, ausschließlich Prepared Statements zur
Parametrisierung von SQL-Queries zu verwenden. Bei der Verwendung von Prepared Statements ist es unmöglich, eine
SQL-Injection zu erzeugen. Wenn man sich nur auf die Validierung der Daten verlässt, vergiss man früher oder später,
einen Wert ausreichen zu validieren. Zudem können Freitextfelder nicht entsprechend validiert werden. Der einzige
wirklich sichere Weg ist es daher, Prepared Statements
zu verwenden.

Der JTL-Shop stellt eine einfache Möglichkeit bereit, PreparedStatements auszuführen (Empfohlene Variante):

.. code-block:: php

    <?php

    $db = Shop::Container()->getDB();

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
    $productInfo = $db->executeQueryPrepared($query, ['productId' => $productId], NiceDB::RET_ARRAY_OF_OBJECTS);


Hinweis zu Pluginzertifizierungen
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Aktuell nutzt mancher Code im Shop selbst keine Prepared Statements. Sobald wir den JTL-Shop-Code entsprechend
umgestellt haben, werden wir nur noch Plugins zertifizieren, die ausschließlich Prepared Statements verwenden. Wir
empfehlen daher allen Plugin-Entwicklern, den eigenen Code auf Prepared Statements umzustellen bzw. neuen Code
ausschließlich mit Prepared Statements zu entwickeln.
