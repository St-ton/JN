Struktur
========

*****************************************
 Allgemeines zum JTL-Shop Template System
*****************************************

JTL-Shop nutzt das Templatesystem `Smarty <http://www.smarty.net/>`_, welches die serverseitige Anwendungslogik von der Präsentation (dem Template) trennt.

Standardmäßig wird der JTL-Shop mit einem modernen CSS/LESS-Template mit dem Namen ``JTL-Shop-Evo`` ausgeliefert.
Das Template beinhaltet 15 verschiedene Design-Themen (im folgenden ``Themes`` genannt), welche die Präsentation des Shops beeinflussen.
Das Evo-Template beinhaltet alle Themes von https://bootswatch.com. Dort finden Sie auch eine Übersicht aller integrierten Themes.

Im Admin-Backend unter ``Templates -> JTL-Shop-Evo -> Einstellungen`` bestimmt der Shop-Betreiber ein Standard-Theme, welches im Shop aktiv ist.

Alle folgenden Erläuterungen und Anleitungen auf diesen Seiten beziehen sich auf das Standard-Template des JTL-Shop "JTL-Shop-Evo".

***************
 Ordnerstruktur
***************

.. image:: /_images/template_tree.png

Das JTL-Shop-Evo Template liegt im Ordner ``<Shop-Root>/templates/Evo/``.

*************
 template.xml
*************

Weiterhin finden Sie im Stammverzeichnis des Evo-Templates die Datei ``template.xml``.
In dieser Datei werden template-spezifische Einstellungen definiert. Diese Einstellungen werden vom Shop automatisch eingelesen und werden im Admin-Backend unter ``Templates -> JTL-Shop-Evo -> Einstellungen`` aufgelistet. In der ``template.xml`` werden nur die verfügbaren Template-Einstellungen definiert, die zugehörigen Einstellungswerte werden in der Shop-Datenbank gespeichert.

.. note:: Template-spezifische Tags:

    =========== ============
    Tag         Beschreibung
    =========== ============
    Name        Name des Templates

                -> wird unter ``Templates`` als Name verwendet
    Author      Template-Autor

                -> wird unter ``Templates`` als Autor verwendet
    URL         Autor-URL

                -> wird unter ``Templates`` für die verlinkung des Autors verwendet
    Version     Template-Version
    ShopVersion Shop-Version
    Preview     Pfad zum Vorschaubild ausgehend vom aktuellen Verzeichnis des Templates
    DokuURL     URL zur Template-Dokumentation

                -> wird unter ``Templates -> TemplateName -> Einstellungen`` als Link zur ``Dokumentation zu Einstellungen`` verwendet
    =========== ============

Neben template-spezifischen Einstellungen werden in der ``template.xml`` auch die verfügbaren Themes und die zu inkludierenden CSS/JS-Dateien definiert. Eine Anleitung zum Einbau eines eigenen Themes finden Sie unter :doc:`/shop_templates/eigenes_theme`.

*****************
 Ordner: snippets
*****************

Die Template-Dateien im Stammverzeichnis JTL-Shop-Evo Template inkludieren per Smarty-Include-Funktion andere Template-Dateien, die im Verzeichnis ``snippets`` liegen.

***************
 Ordner: themes
***************

Ein Theme im JTL-Shop-Evo Template definiert per CSS/LESS und Hintergrundgrafiken das Design des Shop-Templates. Themes liegen in Unterordnern im Verzeichnis Evo/themes/.

Unter :doc:`/shop_templates/eigenes_theme` finden Sie nähere Informationen zur Theme-Struktur und Theme-Anpassung.
