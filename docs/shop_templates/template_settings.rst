Template Einstellungen
======================

.. contents::
    Inhalt

*******************************************
 Wofür werden diese Einstellungen benötigt?
*******************************************

Die Template Einstellungen befinden sich im Shop-Backend unter ``Templates -> JTL-Shop-Evo -> Einstellungen`` und werden aus der zum Template zugehörigen ``template.xml`` generiert.
Es befinden sich bereits einige vordefinierte Einstellungen in der ``template.xml``, es können aber auch, vorzugsweise in einem eigenen :doc:`Child-Template </shop_templates/eigenes_template>`, Einstellungen hinzugefügt werden.
In den Template-Einstellungen können Einstellungen verändert werden, die sich auf die Ausgabe der Seite beeinflussen, wie, welches Theme verwendet werden soll, ob im Footer weiter Links angezeigt werden sollen etc..

****************************************
 Bedeutungen der einzelnen Einstellungen
****************************************

Im Evo-Template werden bereits einige vordefinierte Einstellungen mitgeliefert, deren Bedeutung hier genauer erklärt werden.

Allgemein
---------

Komprimierung von JavaScript- und CSS-Dateien
  Wird diese Einstellung aktiviert, werden Javascript und CSS-Dateien komprimiert, um so die Dateigröße zu verringern und damit Traffic zu sparen.

Komprimierung des HTML-Ausgabedokuments
  Die gesamte HTML-Struktur wird bei der Ausgabe komprimiert, um ebenfalls die Dateigröße zu verringern.

Komprimierung von Inline-CSS-Code
  CSS-Code, der sich innerhalb des HTML - und nicht in einer seperaten Datei - befindet, wird komprimiert.

Komprimierung von Inline-JavaScript-Code
  JavaScript-Code, der sich innerhalb des HTML - und nicht in einer seperaten Datei - befindet, wird komprimiert.

Benutzerdefinierte Template-Dateien verwenden?
  Wird diese Einstellung aktiviert, können im Template für jede Template-Datei (.tpl) benutzerdefinierte Dateien erstellt werden.
  Dafür wird die jeweilige Datei kopiert und am Namen ``_custom`` angehängt. Beispiel: Die Datei ``header.tpl`` kopieren und die kopierte Datei in ``header_custom.tpl`` umbenennen.
  Diese Dateien werden beim Update des Templates nicht überschrieben und sind deshalb, nach :doc:`Child-Template </shop_templates/eigenes_template>`, das bevorzugte Mittel um minimale Änderungen am Template vorzunehmen.

  **Hinweis:** Diese Funktion hat nur Einfluss auf der Evo-Template und nicht bei dem Evo-Child-Template, da dort alle vorhandenen Dateien die äquivalente Original-Datei überschreiben.

Cron aktivieren?
  Der Cron übernimmt die Aufgabe, sich ständig wiederholende Aufgaben abzuschließen, z.B.: Aktualisierung der Statistiken etc. (Empfohlene Einstellung: an)

Theme
-----

Hier kann das Theme und alle theme-spezifischen Einstellung geändert werden, die größtenteils selbsterklärend sind.

Hintergrundbild
  Das Evo-Template wird mit 13 Hintergrundbildern ausgeliefert. Diese befinden sich im Ordner ``<Shop-Root>/templates/Evo/themes/base/images/backgrounds``.

  Falls Sie ein eigenes Theme erstellt haben und ein eigenes Hintergrundbild verwenden möchten, legen Sie dieses bitte in den Ordner ``<Shop-Root>/templates/Evo/themes/ihrTheme`` mit dem Dateinamen ``background.jpg`` und wählen dieses anschließend in den Theme-Einstellungen unter Hintergrundbild aus (Custom).
  Verfahren Sie genau so, wenn Sie das Evo-Child-Template verwenden.

Statischer Header?
  Der Header wird, nicht nur beim Hinaufscrollen, sondern auch beim Herunterscrollen, am oberen Bildschirmrand fixiert.

Favicon
  Ein Favicon ist ein kleines Bild (32x32, 16x16) welches in den Browser-Tabs neben dem Titel der Seite angezeigt wird.

Megamenü
--------

Kategorien
  Ist dieser Punkt aktiv, werden alle Hauptkategorien im Shop horizontal im Megamenü angeordnet. Sollen keine Kategorien angezeigt werden, muss man diesen Punkt auf inaktiv setzen.

Seiten der Linkgruppe 'megamenu'
  Ist dieser Punkt aktiv, dann achtet das Megamenü auf eine Linkgruppe mit dem Namen ``megamenu``. Diese kann unter ``Inhalte -> Eigene Seiten`` hinzugefügt werden. Die enthaltenen Seiten können in der Storefront hierarchisch im Megamenü aufgeklappt werden.

Hersteller-Dropdown
  Aktiviert ein Dropdown im Megamenü, welches Links zu den Herstellern beinhaltet.

Footer-Einstellungen
--------------------

Hier kann man verschiedene Einstellungen für den Footer vornehmen, unter anderem Social-Media-Links.

Evo-LiveStyler
--------------

Eine ausführliche Beschreibung zu dem Thema gibt es :doc:`hier </shop_templates/livestyler>`.
