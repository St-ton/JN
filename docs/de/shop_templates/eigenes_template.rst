.. _label-eigenes-template:

Eigenes Template
================

.. |br| raw:: html

   <br />

.. raw:: html

    <style>
        .std-ref {
            font-size: 75%;
            font-family: "Consolas","Monaco",monospace;
            font-weight: 400;
            background-clip: border-box;
            background-color: rgb(255,255,255);
            border-left: 1px solid rgb(225,228,229);
            border-right: 1px solid rgb(225,228,229);
            border-top: 1px solid rgb(225,228,229);
            border-bottom: 1px solid rgb(225,228,229);
            border-collapse: collapse;
            border-spacing: 0px, 0px;
            box-sizing: border-box;
            padding: 0px 5px 0px;
            color: rgb(231, 76, 69) !important;
            white-space: nowrap;
            empty-cells: show;
        }
    </style>

Was ist ein Template?
---------------------

Das *Template* des Shops ist die "optische Grundstruktur" bzw. "das grundlegende Aussehen" Ihres Shops. |br|
Mit Hilfe des *Templates* wird die Anordnung sämtlicher Elemente aller Shop-Seiten vorgegeben.

.. note::

    Das *Template* ist nicht zu verwechseln, mit dem *Theme*!

    Das *Theme* ist ein Teil des *Templates*, welches eine noch feinere Anpassung der Shop-Seiten ermöglicht.


Der bevorzugte Weg zum eigenen Template: Child-Templates
--------------------------------------------------------

Um ein eigenes Template zu entwickeln, müssen Sie ein Child-Template anlegen. Wie das geht, wird auf dieser Seite
erklärt.

Mit diesem Child-Template können Sie **Style-, JavaScript- oder PHP**-Dateien der in JTL-Shop mitgelieferten
Standard-Templates "*EVO*" und "*NOVA*" **erweitern oder überschreiben**. Dabei werden alle Dateien des Templates
von JTL-Shop geladen, außer denjenigen, die Sie in Ihr Child-Template kopiert und somit "überschrieben" haben.
In Ihrem Child-Template können Sie nun einzelne Passagen des Shops (Blocks) oder auch komplette Dateien
austauschen. |br|
Das mitgeliefert Template bleibt dabei erhalten und **updatefähig**, da es nicht verändert werden muss.

Die Struktur eines Child-Templates inkl. LESS- **oder** CSS-Dateien sieht dann in etwa so aus:

.. code-block:: console

    templates/childEvo/
    ├── css
    │   └── mytheme.css
    ├── js
    │   └── custom.js
    ├── php
    │   └── functions.php
    ├── themes
    │   ├── base
    │   │   ├── images
    │   │   ├── less
    │   │   └── fileinput.min.css
    │   └── my-evo
    │       ├── less
    │       └── bootstrap.css
    ├── preview.png
    ├── README.md
    └── template.xml

.. note:

   text instead of (un-editable old image)

   .. image:: /_images/jtl-shop_child-template_struktur.jpg

.. note::

    Manche Dateien, wie z. B. :ref:`functions.php <eigene-smarty-funktionen-integrieren>`, sind nur exemplarisch in
    dieser Struktur abgebildet und nicht obligatorisch. |br|
    Dies soll nur aufzeigen, dass Sie auch Funktionen überschreiben können.

.. _label_eigenes_child_template:

Ein neues Child-Template erstellen
-----------------------------------

**Am einfachsten ist es, wenn Sie mit einem Beispiel-Child-Template beginnen.**

Sie könnten Beispiel-Child-Templates manuell von der Projektseite
`Child-Templates <https://gitlab.com/jtl-software/jtl-shop/child-templates/>`_ herunterladen.

Das Anlegen eines Child-Templates ist in Shop 4.x und 5.x fast identisch, lediglich der Aufbau der einzelnen Templates
unterscheidet sich. |br|
So existiert für jedes der von JTL ausgelieferten Templates ein entsprechendes **Beispiel**.

.. caution::

    Verwenden Sie kein **Beispiel Child** produktiv! |br|
    Wir empfehlen, mit einer **Kopie** in einem neuen Verzeichnis zu starten.

Vorgehen am Beispiel von NOVAChild
""""""""""""""""""""""""""""""""""

Sie finden die Vorlage auf der
Projektseite `novachild <https://gitlab.com/jtl-software/jtl-shop/child-templates/novachild>`_.

Für ein neues Child-Template benennen Sie zunächst den Ordner ``novachild/`` in den gewünschten
Template-Namen um, z. B. ``MeinShopTemplate/``, und laden dann den Ordner in das Unterverzeichis ``templates/``
Ihres Shops. |br|

.. caution::

    Verwenden Sie für den Templatenamen bitte keine Sonderzeichen. |br|

Im Unterordner ``<Shop-Root>/templates/MeinShopTemplate/`` finden Sie die Datei ``template.xml``.

Wenn Sie also beispielsweise das NOVA-Template von JTL-Shop erweitern möchten, sollte die ``template.xml`` wie
folgt aussehen:

.. code-block:: xml
    :emphasize-lines: 8

    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
    <Template isFullResponsive="true">
        <Name>MeinShopTemplate</Name>
        <Author>Max Mustermann</Author>
        <URL>https://www.mein-shop.de</URL>
        <Version>1.00</Version>
        <MinShopVersion>5.0.0</MinShopVersion>
        <Parent>NOVA</Parent>
        <Preview>preview.png</Preview>
        <Description>Das ist mein eigenes Template!</Description>
    </Template>

Dabei ist zu beachten, dass unter ``<Parent>`` das Eltern-Template (repräsentiert durch den Ordnernamen) eingetragen
wird, welches angepasst werden soll.

.. hint::

    Das Attribute **isFullResponsive="true|false"** im Tag ``<Template>`` kennzeichnet, dass sich Ihr neues Template
    vollständig responsive verhält, also automatisch an jede Auflösung anpasst.

    Wenn Sie Ihr Child-Template vom Evo- oder NOVA-Template ableiten, dann sollten Sie dies immer auf **true**
    einstellen. |br|
    Das Attribut bewirkt bei der Einstellung auf **true**, dass im Backend die Option
    "*Standard-Template für mobile Endgeräte?*" nicht mehr ausgewählt werden kann und eine Warnung ausgegeben wird,
    falls dies (noch) so sein sollte.

Ab JTL-Shop Version 5.0.0 ist es bei allen Templates Konvention, dass der in der ``template.xml`` definierte Namen auch
dem Ordnernamen entsprechen muss. |br|
Darüberhinaus werden alle PHP-Dateien im Hauptverzeichnis des Templates über einen Autoloader geladen.
Auch der  Namespace in allen dort genutzen PHP-Dateien muss dabei immer dem Schema ``Template\<template-name>``
entsprechen.

Wenn Sie Ihr Childtemplate also wie oben in ``MeinShopTemplate`` umbenannt haben, müssen folgende Änderungen vorgenommen
werden:

* ``<Name>``-Attribut in der template.xml: ``MeinShopTemplate``
* Ordner des Templates: ``<Shop-Root>/templates/MeinShopTemplate``
* *namespace* in der Datei Bootstrap.php: ``Template\MeinShopTemplate``


Ihr Template aktivieren
-----------------------

Wenn Sie nun alle Änderungen an Ihrem Child-Template vorgenommen haben, gehen Sie in das Backend von JTL-Shop. |br|
Navigieren Sie im Backend des Shops zum Menü **Template** und klicken Sie dort auf den Button :guilabel:`Aktivieren` neben
Ihrem Child-Template.

In der folgenden Eingabemaske können Sie nun im Abschnitt **Theme** Ihr Theme aus der Select-Box auswählen.
Auch andere Template-Einstellungen können Sie hier vornehmen. |br|
Klicken Sie anschließend am Ende der Seite auf :guilabel:`Speichern`, um Ihr Template in Betrieb zu nehmen.

Nach dem Ändern von Templateeinstellungen und/oder dem Wechsel von Themes empfiehlt es sich, die entsprechenden
Zwischenspeicher des Shops zu leeren. |br|
Hierzu navigieren Sie im Backend-Menü auf den Menüpunkt "**System**" (Shop 4.x) bzw. "**Einstellungen**" (Shop 5.x)
und klicken auf "**Cache**". Wählen Sie hier "*Template*" in der dazugehörigen Checkbox aus.
Anschließend klicken Sie am Ende der Seite auf den Button :guilabel:`absenden`, um den Cache zu leeren.

Nun sollten Ihr Child-Template aktiviert sein, sodass Sie Ihre Änderungen in Ihrem JTL-Shop sehen können.

Eigenes Hintergrundbild
-----------------------

Um beispielsweise ein eigenes Hintergrundbild in Ihrem Shop einzurichten, kopieren Sie Ihr Hintergrundbild,
als **JPG** oder **PNG** in den Ordner ``<Shop-Root>/templates/Mein-Shop-Template/themes/[my-evo]/`` Ihres
EVO-Child-Templates.

Sie können nun das Hintergrundbild in den Template-Einstellungen Ihres EVO-Child-Templates im Backend von JTL-Shop
einstellen, indem Sie in der Pulldownliste für "Hintergrundbild" die Auswahl "Custom" am Ende der Liste auswählen.

.. note::

    Dies gilt nut für das EVO-Template. Im NOVA-Template gibt es kein Hintergrundbild.

Im Gegensatz hierzu wäre im NOVA-Template eine Änderung des Hintergrundes über dessen Farbwerte zu bewerkstelligen. |br|
Hierfür kann das Plugin "*JTL Theme-Editor*" genutzt werden. Weitere Informationen dazu finden Sie
im Abschnitt ":doc:`theme_edit`".

Überschreiben bestehender Skripte
---------------------------------

Falls Sie im Parent-Template definierte JavaScript-Dateien überschreiben möchten, fügen Sie dem File-Eintrag das
Attribut ``override="true"`` hinzu und erstellen Sie Ihre eigene Version der JavaScript-Datei im
Unterverzeichnis ``js/``.

.. code-block:: xml
    :emphasize-lines: 13

    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
    <Template isFullResponsive="true">
        <Name>Mein-Shop-Template</Name>
        <Author>Max Mustermann</Author>
        <URL>https://www.mein-shop.de</URL>
        <Parent>Evo</Parent>
        <Preview>preview.png</Preview>
        <Description>Mein erstes Child-Template</Description>

        <Minify>
            <JS Name="jtl3.js">
                <File Path="js/mytheme.js"/>
                <File Path="js/jtl.evo.js" override="true"/>
            </JS>
        </Minify>
        <Boxes>
            <Container Position="right" Available="1"></Container>
        </Boxes>
    </Template>

Dieses Beispiel würde bewirken, dass die Datei ``js/jtl.evo.js`` Ihres Child-Templates anstelle der originalen Datei
des Evo-Templates eingebunden wird. |br|
Ohne das **override**-Attribut würde die genannte Datei **zusätzlich** zur ``jtl.evo.js`` des Parent-Templates
eingebunden werden.

Der Name des minifizierten und zum Browser übermittelten Javascripts (``jtl3.js``) ist eine feste Konstante und kann
nicht angepasst werden.

Eigene Skripte nachladen
------------------------

**Hinweis für das NOVA-Template:**

Um den Ladevorgang der Shop-Seiten nicht zu stark zu verzögern, sollten Sie zusätzliche Skripte Ihres
Child-Templates in der Templatedatei ``footer.tpl`` laden. |br|
Hierfür ist der Block ``{block name='layout-footer-js'}`` vorgesehen.

Wichtig ist, dass Sie hierbei Ihre Skripte *asynchron* laden. Fügen Sie dazu das Attribut ``async`` zu Ihren
``<script>``-Tags hinzu. |br|
(zum Beispiel: ``<script src="my-nscript.js" async></script>``)


.. _label_eigenestemplate_tpldateien:

Änderungen an Template-Dateien
------------------------------

Template-Dateien (Dateiendung ``.tpl``) können auf zwei Arten angepasst werden:

* einzelne Teile ("*Blocks*")
* komplette Struktur einer Template-Datei

Anpassungen über Blocks
"""""""""""""""""""""""

"*Blocks*" sind im Template-Code, namentlich definierte Stellen, die im Child-Template, über diese Namen, *erweitert*
oder *ersetzt* werden können. |br|
Möglich ist dies dank der Fähigkeit des Smarty-Frameworks, "*Vererbung*" von Templates zu erlauben.

Beispielsweise können Sie im Header Ihres Shops individuelle Dateien laden, das Logo austauschen, oder das Menü
anpassen. |br|
Alle JTL-Templates besitzen bereits viele vordefinierte *Blocks*, die Sie verändern können.

Blocks sind in den Template-Dateien an folgender Struktur zu erkennen:

.. code-block:: html+smarty

    {block name="<name des blocks>"}...{/block}

Wenn Sie nun eine bestimmte Template-Datei verändern möchten, kopieren Sie diese aus dem Parent-Template und fügen
Sie an der gleichen Stelle in Ihr Child-Template-Verzeichnis ein.

.. attention::

    Die Ordnerstruktur im Child-Template muss der des Parent-Templates entsprechen.

    Beispiel: ``templates/Evo/layout/header.tpl`` -> ``templates/Mein-Shop-Template/layout/header.tpl``

Möchten Sie beispielsweise den Seitenkopf Ihres Shops anpassen, erstellen Sie in Ihrem Child-Template-Verzeichnis
den Ordner ``layout/`` und darin die Datei ``header.tpl``. |br|
Nun fügen Sie in dieser ``header.tpl`` Ihres Child-Templates folgenden Code ein:

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

Mit dieser Zeile wird die Child-Template-Datei ``header.tpl`` angewiesen, die Parent-Template-Datei
``header.tpl`` (hier aus dem EVO-Template) zu erweitern ("*extends*").

Möchten Sie nun beispielsweise den Seitentitel verändern, finden Sie in der ``header.tpl`` den Block '*head-title*' :

.. code-block:: html+smarty

    <title>{block name="head-title"}{$meta_title}{/block}</title>

Dieser Block kann nun auf drei verschiedene Arten geändert werden.

**1. "Ersetzen"**

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

    {block name="head-title"}Mein Shop!{/block}

Hierbei wird der komplette Block ersetzt:

- **Ursprüngliche Ausgabe:** {$meta_title}
- **Neue Ausgabe:** Mein Shop!

**2. "Text hängen":**

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

    {block name="head-title" append} Mein Shop!{/block}

Hier wird der eingegebene Text an den meta-title der Seite angehängt:

- **Ursprüngliche Ausgabe:** {$meta_title}
- **Neue Ausgabe:** {$meta_title} Mein Shop!

**3. "Text voranstellen":**

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

    {block name="head-title" prepend}Mein Shop! {/block}

Hiermit wird der eingegebene Text dem meta-title der Seite vorangestellt:

- **Ursprüngliche Ausgabe:** {$meta_title}
- **Neue Ausgabe:** Mein Shop! {$meta_title}

In Ihrem Child-Template befinden sich nun nur noch die Template-Dateien, die Sie verändert haben. Die
komplette Templatestruktur aus dem jeweiligen Parent-Template ist nicht erforderlich. |br|
Wird das Parent-Template aktualisiert, beispielsweise durch ein offizielles Update, müssen nur wenige bis gar
keine Anpassungen an Ihrem Child-Template vorgenommen werden.

Weitere Infos zu Blocks finden Sie auf `smarty.net <https://www.smarty.net/docs/en/language.function.block.tpl>`_

Anpassung der gesamten Struktur
"""""""""""""""""""""""""""""""

.. caution::

    Anpassungen der gesamten Template-Struktur können weitreichende Folgen haben! Gehen Sie bei der Bearbeitung
    deshalb bitte sehr vorsichtig vor.

Wenn Sie die komplette Struktur einer Template-Datei anpassen wollen, können Sie auch eine Datei mit gleichem
Namen wie im Parent-Template erstellen, aber den Inhalt selbst festzulegen. |br|
Dieses Vorgehen entspricht zwar im Weiteren der oben genannten Variante, allerdings werden nun keine Ersetzungen
vom Shop mehr vorgenommen, wie dies in den originalen Template-Dateien normalerweise geschieht.

Der Hauptnachteil dieser Variante ist der Verlust der Update-Fähigkeit über das Parent-Template. |br|
Wird im Zuge eines offiziellen Updates das Parent-Template umfangreich geändert, so müssen diese Änderungen händisch
in das Child-Template übernommen werden.

Eigenen CSS-Code einfügen
"""""""""""""""""""""""""

Wie man eigenen CSS-Code in das Child-Template einfügt, finden Sie
hier: :doc:`Eigenes Theme </shop_templates/eigenes_theme>`
