Struktur
========

.. |br| raw:: html

    <br />

Allgemeines zum Template-System von JTL-Shop
--------------------------------------------

JTL-Shop nutzt das Template-System `Smarty <https://www.smarty.net/>`_, welches die serverseitige Anwendungslogik
von der Präsentation (dem Template) trennt. |br|
Das EVO-Template nutzt zur Darstellung die bekannte Technologie CSS/LESS. |br|
Im NOVA-Template gehen wir noch einen Schritt weiter und setzen anstelle von CSS/LESS das modernere SCSS ein.

Das EVO-Template beinhaltet 16 verschiedene Design-Themen (im Folgenden "*Themes*" genannt), welche die Darstellung
des Shops beeinflussen. Im NOVA-Template gibt es aktuell das Standard-Theme. |br|
Diese *Themes* sind freie Designvorlagen von https://bootswatch.com. Dort finden Sie auch zusätzliche Informationen
zu allen im Shop integrierten Themes. |br|
Im Admin-Backend unter "*[Templates|Template-Einstellungen] -> [Template-Name] -> Einstellungen*" bestimmt der
Shop-Betreiber ein Standard-Theme, welches im Shop aktiv ist.

.. note::

    Alle Erläuterungen und Anleitungen der folgenden Seiten beziehen sich auf die JTL-Standard-Templates. |br|
    (Gelegentlich wird examplarisch nur das EVO-Template als Bezugspunkt aufgelistet. Besonderheiten
    des NOVA-Templates finden sich dann im Text.)

Ordnerstruktur
--------------

Alle JTL-Shop-Templates befinden sich im Ordner ``<Shop-Root>/templates/``.

.. code-block:: console

    templates/Evo/
    ├── account/
    ├── basket/
    ├── blog/
    ├── boxes/
    ├── checkout/
    ├── comparelist/
    ├── contact/
    ├── fonts/
    ├── js/
    ├── layout/
    ├── newsletter/
    ├── page/
    ├── php/
    ├── poll/
    ├── productdetails/
    ├── productlist/
    ├── productwizard/
    ├── register/
    ├── selectionwizard/
    ├── snippets/
    ├── themes/
    └── template.xml


In jedem Template-Ordner muss zwingen eine ``template.xml`` vorhanden sein.

Ordner: snippets
""""""""""""""""

Die Template-Dateien im Stammverzeichnis eines JTL-Shop-Templates inkludieren (per Smarty-Include-Funktion) unter
anderem auch die Template-Dateien im Verzeichnis ``snippets/``. |br|
An dieser Stelle haben Sie die Möglichkeit, verschiedene Template-Teile ("Schnipsel") unterzubringen und von hieraus
in Ihr Template zu inkludieren, die sonst nicht eindeutig thematisch zuzuordnen sind.

Ordner: themes
""""""""""""""

Ein Theme im JTL-Shop-Template definiert per CSS und Hintergrundgrafiken das Design des Shop-Templates. |br|
(Im EVO-Template wird dieses CSS aus LESS erzeugt und im neuen NOVA-Template geschieht dies aus SCSS heraus.)

Themes liegen in Unterordnern im Verzeichnis ``templates/[Template-Name]/themes/``. |br|
Weitere Informationen zur Theme-Struktur und Theme-Anpassung finden Sie unter ":doc:`/shop_templates/eigenes_theme`".

Die ``template.xml``
--------------------

Jedes Template verfügt über eine Datei namens ``template.xml``, welche die grundlegenden Einstellungen des jeweiligen
Templates beinhaltet. |br|
Diese Einstellungen werden vom Shop automatisch eingelesen und im Admin-Backend
unter "*Templates|Template-Einstellungen -> [Template-Name] -> Einstellungen*" aufgelistet.

In der ``template.xml`` werden nur die verfügbaren Template-Einstellungen definiert. Die zugehörigen Einstellungswerte
werden in der Shop-Datenbank gespeichert.

Diese Tags sollten immer gefüllt sein:

+-------------------+-------------------------------------------------------------------------+
| Tag               | Beschreibung                                                            |
+===================+=========================================================================+
| ``<Name>``        | Name des Templates                                                      |
|                   | (wird unter "*Templates*" als Name des Templates angezeigt)             |
+-------------------+-------------------------------------------------------------------------+
| ``<Author>``      | Name des Autors                                                         |
|                   | (wird unter "*Templates*" als Name des Autors angezeigt)                |
+-------------------+-------------------------------------------------------------------------+
| ``<URL>``         | Autor-URL                                                               |
|                   | (wird unter "*Templates*" als Verlinkung zum Autor angezeigt)           |
+-------------------+-------------------------------------------------------------------------+
| ``<Version>``     | Template-Version                                                        |
+-------------------+-------------------------------------------------------------------------+
| ``<ShopVersion>`` | Shop-Version                                                            |
+-------------------+-------------------------------------------------------------------------+
| ``<Preview>``     | Pfad zum Vorschaubild ausgehend vom aktuellen Verzeichnis des Templates |
+-------------------+-------------------------------------------------------------------------+
| ``<Descrption>``  | Kurzbeschreibung des Templates                                          |
|                   | (wird unterhalb des Template-Namens unter "*Templates*" angezeigt)      |
+-------------------+-------------------------------------------------------------------------+

Neben template-spezifischen Einstellungen werden in der ``template.xml`` auch die verfügbaren Themes und die zu
inkludierenden CSS/JS-Dateien definiert. |br|
Eine Anleitung zum Erstellen eines eigenen Themes finden Sie im Abschnitt ":doc:`/shop_templates/eigenes_theme`".

