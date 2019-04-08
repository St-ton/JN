Overlays
========

Seit der Shop Version 5.0.0 können Overlays, speziell Artikeloverlays, templatespezifisch eingestellt werden.
Die Einstellung finden Sie im Menü unter *Bilder->Artikel-Overlays*.

Dort können Sie wie gewohnt die entsprechenden Overlay-Typen für die gewünschten Sprachen ändern. Die Änderungen werden
nur für das aktuell ausgewählte Template vorgenommen und gespeichert.


Bei neuen Templates
-------------------

Wenn Sie ein eigenes Template entwickeln, besteht die Möglichkeit direkt eigene Overlays für dieses mitzuliefern.
Dazu müssen die originalen Bilder in den Ordner **templatename/images/overlay/original/** gelegt werden. Die Dateinamen
sind dabei nach dem Muster **overlay_sprache_overlaytype.grafikformat** zu wählen.

.. note::

    Beispiel: **overlay_1_2.jpg**  wobei **"1"** im Standard-Shop die Sprache *Deutsch* ist und die **"2"** für das
    Overlay *Sonderangebote* steht.

Die entsprechenden Overlay-Größen werden angelegt, sobald das Template aktiviert wird.

Typen und Sprache
-----------------

Die ID's der Sprache können sie der Datenbanktabelle **tsprache** entnehmen, die Overlay-Typen der Tabelle
**tsuchspecialoverlay**.

Aktuell gibt es folgende Overlay-Typen:

+------+--------------------+
| Type | Name               |
+======+====================+
| 1    | Bestseller         |
+------+--------------------+
| 2    | Sonderangebote     |
+------+--------------------+
| 3    | Neu im Sortiment   |
+------+--------------------+
| 4    | Top Angebote       |
+------+--------------------+
| 5    | In kürze Verfügbar |
+------+--------------------+
| 6    | Top bewertet       |
+------+--------------------+
| 7    | Lagerbestand       |
+------+--------------------+
| 8    | Auf Lager          |
+------+--------------------+
| 9    | Vorbestellbar      |
+------+--------------------+


Werden keine eigenen Overlays gewählt, wird auf die Standard-Overlays zurückgegriffen.
